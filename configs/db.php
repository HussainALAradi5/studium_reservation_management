<?php
// db.php
try {
    $dsn = sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4", DB_HOST, DB_NAME, DB_PORT);
    $pdo = new \PDO($dsn, DB_USER, DB_PASS, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}