<?php

declare(strict_types=1);

namespace App\Constants\Reviews;

final class ReviewRules
{
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;
    public const MIN_COMMENT = 3;
    public const MAX_COMMENT = 1000;
    public const PAGE_SIZE = 5;
}
