<?php

declare(strict_types=1);

namespace App\Exceptions\Common;

use App\Constants\Common\ErrorCodes;

final class TooManyRequestsException extends AppException
{
    public function __construct(string $message = "Too many attempts. Please try again later.", array $details = [])
    {
        parent::__construct(ErrorCodes::TOO_MANY_ATTEMPTS, $message, 429, $details);
    }
}
