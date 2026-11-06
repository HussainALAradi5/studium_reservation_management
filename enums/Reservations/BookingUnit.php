<?php

declare(strict_types=1);

namespace App\Enums\Reservations;

use App\Enums\Common\HasOptions;

/** A rental is priced and sized either by the hour or by whole opening days. */
enum BookingUnit: string
{
    use HasOptions;

    case HOUR = 'hour';
    case DAY = 'day';

    public function label(): string
    {
        return match ($this) {
            self::HOUR => 'Hourly',
            self::DAY => 'Full day',
        };
    }

    /** "1.5 hours", "1 hour", "3 days". */
    public function describe(float $count): string
    {
        $number = rtrim(rtrim(number_format($count, 2, '.', ''), '0'), '.');
        $singular = (float) $number === 1.0;
        return match ($this) {
            self::HOUR => $number . ($singular ? ' hour' : ' hours'),
            self::DAY => $number . ($singular ? ' day' : ' days'),
        };
    }
}
