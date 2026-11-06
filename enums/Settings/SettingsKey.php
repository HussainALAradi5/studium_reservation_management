<?php

declare(strict_types=1);

namespace App\Enums\Settings;

use App\Enums\Common\HasOptions;

/**
 * Every admin-editable setting. Each key carries its default, validation range, and help text,
 * so the settings form, validation, and fallbacks come from one place.
 */
enum SettingsKey: string
{
    use HasOptions;

    case AUTO_CONFIRM_BOOKINGS = 'reservation.auto_confirm';
    case MIN_LEAD_MINUTES = 'reservation.min_lead_minutes';
    case MAX_ADVANCE_DAYS = 'reservation.max_advance_days';
    case MAX_HOURS_PER_BOOKING = 'reservation.max_hours';
    case MAX_DAYS_PER_BOOKING = 'reservation.max_days';
    case CANCEL_CUTOFF_HOURS = 'reservation.cancel_cutoff_hours';
    case SUGGESTION_LIMIT = 'reservation.suggestion_limit';

    public function isBoolean(): bool
    {
        return $this === self::AUTO_CONFIRM_BOOKINGS;
    }

    public function defaultValue(): int
    {
        return match ($this) {
            self::AUTO_CONFIRM_BOOKINGS => 0,
            self::MIN_LEAD_MINUTES => 15,
            self::MAX_ADVANCE_DAYS => 60,
            self::MAX_HOURS_PER_BOOKING => 4,
            self::MAX_DAYS_PER_BOOKING => 7,
            self::CANCEL_CUTOFF_HOURS => 2,
            self::SUGGESTION_LIMIT => 5,
        };
    }

    /** @return array{0: int, 1: int} allowed min and max */
    public function range(): array
    {
        return match ($this) {
            self::AUTO_CONFIRM_BOOKINGS => [0, 1],
            self::MIN_LEAD_MINUTES => [0, 1440],
            self::MAX_ADVANCE_DAYS => [1, 365],
            self::MAX_HOURS_PER_BOOKING => [1, 24],
            self::MAX_DAYS_PER_BOOKING => [1, 30],
            self::CANCEL_CUTOFF_HOURS => [0, 168],
            self::SUGGESTION_LIMIT => [1, 10],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::AUTO_CONFIRM_BOOKINGS => 'Confirm bookings automatically',
            self::MIN_LEAD_MINUTES => 'Minimum notice (minutes)',
            self::MAX_ADVANCE_DAYS => 'Book up to (days ahead)',
            self::MAX_HOURS_PER_BOOKING => 'Longest hourly booking (hours)',
            self::MAX_DAYS_PER_BOOKING => 'Longest daily booking (days)',
            self::CANCEL_CUTOFF_HOURS => 'Customer cancel cutoff (hours before start)',
            self::SUGGESTION_LIMIT => 'Alternative slots offered on conflict',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::AUTO_CONFIRM_BOOKINGS => 'When on, free slots are confirmed instantly instead of waiting for a manager.',
            self::MIN_LEAD_MINUTES => 'Bookings must start at least this long from now.',
            self::MAX_ADVANCE_DAYS => 'How far ahead customers can book.',
            self::MAX_HOURS_PER_BOOKING => 'Upper limit for one hourly rental.',
            self::MAX_DAYS_PER_BOOKING => 'Upper limit for one full-day rental.',
            self::CANCEL_CUTOFF_HOURS => 'Customers cannot cancel a confirmed booking later than this.',
            self::SUGGESTION_LIMIT => 'How many free alternatives to show when a time is taken.',
        };
    }

    /** Form field name, e.g. "reservation_max_hours". */
    public function fieldName(): string
    {
        return str_replace('.', '_', $this->value);
    }
}
