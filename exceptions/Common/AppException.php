<?php

declare(strict_types=1);

namespace App\Exceptions\Common;

/** Base for every expected error: carries an error code, HTTP status, and optional details. */
class AppException extends \RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status = 400,
        public readonly array $details = []
    ) {
        parent::__construct($message);
    }
}
