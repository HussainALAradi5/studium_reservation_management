<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

use App\Enums\Common\HasOptions;

enum PaymentMethod: string
{
    use HasOptions;

    case CASH = 'cash';
    case CARD = 'card';
    case BANK_TRANSFER = 'bank_transfer';
    case ONLINE = 'online';
}
