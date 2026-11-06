<?php

declare(strict_types=1);

namespace App\Utilities\Pagination;

use App\Dtos\Common\PageRequest;
use App\Dtos\Common\PageResult;
use PDO;

/**
 * The one pagination utility for every list: counts with the same FROM/WHERE, then fetches one page
 * with LIMIT/OFFSET in the database. Sort keys map to SQL columns through an allowlist.
 */
final class Paginator
{
    /**
     * @param string $select    columns, e.g. "s.*, c.name AS city_name"
     * @param string $fromWhere "FROM stadiums s JOIN ... WHERE ..."
     * @param array<string, string> $sortColumns sort key => SQL column
     * @param callable(array): mixed $mapper row => item
     */
    public static function paginate(
        PDO $pdo,
        string $select,
        string $fromWhere,
        array $params,
        PageRequest $request,
        array $sortColumns,
        callable $mapper
    ): PageResult {
        $count = $pdo->prepare("SELECT COUNT(*) $fromWhere");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $column = $sortColumns[$request->sortBy] ?? reset($sortColumns);
        $direction = strtoupper($request->sortDirection);
        $sql = "SELECT $select $fromWhere ORDER BY $column $direction LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value);
        }
        $stmt->bindValue(':limit', $request->pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $request->offset(), PDO::PARAM_INT);
        $stmt->execute();

        return new PageResult(array_map($mapper, $stmt->fetchAll()), $request->page, $request->pageSize, $total);
    }
}
