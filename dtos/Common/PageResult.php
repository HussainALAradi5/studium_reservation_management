<?php

declare(strict_types=1);

namespace App\Dtos\Common;

/** One page of items plus totals. */
final class PageResult
{
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $pageSize,
        public readonly int $totalItems
    ) {
    }

    public function totalPages(): int
    {
        return max(1, (int) ceil($this->totalItems / max(1, $this->pageSize)));
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function meta(): array
    {
        return [
            'page' => $this->page,
            'pageSize' => $this->pageSize,
            'totalItems' => $this->totalItems,
            'totalPages' => $this->totalPages(),
        ];
    }
}
