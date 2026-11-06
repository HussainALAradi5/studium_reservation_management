<?php

declare(strict_types=1);

namespace App\Dtos\Accounting;

use App\Constants\Accounting\AccountingRules;
use App\Exceptions\Common\ValidationException;
use App\Validations\Common\QueryFilters;

/** Report period (defaults to the current month) and optional stadium. */
final class ReportQuery
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly string $stadiumCode
    ) {
    }

    public static function fromQuery(array $query): self
    {
        $from = QueryFilters::date($query, 'from') ?? date('Y-m-01');
        $to = QueryFilters::date($query, 'to') ?? date('Y-m-t');
        if ($from > $to) {
            throw ValidationException::forField('to', 'The end date must be on or after the start date.');
        }
        $days = (new \DateTimeImmutable($from))->diff(new \DateTimeImmutable($to))->days;
        if ($days > AccountingRules::MAX_REPORT_DAYS) {
            throw ValidationException::forField('to', 'Choose a period of at most ' . AccountingRules::MAX_REPORT_DAYS . ' days.');
        }
        return new self($from, $to, QueryFilters::code($query, 'stadium'));
    }
}
