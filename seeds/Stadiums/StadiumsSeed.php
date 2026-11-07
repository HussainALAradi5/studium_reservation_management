<?php

declare(strict_types=1);

namespace App\Seeds\Stadiums;

use App\Repositories\Regional\AddressRepository;
use App\Repositories\Stadiums\StadiumRepository;

/** Sample stadiums assigned to the seeded manager; runs only when new cities were created. */
final class StadiumsSeed
{
    /**
     * Demo stadiums: [city, name, sport, surface, capacity, price/hour, price/day, opens, closes, slot,
     * street, block, area, lat, lng]. Also exported for live use by bin/export-demo-sql.php.
     */
    public const STADIUMS = [
        ['Manama', 'Seef Arena', 'football', 'artificial_turf', 22, 25.000, 180.000, '08:00', '02:00', 60, 'Road 2825', '2801', 'Seef', 26.2361, 50.5409],
        ['Riffa', 'Riffa Padel Club', 'padel', 'hard_court', 4, 12.000, null, '07:00', '23:00', 30, 'Road 105', '901', 'East Riffa', 26.1269, 50.5753],
        ['Muharraq', 'Muharraq Sports Hall', 'basketball', 'wooden', 10, 18.000, 120.000, '09:00', '22:00', 60, 'Road 1520', '215', 'Arad', 26.2541, 50.6200],
    ];

    public function run(array $cityIds, int $managerId): void
    {
        $addresses = new AddressRepository();
        $stadiums = new StadiumRepository();
        $count = 0;
        foreach (self::STADIUMS as [$city, $name, $sport, $surface, $capacity, $hourly, $daily, $open, $close, $slot, $street, $block, $area, $lat, $lng]) {
            if (!isset($cityIds[$city])) {
                continue;
            }
            $addressId = $addresses->insert([
                'city_id' => $cityIds[$city],
                'street' => $street,
                'block' => $block,
                'area' => $area,
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
            $stadiums->insert([
                'manager_id' => $managerId,
                'address_id' => $addressId,
                'name' => $name,
                'description' => "Well-kept $sport venue with lighting, changing rooms, and parking.",
                'sport_type' => $sport,
                'surface_type' => $surface,
                'capacity' => $capacity,
                'price_per_hour' => $hourly,
                'price_per_day' => $daily,
                'opening_time' => $open,
                'closing_time' => $close,
                'slot_minutes' => $slot,
                'is_active' => 1,
            ]);
            $count++;
        }
        echo "Seeded stadiums: $count new.\n";
    }
}
