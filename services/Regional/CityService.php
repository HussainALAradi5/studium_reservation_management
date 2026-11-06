<?php

declare(strict_types=1);

namespace App\Services\Regional;

use App\Constants\Common\ErrorCodes;
use App\Dtos\Common\PageResult;
use App\Dtos\Regional\CityFormDto;
use App\Dtos\Regional\CityQuery;
use App\Exceptions\Common\ConflictException;
use App\Exceptions\Common\NotFoundException;
use App\Models\Regional\City;
use App\Repositories\Regional\CityRepository;

final class CityService
{
    public function __construct(private readonly CityRepository $cities = new CityRepository())
    {
    }

    public function list(CityQuery $query): PageResult
    {
        return $this->cities->paginate($query);
    }

    /** @return City[] */
    public function listActive(): array
    {
        return $this->cities->listActive();
    }

    public function findByCode(string $code): City
    {
        return $this->cities->findByCode($code) ?? throw new NotFoundException('City not found.');
    }

    /** Active city for a new or edited address; 422-style conflict when inactive. */
    public function findActiveByCode(string $code): City
    {
        $city = $this->findByCode($code);
        if (!$city->isActive) {
            throw new ConflictException('This city is inactive.', ErrorCodes::CONFLICT, ['fields' => ['cityCode' => 'This city is inactive.']]);
        }
        return $city;
    }

    public function create(CityFormDto $dto): City
    {
        $this->assertUniqueName($dto);
        $id = $this->cities->insert($this->columns($dto));
        return $this->cities->findById($id);
    }

    public function update(string $code, CityFormDto $dto): City
    {
        $city = $this->findByCode($code);
        $this->assertUniqueName($dto, $city->id);
        $this->cities->update($city->id, $this->columns($dto));
        return $this->cities->findById($city->id);
    }

    public function delete(string $code): void
    {
        $city = $this->findByCode($code);
        if ($this->cities->countAddresses($city->id) > 0) {
            throw new ConflictException('This city has stadiums. Deactivate it instead of deleting.', ErrorCodes::CITY_IN_USE);
        }
        $this->cities->softDelete($city->id);
    }

    private function assertUniqueName(CityFormDto $dto, ?int $excludeId = null): void
    {
        if ($this->cities->existsByName($dto->name, $dto->countryCode, $excludeId)) {
            throw ConflictException::duplicate('name', 'City name');
        }
    }

    private function columns(CityFormDto $dto): array
    {
        return ['name' => $dto->name, 'country_code' => $dto->countryCode, 'is_active' => (int) $dto->isActive];
    }
}
