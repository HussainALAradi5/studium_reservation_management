<?php

declare(strict_types=1);

namespace App\Constants\Reservations;

/**
 * Fixed booking rules. Admin-tunable limits (lead time, max hours/days, cutoff, suggestions)
 * live in SettingsKey with their defaults.
 */
final class ReservationRules
{
    public const MIN_HOURLY_MINUTES = 30;
    public const SUGGESTION_SEARCH_DAYS = 7;
    public const CALENDAR_MONTH_PATTERN = '/^\d{4}-(0[1-9]|1[0-2])$/';
    public const EXPIRED_REASON = 'Expired: not confirmed before start time.';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';
}
