<?php

declare(strict_types=1);

namespace App\Exceptions\Common;

use App\Constants\Common\ErrorCodes;

final class ForbiddenException extends AppException
{
    public function __construct(string $message = "You do not have permission to do this.", array $details = [])
    {
        parent::__construct(ErrorCodes::FORBIDDEN, $message, 403, $details);
    }
}
