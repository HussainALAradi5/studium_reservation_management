<?php
use App\Utilities\MigrationUtility;

try {
    $dsn_server = sprintf("mysql:host=%s;port=%s;charset=utf8mb4", DB_HOST, DB_PORT);
    $pdo = new \PDO($dsn_server, DB_USER, DB_PASS, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

    // 1. Setup DB
    MigrationUtility::createDatabase($pdo, DB_NAME);

    // 2. Load Schema
    $schema = require __DIR__ . '/schema.php';

    // 3. Loop and Create
    foreach ($schema as $tableName => $columns) {
        MigrationUtility::createTable($pdo, $tableName, $columns);
    }

} catch (\PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}