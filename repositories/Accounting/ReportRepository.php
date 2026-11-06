<?php

declare(strict_types=1);

namespace App\Repositories\Accounting;

use App\Config\Database\Database;
use App\Enums\Accounting\TransactionType;
use App\Enums\Reservations\ReservationStatus;
use PDO;

/**
 * Aggregate queries for the accounting report. Every method takes the stadium ids already
 * resolved from the user's scope, so the SQL never widens access.
 */
final class ReportRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /** @return array<int, array{stadium_id: int, gross: float, refunds: float}> */
    public function cashByStadium(array $stadiumIds, string $from, string $to): array
    {
        [$in, $params] = $this->inClause($stadiumIds);
        $sql = "SELECT r.stadium_id,
                    SUM(CASE WHEN p.type = :payment THEN p.amount ELSE 0 END) AS gross,
                    SUM(CASE WHEN p.type = :refund THEN p.amount ELSE 0 END) AS refunds
                FROM payments p JOIN reservations r ON r.id = p.reservation_id
                WHERE p.deleted_at IS NULL AND r.stadium_id IN ($in) AND p.paid_at BETWEEN :from_at AND :to_at
                GROUP BY r.stadium_id";
        return $this->keyed($sql, [...$params, ...$this->range($from, $to), ...$this->types()], 'stadium_id');
    }

    /** @return array<string, float> date => net cash */
    public function dailyNetCash(array $stadiumIds, string $from, string $to): array
    {
        [$in, $params] = $this->inClause($stadiumIds);
        $sql = "SELECT DATE(p.paid_at) AS day,
                    SUM(CASE WHEN p.type = :payment THEN p.amount ELSE -p.amount END) AS net
                FROM payments p JOIN reservations r ON r.id = p.reservation_id
                WHERE p.deleted_at IS NULL AND r.stadium_id IN ($in) AND p.paid_at BETWEEN :from_at AND :to_at
                GROUP BY DATE(p.paid_at)";
        $params = [...$params, ...$this->range($from, $to), 'payment' => TransactionType::PAYMENT->value];
        return array_map('floatval', array_column($this->rows($sql, $params), 'net', 'day'));
    }

    /** @return array<int, array{stadium_id: int, total: float}> */
    public function expensesByStadium(array $stadiumIds, string $from, string $to): array
    {
        [$in, $params] = $this->inClause($stadiumIds);
        $sql = "SELECT stadium_id, SUM(amount) AS total FROM expenses
                WHERE deleted_at IS NULL AND stadium_id IN ($in) AND expense_date BETWEEN :from_date AND :to_date
                GROUP BY stadium_id";
        return $this->keyed($sql, [...$params, 'from_date' => $from, 'to_date' => $to], 'stadium_id');
    }

    /** @return array<string, float> category => total */
    public function expensesByCategory(array $stadiumIds, string $from, string $to): array
    {
        [$in, $params] = $this->inClause($stadiumIds);
        $sql = "SELECT category, SUM(amount) AS total FROM expenses
                WHERE deleted_at IS NULL AND stadium_id IN ($in) AND expense_date BETWEEN :from_date AND :to_date
                GROUP BY category ORDER BY total DESC";
        $rows = $this->rows($sql, [...$params, 'from_date' => $from, 'to_date' => $to]);
        return array_map('floatval', array_column($rows, 'total', 'category'));
    }

    /**
     * Reservations starting in the period, per stadium and status, with booked value, outstanding, and minutes.
     * @return array<int, array{stadium_id: int, status: string, bookings: int, booked_value: float, outstanding: float, minutes: int}>
     */
    public function bookingsByStadiumAndStatus(array $stadiumIds, string $from, string $to): array
    {
        [$in, $params] = $this->inClause($stadiumIds);
        $sql = "SELECT stadium_id, status, COUNT(*) AS bookings, SUM(total_amount) AS booked_value,
                    SUM(GREATEST(total_amount - paid_amount, 0)) AS outstanding, SUM(duration_minutes) AS minutes
                FROM reservations
                WHERE deleted_at IS NULL AND stadium_id IN ($in) AND start_at BETWEEN :from_at AND :to_at
                GROUP BY stadium_id, status";
        return $this->rows($sql, [...$params, ...$this->range($from, $to)]);
    }

    public static function billableStatuses(): array
    {
        return [ReservationStatus::CONFIRMED->value, ReservationStatus::COMPLETED->value];
    }

    private function inClause(array $ids): array
    {
        $ids = $ids === [] ? [0] : array_values($ids);
        $params = [];
        foreach ($ids as $index => $id) {
            $params["sid$index"] = (int) $id;
        }
        return [implode(', ', array_map(static fn ($key) => ":$key", array_keys($params))), $params];
    }

    private function range(string $from, string $to): array
    {
        return ['from_at' => "$from 00:00:00", 'to_at' => "$to 23:59:59"];
    }

    private function types(): array
    {
        return ['payment' => TransactionType::PAYMENT->value, 'refund' => TransactionType::REFUND->value];
    }

    private function rows(string $sql, array $params): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function keyed(string $sql, array $params, string $key): array
    {
        $result = [];
        foreach ($this->rows($sql, $params) as $row) {
            $result[(int) $row[$key]] = $row;
        }
        return $result;
    }
}
