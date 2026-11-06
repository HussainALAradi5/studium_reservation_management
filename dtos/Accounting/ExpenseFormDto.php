<?php

declare(strict_types=1);

namespace App\Dtos\Accounting;

use App\Constants\Accounting\AccountingRules;
use App\Constants\Common\RegexPatterns;
use App\Enums\Accounting\ExpenseCategory;
use App\Validations\Common\Validator;

final class ExpenseFormDto
{
    public function __construct(
        public readonly string $stadiumCode,
        public readonly ExpenseCategory $category,
        public readonly float $amount,
        public readonly string $expenseDate,
        public readonly string $description
    ) {
    }

    public static function fromInput(array $input): self
    {
        $data = (new Validator($input))
            ->string('stadiumCode', 'Stadium', 12, 12, true, RegexPatterns::RECORD_CODE)
            ->enum('category', 'category', ExpenseCategory::class)
            ->decimal('amount', 'Amount', 0.001, AccountingRules::MAX_AMOUNT)
            ->date('expenseDate', 'Date', true, false)
            ->string('description', 'Description', 3, 255)
            ->validated();
        return new self(
            $data['stadiumCode'],
            $data['category'],
            round($data['amount'], AccountingRules::AMOUNT_DECIMALS),
            $data['expenseDate'],
            $data['description']
        );
    }
}
