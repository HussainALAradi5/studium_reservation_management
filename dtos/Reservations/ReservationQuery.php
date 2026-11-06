<?php

declare(strict_types=1);

namespace App\Dtos\Reservations;

use App\Dtos\Common\PageRequest;
use App\Enums\Reservations\ReservationStatus;
use App\Validations\Common\QueryFilters;

final class ReservationQuery
{
    public function __construct(
        public readonly PageRequest $page,
        public readonly ?ReservationStatus $status = null,
        public readonly string $stadiumCode = '',
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        public readonly string $search = ''
    ) {
    }

    public static function fromQuery(array $query): self
    {
        return new self(
            PageRequest::fromQuery($query, ['start', 'created', 'total'], 'start'),
            QueryFilters::enum($query, 'status', ReservationStatus::class),
            QueryFilters::code($query, 'stadium'),
            QueryFilters::date($query, 'from'),
            QueryFilters::date($query, 'to'),
            QueryFilters::text($query, 'q')
        );
    }
}
