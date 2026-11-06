<?php

declare(strict_types=1);

namespace App\Enums\Stadiums;

use App\Enums\Common\HasOptions;

enum SportType: string
{
    use HasOptions;

    case FOOTBALL = 'football';
    case FUTSAL = 'futsal';
    case BASKETBALL = 'basketball';
    case VOLLEYBALL = 'volleyball';
    case TENNIS = 'tennis';
    case PADEL = 'padel';
    case MULTI_SPORT = 'multi_sport';
}
