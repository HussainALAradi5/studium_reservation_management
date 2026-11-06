<?php

declare(strict_types=1);

namespace App\Dtos\Common;

/**
 * Which rows a user may see: everything (admin), stadiums they manage (manager), or their own bookings (customer).
 * Built by AccessPolicy::scopeFor(); repositories turn it into SQL.
 */
final class AccessScope
{
    private function __construct(
        public readonly ?int $managerId,
        public readonly ?int $customerId
    ) {
    }

    public static function all(): self
    {
        return new self(null, null);
    }

    public static function manager(int $userId): self
    {
        return new self($userId, null);
    }

    public static function customer(int $userId): self
    {
        return new self(null, $userId);
    }

    /**
     * SQL condition for queries that join stadiums (alias $stadium) and optionally reservations (alias $reservation).
     * @return array{0: string, 1: array}
     */
    public function toSql(string $stadium = 's', ?string $reservation = 'r'): array
    {
        if ($this->managerId !== null) {
            return ["$stadium.manager_id = :scope_manager_id", ['scope_manager_id' => $this->managerId]];
        }
        if ($this->customerId !== null && $reservation !== null) {
            return ["$reservation.customer_id = :scope_customer_id", ['scope_customer_id' => $this->customerId]];
        }
        return ['1 = 1', []];
    }
}
