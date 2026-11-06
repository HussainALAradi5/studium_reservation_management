<?php

declare(strict_types=1);

namespace App\Models\Stadiums;

use App\Enums\Stadiums\SportType;
use App\Enums\Stadiums\SurfaceType;
use App\Models\Base\BaseEntity;
use App\Models\Regional\Address;

final class Stadium extends BaseEntity
{
    public ?int $managerId = null;
    public ?string $managerName = null;
    public ?string $managerCode = null;
    public int $addressId = 0;
    public Address $address;
    public string $name = '';
    public ?string $description = null;
    public SportType $sportType;
    public SurfaceType $surfaceType;
    public int $capacity = 0;
    public float $pricePerHour = 0.0;
    /** Null when the stadium is rented by the hour only. */
    public ?float $pricePerDay = null;
    public string $openingTime = '';
    public string $closingTime = '';
    public int $slotMinutes = 60;
    public bool $isActive = true;

    public static function fromRow(array $row): static
    {
        $stadium = new self();
        $stadium->fillBase($row);
        $stadium->managerId = self::nullableInt($row['manager_id']);
        $stadium->managerName = $row['manager_name'] ?? null;
        $stadium->managerCode = $row['manager_code'] ?? null;
        $stadium->addressId = (int) $row['address_id'];
        $stadium->address = Address::fromRow($row, 'address_');
        $stadium->name = $row['name'];
        $stadium->description = $row['description'];
        $stadium->sportType = SportType::from($row['sport_type']);
        $stadium->surfaceType = SurfaceType::from($row['surface_type']);
        $stadium->capacity = (int) $row['capacity'];
        $stadium->pricePerHour = (float) $row['price_per_hour'];
        $stadium->pricePerDay = self::nullableFloat($row['price_per_day']);
        $stadium->openingTime = substr($row['opening_time'], 0, 5);
        $stadium->closingTime = substr($row['closing_time'], 0, 5);
        $stadium->slotMinutes = (int) $row['slot_minutes'];
        $stadium->isActive = (bool) $row['is_active'];
        return $stadium;
    }

    public function allowsDailyBooking(): bool
    {
        return $this->pricePerDay !== null;
    }

    public function closesAfterMidnight(): bool
    {
        return $this->closingTime <= $this->openingTime;
    }

    public function hoursLabel(): string
    {
        return $this->openingTime . ' – ' . $this->closingTime . ($this->closesAfterMidnight() ? ' (next day)' : '');
    }
}
