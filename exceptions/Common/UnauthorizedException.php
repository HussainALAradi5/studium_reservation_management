<?php

declare(strict_types=1);

namespace App\Exceptions\Common;

use App\Constants\Common\ErrorCodes;

final class UnauthorizedException extends AppException
{
    public function __construct(string $message = "Please sign in to continue.", array $details = [])
    {
        parent::__construct(ErrorCodes::UNAUTHENTICATED, $message, 401, $details);
    }
}
