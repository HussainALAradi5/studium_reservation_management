<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

use App\Enums\Common\HasOptions;

enum TransactionType: string
{
    use HasOptions;

    case PAYMENT = 'payment';
    case REFUND = 'refund';
}
