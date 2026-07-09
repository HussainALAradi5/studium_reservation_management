<?php

namespace App\Utilities;

class MigrationUtility
{
    public static function createTable(\PDO $pdo, string $tableName, array $columns): void
    {
        $colSql = implode(", ", $columns);
        $sql = "CREATE TABLE IF NOT EXISTS `$tableName` ($colSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
    }

    public static function createDatabase(\PDO $pdo, string $dbName): void
    {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");
    }
}