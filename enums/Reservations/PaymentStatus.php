<?php

declare(strict_types=1);

namespace App\Enums\Reservations;

use App\Enums\Common\HasOptions;

/** Derived from paid and total amounts (docs: workflows/accounting.md). */
enum PaymentStatus: string
{
    use HasOptions;

    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case REFUNDED = 'refunded';

    public static function fromAmounts(float $paid, float $total, bool $hasRefunds): self
    {
        if ($paid <= 0) {
            return $hasRefunds ? self::REFUNDED : self::UNPAID;
        }
        return $paid >= $total ? self::PAID : self::PARTIAL;
    }

    public function tone(): string
    {
        return match ($this) {
            self::UNPAID => 'neutral',
            self::PARTIAL => 'warning',
            self::PAID => 'success',
            self::REFUNDED => 'info',
        };
    }
}
