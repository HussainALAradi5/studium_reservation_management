<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Config\Database\Database;
use PDO;

/** Failed sign-in log used for brute-force throttling (not an audited entity). */
final class LoginAttemptRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function countSince(string $identifier, string $ip, string $since): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE identifier = :identifier AND ip_address = :ip AND attempted_at >= :since'
        );
        $stmt->execute(['identifier' => $identifier, 'ip' => $ip, 'since' => $since]);
        return (int) $stmt->fetchColumn();
    }

    public function record(string $identifier, string $ip): void
    {
        $this->pdo->prepare('INSERT INTO login_attempts (identifier, ip_address, attempted_at) VALUES (:identifier, :ip, :now)')
            ->execute(['identifier' => $identifier, 'ip' => $ip, 'now' => date('Y-m-d H:i:s')]);
    }

    public function clear(string $identifier, string $ip): void
    {
        $this->pdo->prepare('DELETE FROM login_attempts WHERE identifier = :identifier AND ip_address = :ip')
            ->execute(['identifier' => $identifier, 'ip' => $ip]);
    }
}
