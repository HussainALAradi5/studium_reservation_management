<?php

declare(strict_types=1);

namespace App\Exceptions\Common;

use App\Constants\Common\ErrorCodes;

/** 409 for business-rule conflicts (duplicates, invalid transitions, overlapping bookings). */
final class ConflictException extends AppException
{
    public function __construct(string $message, string $errorCode = ErrorCodes::CONFLICT, array $details = [])
    {
        parent::__construct($errorCode, $message, 409, $details);
    }

    public static function duplicate(string $field, string $label): self
    {
        return new self("$label is already in use.", ErrorCodes::DUPLICATE_VALUE, ['fields' => [$field => "$label is already in use."]]);
    }
}
