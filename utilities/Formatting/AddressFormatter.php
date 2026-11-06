<?php

declare(strict_types=1);

namespace App\Utilities\Formatting;

/** "Bldg 12, Road 3801, Block 338, Adliya, Manama" — empty parts are skipped. */
final class AddressFormatter
{
    public static function format(?string $building, string $street, ?string $block, ?string $area, ?string $city): string
    {
        $parts = [
            $building ? "Bldg $building" : null,
            $street !== '' ? (ctype_digit($street) ? "Road $street" : $street) : null,
            $block ? "Block $block" : null,
            $area,
            $city,
        ];
        return implode(', ', array_filter($parts, static fn ($part) => $part !== null && $part !== ''));
    }

    public static function googleMapsUrl(float $lat, float $lng): string
    {
        return 'https://www.google.com/maps/search/?api=1&query=' . $lat . ',' . $lng;
    }

    public static function googleDirectionsUrl(float $lat, float $lng): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination=' . $lat . ',' . $lng;
    }
}
