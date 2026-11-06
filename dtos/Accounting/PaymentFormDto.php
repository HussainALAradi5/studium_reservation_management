<?php

declare(strict_types=1);

namespace App\Dtos\Accounting;

use App\Constants\Accounting\AccountingRules;
use App\Constants\Common\RegexPatterns;
use App\Enums\Accounting\PaymentMethod;
use App\Enums\Accounting\TransactionType;
use App\Validations\Common\Validator;

final class PaymentFormDto
{
    public function __construct(
        public readonly TransactionType $type,
        public readonly PaymentMethod $method,
        public readonly float $amount,
        public readonly \DateTimeImmutable $paidAt,
        public readonly ?string $reference,
        public readonly ?string $notes
    ) {
    }

    public static function fromInput(array $input): self
    {
        $validator = (new Validator($input))
            ->enum('type', 'transaction type', TransactionType::class)
            ->enum('method', 'payment method', PaymentMethod::class)
            ->decimal('amount', 'Amount', 0.001, AccountingRules::MAX_AMOUNT)
            ->dateTime('paidAt', 'Paid at')
            ->string('reference', 'Reference', 1, 80, false, RegexPatterns::PAYMENT_REFERENCE)
            ->string('notes', 'Notes', 1, 255, false);
        if ($validator->value('paidAt') instanceof \DateTimeImmutable && $validator->value('paidAt') > new \DateTimeImmutable()) {
            $validator->addError('paidAt', 'Payment time cannot be in the future.');
        }
        $data = $validator->validated();
        return new self(
            $data['type'],
            $data['method'],
            round($data['amount'], AccountingRules::AMOUNT_DECIMALS),
            $data['paidAt'],
            $data['reference'],
            $data['notes']
        );
    }
}
