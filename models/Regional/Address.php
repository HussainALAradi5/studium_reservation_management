<?php

declare(strict_types=1);

namespace App\Models\Regional;

use App\Models\Base\BaseEntity;
use App\Utilities\Formatting\AddressFormatter;

final class Address extends BaseEntity
{
    public int $cityId = 0;
    public string $cityCode = '';
    public string $cityName = '';
    public string $street = '';
    public ?string $building = null;
    public ?string $block = null;
    public ?string $area = null;
    public ?float $latitude = null;
    public ?float $longitude = null;

    /** Reads either a plain addresses row or columns prefixed with "address_" from a join. */
    public static function fromRow(array $row, string $prefix = ''): static
    {
        $get = static fn (string $key) => $row[$prefix . $key] ?? null;
        $address = new self();
        $address->id = (int) $get('id');
        $address->code = (string) $get('code');
        $address->cityId = (int) $get('city_id');
        $address->cityCode = (string) ($row['city_code'] ?? '');
        $address->cityName = (string) ($row['city_name'] ?? '');
        $address->street = (string) $get('street');
        $address->building = $get('building');
        $address->block = $get('block');
        $address->area = $get('area');
        $address->latitude = self::nullableFloat($get('latitude'));
        $address->longitude = self::nullableFloat($get('longitude'));
        return $address;
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function formatted(): string
    {
        return AddressFormatter::format($this->building, $this->street, $this->block, $this->area, $this->cityName);
    }
}
