<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Enums\Accounting\PaymentMethod;
use App\Enums\Accounting\TransactionType;
use App\Models\Base\BaseEntity;

final class Payment extends BaseEntity
{
    public int $reservationId = 0;
    public TransactionType $type;
    public PaymentMethod $method;
    public float $amount = 0.0;
    public string $paidAt = '';
    public ?string $reference = null;
    public ?string $notes = null;
    public string $recordedByName = '';

    public static function fromRow(array $row): static
    {
        $payment = new self();
        $payment->fillBase($row);
        $payment->reservationId = (int) $row['reservation_id'];
        $payment->type = TransactionType::from($row['type']);
        $payment->method = PaymentMethod::from($row['method']);
        $payment->amount = (float) $row['amount'];
        $payment->paidAt = $row['paid_at'];
        $payment->reference = $row['reference'];
        $payment->notes = $row['notes'];
        $payment->recordedByName = (string) ($row['recorded_by_name'] ?? '');
        return $payment;
    }

    public function isRefund(): bool
    {
        return $this->type === TransactionType::REFUND;
    }
}
