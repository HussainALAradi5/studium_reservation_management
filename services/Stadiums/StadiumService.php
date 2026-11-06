<?php

declare(strict_types=1);

namespace App\Services\Stadiums;

use App\Config\Database\Database;
use App\Constants\Common\ErrorCodes;
use App\Dtos\Common\AccessScope;
use App\Dtos\Common\PageResult;
use App\Dtos\Stadiums\StadiumFormDto;
use App\Dtos\Stadiums\StadiumQuery;
use App\Exceptions\Common\ConflictException;
use App\Exceptions\Common\NotFoundException;
use App\Exceptions\Common\ValidationException;
use App\Models\Stadiums\Stadium;
use App\Models\Users\User;
use App\Repositories\Regional\AddressRepository;
use App\Repositories\Reservations\ReservationRepository;
use App\Repositories\Stadiums\StadiumRepository;
use App\Repositories\Users\UserRepository;
use App\Services\Common\AccessPolicy;
use App\Services\Regional\CityService;

final class StadiumService
{
    public function __construct(
        private readonly StadiumRepository $stadiums = new StadiumRepository(),
        private readonly AddressRepository $addresses = new AddressRepository(),
        private readonly ReservationRepository $reservations = new ReservationRepository(),
        private readonly UserRepository $users = new UserRepository(),
        private readonly CityService $cities = new CityService()
    ) {
    }

    /** Public catalogue: active stadiums in active cities. */
    public function browse(StadiumQuery $query): PageResult
    {
        return $this->stadiums->paginate($query, AccessScope::all(), true);
    }

    /** Management list: all stadiums for admins, assigned ones for managers. */
    public function listManaged(User $user, StadiumQuery $query): PageResult
    {
        return $this->stadiums->paginate($query, AccessPolicy::scopeFor($user), false);
    }

    /** @return Stadium[] */
    public function listForSelect(User $user): array
    {
        return $this->stadiums->listInScope(AccessPolicy::scopeFor($user));
    }

    /** A stadium anyone may see; inactive ones are visible only to those who manage them. */
    public function findVisible(?User $user, string $code): Stadium
    {
        $stadium = $this->stadiums->findByCode($code);
        $canSee = $stadium !== null
            && ($stadium->isActive || ($user !== null && AccessPolicy::canEditStadium($user, $stadium)));
        return $canSee ? $stadium : throw new NotFoundException('Stadium not found.');
    }

    /** A stadium the user manages; 404 when out of scope. */
    public function findManageable(User $user, string $code): Stadium
    {
        $stadium = $this->stadiums->findByCode($code);
        if ($stadium === null || !AccessPolicy::canEditStadium($user, $stadium)) {
            throw new NotFoundException('Stadium not found.');
        }
        return $stadium;
    }

    /** Admin only: inserts the address and the stadium in one transaction. */
    public function create(StadiumFormDto $dto): Stadium
    {
        $cityId = $this->cities->findActiveByCode($dto->cityCode)->id;
        $managerId = $this->resolveManagerId($dto->managerCode);
        $id = Database::transaction(function () use ($dto, $cityId, $managerId) {
            $addressId = $this->addresses->insert($this->addressColumns($dto, $cityId));
            return $this->stadiums->insert([
                ...$this->stadiumColumns($dto),
                'address_id' => $addressId,
                'manager_id' => $managerId,
            ]);
        });
        return $this->stadiums->findById($id);
    }

    /** Admin or assigned manager. Only admins may change the manager. */
    public function update(User $actor, string $code, StadiumFormDto $dto): Stadium
    {
        $stadium = $this->findManageable($actor, $code);
        $city = $stadium->address->cityCode === $dto->cityCode
            ? $this->cities->findByCode($dto->cityCode)
            : $this->cities->findActiveByCode($dto->cityCode);
        if ($stadium->isActive && !$dto->isActive) {
            $this->assertNoFutureBookings($stadium);
        }

        $columns = $this->stadiumColumns($dto);
        if ($actor->isAdmin()) {
            $columns['manager_id'] = $this->resolveManagerId($dto->managerCode);
        }
        Database::transaction(function () use ($stadium, $dto, $city, $columns) {
            $this->addresses->update($stadium->addressId, $this->addressColumns($dto, $city->id));
            $this->stadiums->update($stadium->id, $columns);
        });
        return $this->stadiums->findById($stadium->id);
    }

    public function toggleActive(User $actor, string $code): Stadium
    {
        $stadium = $this->findManageable($actor, $code);
        if ($stadium->isActive) {
            $this->assertNoFutureBookings($stadium);
        }
        $this->stadiums->update($stadium->id, ['is_active' => (int) !$stadium->isActive]);
        return $this->stadiums->findById($stadium->id);
    }

    public function countActive(): int
    {
        return $this->stadiums->countActive();
    }

    private function assertNoFutureBookings(Stadium $stadium): void
    {
        $count = $this->reservations->countFutureBlocking($stadium->id);
        if ($count > 0) {
            throw new ConflictException(
                "This stadium has $count upcoming booking(s). Cancel or move them before deactivating.",
                ErrorCodes::STADIUM_HAS_BOOKINGS,
                ['count' => $count]
            );
        }
    }

    private function resolveManagerId(string $managerCode): ?int
    {
        if ($managerCode === '') {
            return null;
        }
        $manager = $this->users->findByCode($managerCode);
        if ($manager === null || !$manager->isManager() || !$manager->isActive) {
            throw ValidationException::forField('managerCode', 'Choose an active manager.');
        }
        return $manager->id;
    }

    private function stadiumColumns(StadiumFormDto $dto): array
    {
        return [
            'name' => $dto->name,
            'description' => $dto->description,
            'sport_type' => $dto->sportType->value,
            'surface_type' => $dto->surfaceType->value,
            'capacity' => $dto->capacity,
            'price_per_hour' => $dto->pricePerHour,
            'price_per_day' => $dto->pricePerDay,
            'opening_time' => $dto->openingTime,
            'closing_time' => $dto->closingTime,
            'slot_minutes' => $dto->slotMinutes,
            'is_active' => (int) $dto->isActive,
        ];
    }

    private function addressColumns(StadiumFormDto $dto, int $cityId): array
    {
        return [
            'city_id' => $cityId,
            'street' => $dto->street,
            'building' => $dto->building,
            'block' => $dto->block,
            'area' => $dto->area,
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
        ];
    }
}
