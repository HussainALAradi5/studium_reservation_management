<?php

declare(strict_types=1);

namespace App\Enums\Users;

use App\Enums\Common\HasOptions;

enum UserRole: string
{
    use HasOptions;

    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CUSTOMER = 'customer';
}
