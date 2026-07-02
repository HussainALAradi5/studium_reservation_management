<?php

namespace App\Utilities;

class Validation
{
    public static function isDuplicate(\PDO $pdo, string $column, string $value, ?int $excludeCode = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE $column = :value";
        if ($excludeCode !== null) $sql .= " AND code != :excludeCode";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['value' => $value] + ($excludeCode ? ['excludeCode' => $excludeCode] : []));
        return (bool) $stmt->fetchColumn();
    }
}
