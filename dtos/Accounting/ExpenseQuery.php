<?php

declare(strict_types=1);

namespace App\Dtos\Accounting;

use App\Dtos\Common\PageRequest;
use App\Enums\Accounting\ExpenseCategory;
use App\Validations\Common\QueryFilters;

final class ExpenseQuery
{
    public function __construct(
        public readonly PageRequest $page,
        public readonly string $stadiumCode = '',
        public readonly ?ExpenseCategory $category = null,
        public readonly ?string $from = null,
        public readonly ?string $to = null
    ) {
    }

    public static function fromQuery(array $query): self
    {
        return new self(
            PageRequest::fromQuery($query, ['date', 'amount', 'created'], 'date'),
            QueryFilters::code($query, 'stadium'),
            QueryFilters::enum($query, 'category', ExpenseCategory::class),
            QueryFilters::date($query, 'from'),
            QueryFilters::date($query, 'to')
        );
    }
}
