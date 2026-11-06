<?php

declare(strict_types=1);

namespace App\Utilities\Formatting;

use App\Config\App\AppConfig;
use App\Constants\Accounting\AccountingRules;

final class Money
{
    public static function round(float $amount): float
    {
        return round($amount, AccountingRules::AMOUNT_DECIMALS);
    }

    public static function format(float $amount): string
    {
        return number_format($amount, AccountingRules::AMOUNT_DECIMALS) . ' ' . AppConfig::currency();
    }
}
