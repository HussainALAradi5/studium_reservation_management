<?php

declare(strict_types=1);

namespace App\Dtos\Regional;

use App\Constants\Common\Pagination;
use App\Dtos\Common\PageRequest;
use App\Validations\Common\QueryFilters;

final class CityQuery
{
    public function __construct(
        public readonly PageRequest $page,
        public readonly string $search = ''
    ) {
    }

    public static function fromQuery(array $query): self
    {
        return new self(
            PageRequest::fromQuery($query, ['name', 'created'], 'name', Pagination::SORT_ASC),
            QueryFilters::text($query, 'q')
        );
    }
}
