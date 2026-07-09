<?php

namespace App\Utilities;

class PaginationUtility
{
    public static function calculate(int $totalRecords, int $currentPage, int $perPage = 10): array
    {
        $totalPages = max(1, ceil($totalRecords / $perPage));
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $perPage;

        return [
            'limit' => $perPage,
            'offset' => $offset,
            'total_pages' => (int) $totalPages,
            'current_page' => (int) $currentPage,
            'total_records' => (int) $totalRecords
        ];
    }
}