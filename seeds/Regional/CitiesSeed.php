<?php

declare(strict_types=1);

namespace App\Seeds\Regional;

use App\Constants\Stadiums\StadiumRules;
use App\Repositories\Regional\CityRepository;

final class CitiesSeed
{
    /** Demo cities (also exported for live use by bin/export-demo-sql.php). */
    public const CITIES = ['Manama', 'Muharraq', 'Riffa', 'Isa Town', 'Hamad Town', 'Sitra'];

    /** @return array<string, int> name => id */
    public function run(): array
    {
        $cities = new CityRepository();
        $ids = [];
        foreach (self::CITIES as $name) {
            if (!$cities->existsByName($name, StadiumRules::DEFAULT_COUNTRY_CODE)) {
                $ids[$name] = $cities->insert(['name' => $name, 'country_code' => StadiumRules::DEFAULT_COUNTRY_CODE, 'is_active' => 1]);
            }
        }
        echo 'Seeded cities: ' . count($ids) . " new.\n";
        return $ids;
    }
}
