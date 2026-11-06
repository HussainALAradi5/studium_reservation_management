<?php

declare(strict_types=1);

namespace App\Enums\Users;

use App\Enums\Common\HasOptions;

enum Gender: string
{
    use HasOptions;

    case MALE = 'male';
    case FEMALE = 'female';
}
