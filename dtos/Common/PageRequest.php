<?php

declare(strict_types=1);

namespace App\Dtos\Common;

use App\Constants\Common\Pagination;

/** Page, size, and an allowlisted sort taken from the query string. Domain filters live in each query DTO. */
final class PageRequest
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $pageSize = Pagination::DEFAULT_PAGE_SIZE,
        public readonly string $sortBy = '',
        public readonly string $sortDirection = Pagination::SORT_DESC
    ) {
    }

    /** @param string[] $allowedSorts */
    public static function fromQuery(array $query, array $allowedSorts, string $defaultSort, string $defaultDirection = Pagination::SORT_DESC): self
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $size = (int) ($query['pageSize'] ?? Pagination::DEFAULT_PAGE_SIZE);
        $size = min(max(1, $size), Pagination::MAX_PAGE_SIZE);
        $sort = in_array($query['sort'] ?? '', $allowedSorts, true) ? $query['sort'] : $defaultSort;
        $direction = strtolower((string) ($query['direction'] ?? $defaultDirection)) === Pagination::SORT_ASC
            ? Pagination::SORT_ASC
            : Pagination::SORT_DESC;
        return new self($page, $size, $sort, $direction);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }
}
