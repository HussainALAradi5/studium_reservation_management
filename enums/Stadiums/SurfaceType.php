<?php

declare(strict_types=1);

namespace App\Enums\Stadiums;

use App\Enums\Common\HasOptions;

enum SurfaceType: string
{
    use HasOptions;

    case NATURAL_GRASS = 'natural_grass';
    case ARTIFICIAL_TURF = 'artificial_turf';
    case HARD_COURT = 'hard_court';
    case WOODEN = 'wooden';
    case SAND = 'sand';
}
