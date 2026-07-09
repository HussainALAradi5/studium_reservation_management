<?php
// 1. Initial connection without specifying the database
try {
    $dsn_server = sprintf("mysql:host=%s;port=%s;charset=utf8mb4", DB_HOST, DB_PORT);
    $pdo = new \PDO($dsn_server, DB_USER, DB_PASS, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    ]);

    // 2. Create the Database if it does not exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    // 3. Create the Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `code` VARCHAR(50) PRIMARY KEY,
        `userName` VARCHAR(100) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `email` VARCHAR(150) NOT NULL UNIQUE,
        `phoneNumber` VARCHAR(20) NOT NULL UNIQUE,
        `secondPhoneNumber` VARCHAR(20),
        `birthDay` DATE,
        `isActive` TINYINT(1) DEFAULT 1,
        `gender` VARCHAR(10),
        `userType` VARCHAR(20),
        `createdAt` DATETIME,
        `updatedAt` DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Create the User Sequence table (to support your sequence logic)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_sequence` (
        `year` INT PRIMARY KEY,
        `last_id` INT DEFAULT 0
    ) ENGINE=InnoDB");

} catch (\PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}