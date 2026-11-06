<?php

declare(strict_types=1);

namespace App\Models\Regional;

use App\Models\Base\BaseEntity;

final class City extends BaseEntity
{
    public string $name = '';
    public string $countryCode = '';
    public bool $isActive = true;
    /** Joined: number of stadiums in the city (list views only). */
    public int $stadiumCount = 0;

    public static function fromRow(array $row): static
    {
        $city = new self();
        $city->fillBase($row);
        $city->name = $row['name'];
        $city->countryCode = $row['country_code'];
        $city->isActive = (bool) $row['is_active'];
        $city->stadiumCount = (int) ($row['stadium_count'] ?? 0);
        return $city;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'countryCode' => $this->countryCode,
            'isActive' => $this->isActive,
            'stadiumCount' => $this->stadiumCount,
        ];
    }
}
