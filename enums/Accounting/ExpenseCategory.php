<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

use App\Enums\Common\HasOptions;

enum ExpenseCategory: string
{
    use HasOptions;

    case MAINTENANCE = 'maintenance';
    case UTILITIES = 'utilities';
    case SALARIES = 'salaries';
    case EQUIPMENT = 'equipment';
    case RENT = 'rent';
    case MARKETING = 'marketing';
    case OTHER = 'other';
}
