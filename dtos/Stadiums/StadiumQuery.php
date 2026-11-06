<?php

declare(strict_types=1);

namespace App\Dtos\Stadiums;

use App\Constants\Common\Pagination;
use App\Dtos\Common\PageRequest;
use App\Enums\Stadiums\SportType;
use App\Validations\Common\QueryFilters;

final class StadiumQuery
{
    public function __construct(
        public readonly PageRequest $page,
        public readonly string $search = '',
        public readonly string $cityCode = '',
        public readonly ?SportType $sportType = null,
        public readonly ?bool $isActive = null
    ) {
    }

    public static function fromQuery(array $query, int $defaultPageSize = Pagination::DEFAULT_PAGE_SIZE): self
    {
        $status = $query['status'] ?? '';
        $query['pageSize'] ??= $defaultPageSize;
        return new self(
            PageRequest::fromQuery($query, ['name', 'price', 'created'], 'name', Pagination::SORT_ASC),
            QueryFilters::text($query, 'q'),
            QueryFilters::code($query, 'city'),
            QueryFilters::enum($query, 'sport', SportType::class),
            $status === 'active' ? true : ($status === 'inactive' ? false : null)
        );
    }
}
