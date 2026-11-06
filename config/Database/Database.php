<?php

declare(strict_types=1);

namespace App\Config\Database;

use App\Config\App\Env;
use PDO;

/** One shared PDO connection per request. */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::connect(true);
        }
        return self::$pdo;
    }

    /** Connect with or without selecting the database (the migration runner creates it first). */
    public static function connect(bool $selectDatabase): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=utf8mb4%s',
            Env::string('DB_HOST', 'localhost'),
            Env::int('DB_PORT', 3306),
            $selectDatabase ? ';dbname=' . self::name() : ''
        );
        $pdo = new PDO($dsn, Env::string('DB_USER', 'root'), Env::string('DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");
        return $pdo;
    }

    public static function name(): string
    {
        return Env::string('DB_NAME', 'studium_reservation_management');
    }

    /** Runs $work inside a transaction and returns its result; rolls back on any error. */
    public static function transaction(callable $work): mixed
    {
        $pdo = self::connection();
        $pdo->beginTransaction();
        try {
            $result = $work($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
