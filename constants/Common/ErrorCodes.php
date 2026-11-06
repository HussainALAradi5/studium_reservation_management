<?php

declare(strict_types=1);

namespace App\Constants\Common;

/** Error codes returned in every JSON error response (`error.code`). */
final class ErrorCodes
{
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';
    public const FORBIDDEN = 'FORBIDDEN';
    public const NOT_FOUND = 'NOT_FOUND';
    public const METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';
    public const CSRF_MISMATCH = 'CSRF_MISMATCH';
    public const TOO_MANY_ATTEMPTS = 'TOO_MANY_ATTEMPTS';
    public const CONFLICT = 'CONFLICT';
    public const DUPLICATE_VALUE = 'DUPLICATE_VALUE';
    public const SELF_ACTION_FORBIDDEN = 'SELF_ACTION_FORBIDDEN';
    public const MANAGER_HAS_STADIUMS = 'MANAGER_HAS_STADIUMS';
    public const CITY_IN_USE = 'CITY_IN_USE';
    public const STADIUM_HAS_BOOKINGS = 'STADIUM_HAS_BOOKINGS';
    public const RESERVATION_CONFLICT = 'RESERVATION_CONFLICT';
    public const RESERVATION_IN_PAST = 'RESERVATION_IN_PAST';
    public const OUTSIDE_OPENING_HOURS = 'OUTSIDE_OPENING_HOURS';
    public const INVALID_TRANSITION = 'INVALID_TRANSITION';
    public const CANCEL_CUTOFF_PASSED = 'CANCEL_CUTOFF_PASSED';
    public const AMOUNT_EXCEEDS_BALANCE = 'AMOUNT_EXCEEDS_BALANCE';
    public const REVIEW_NOT_ALLOWED = 'REVIEW_NOT_ALLOWED';
    public const REVIEW_ALREADY_EXISTS = 'REVIEW_ALREADY_EXISTS';
    public const SERVER_ERROR = 'SERVER_ERROR';
}
