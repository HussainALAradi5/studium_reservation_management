<?php

declare(strict_types=1);

namespace App\Constants\Stadiums;

final class StadiumRules
{
    public const SLOT_MINUTES_OPTIONS = [30, 60, 90, 120];
    public const MIN_CAPACITY = 2;
    public const MAX_CAPACITY = 200;
    public const MAX_DESCRIPTION_LENGTH = 2000;
    public const DEFAULT_COUNTRY_CODE = 'BH';
}
