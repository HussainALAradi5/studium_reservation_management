<?php

namespace App\Services;

use App\Utilities\FilterUtility;
use App\Utilities\PaginationUtility;

abstract class BaseService
{
    protected \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function setAlert(string $message, \App\Enums\UI\AlertMessageType $type, string $title = ''): void
    {
        $_SESSION['flash_alert'] = [
            'message' => $message,
            'type'    => $type->value,
            'title'   => $title
        ];
    }
    protected function setDialog(string $title, string $content, bool $isAlertDialog = false): void
    {
    $_SESSION['flash_dialog'] = [
            'title'   => $title,
            'content' => $content,
            'isAlertDialog' => $isAlertDialog
        ];
    }


    /**
     * A highly reusable engine to fetch paginated and filtered data for ANY database table.
     * 
     * @param string $tableName The database table to query.
     * @param string $selectColumns Comma-separated columns (e.g., 'id, name, email').
     * @param array $searchableColumns Columns to apply the 'LIKE' wildcard search.
     * @param array $exactMatchColumns Columns that require an exact '=' match (e.g., 'isActive').
     * @param array $filters The raw input array (usually $_GET).
     * @param int $page Current page number.
     * @param int $perPage Number of records per page.
     * @param string $orderBy SQL ORDER BY string (e.g., 'createdAt DESC').
     * @return array Contains 'data' (results) and 'pagination' (metadata).
     */
    protected function getPaginatedData(
        string $tableName, 
        string $selectColumns, 
        array $searchableColumns, 
        array $exactMatchColumns, 
        array $filters, 
        int $page = 1, 
        int $perPage = 10,
        string $orderBy = 'id DESC'
    ): array {
        
        // 1. Build secure WHERE clause using the FilterUtility
        list($whereClause, $bindings) = FilterUtility::buildQuery($searchableColumns, $exactMatchColumns, $filters);

        // 2. Count total matching records for the math calculation
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM `$tableName` $whereClause");
        $countStmt->execute($bindings);
        $totalRecords = (int) $countStmt->fetchColumn();

        // 3. Delegate the math to the PaginationUtility
        $pagination = PaginationUtility::calculate($totalRecords, $page, $perPage);

        // 4. Execute the final paginated query
        // String interpolation here is safe because $pagination variables are strict integers
        $sql = "SELECT $selectColumns FROM `$tableName` $whereClause ORDER BY $orderBy LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return [
            'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'pagination' => $pagination
        ];
    }


}
