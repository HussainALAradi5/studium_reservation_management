<?php

namespace App\Utilities;

class FilterUtility
{
    /**
     * Builds a safe WHERE clause and binding array for PDO dynamically.
     * 
     * @param array $searchableColumns Columns to apply the 'LIKE' search term.
     * @param array $exactMatchColumns Columns that require an exact '=' match.
     * @param array $filters The raw input array ($_GET).
     */
    public static function buildQuery(array $searchableColumns, array $exactMatchColumns, array $filters): array
    {
        $conditions = [];
        $bindings = [];

        // 1. Global Search
        if (!empty($filters['search']) && !empty($searchableColumns)) {
            $searchTerm = '%' . $filters['search'] . '%';
            $searchConditions = [];
            foreach ($searchableColumns as $column) {
                $searchConditions[] = "$column LIKE ?";
                $bindings[] = $searchTerm;
            }
            $conditions[] = "(" . implode(" OR ", $searchConditions) . ")";
        }

        // 2. Dynamic Exact Matches (e.g., status, role, category)
        foreach ($exactMatchColumns as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $conditions[] = "$column = ?";
                $bindings[] = $filters[$column];
            }
        }

        $whereClause = empty($conditions) ? "" : "WHERE " . implode(" AND ", $conditions);

        return [$whereClause, $bindings];
    }
}