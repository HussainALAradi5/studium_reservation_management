<?php

declare(strict_types=1);

namespace App\Exceptions\Common;

use App\Constants\Common\ErrorCodes;

/** 422 with per-field messages: ['email' => 'Enter a valid email.']. */
final class ValidationException extends AppException
{
    public function __construct(array $fieldErrors, string $message = 'Please fix the highlighted fields.')
    {
        parent::__construct(ErrorCodes::VALIDATION_FAILED, $message, 422, ['fields' => $fieldErrors]);
    }

    public static function forField(string $field, string $message): self
    {
        return new self([$field => $message], $message);
    }
}
