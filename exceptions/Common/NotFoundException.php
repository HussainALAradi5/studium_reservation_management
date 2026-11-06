<?php

declare(strict_types=1);

namespace App\Exceptions\Common;

use App\Constants\Common\ErrorCodes;

final class NotFoundException extends AppException
{
    public function __construct(string $message = "The requested record was not found.", array $details = [])
    {
        parent::__construct(ErrorCodes::NOT_FOUND, $message, 404, $details);
    }
}
