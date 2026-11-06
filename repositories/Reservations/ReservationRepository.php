<?php

declare(strict_types=1);

namespace App\Repositories\Reservations;

use App\Constants\Common\CodePrefixes;
use App\Constants\Reservations\ReservationRules;
use App\Dtos\Common\AccessScope;
use App\Dtos\Common\PageResult;
use App\Dtos\Reservations\ReservationQuery;
use App\Enums\Reservations\ReservationStatus;
use App\Models\Reservations\Reservation;
use App\Repositories\Base\BaseRepository;
use App\Utilities\Pagination\Paginator;

final class ReservationRepository extends BaseRepository
{
    private const SELECT = 'r.*, s.code AS stadium_code, s.name AS stadium_name, s.manager_id AS stadium_manager_id,
        u.code AS customer_code, u.full_name AS customer_name, u.phone AS customer_phone, c.name AS city_name';

    private const FROM = 'FROM reservations r
        JOIN stadiums s ON s.id = r.stadium_id
        JOIN addresses a ON a.id = s.address_id
        JOIN cities c ON c.id = a.city_id
        JOIN users u ON u.id = r.customer_id';

    private const SORT_COLUMNS = ['start' => 'r.start_at', 'created' => 'r.created_at', 'total' => 'r.total_amount'];

    protected function table(): string
    {
        return 'reservations';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::RESERVATION;
    }

    public function findById(int $id): ?Reservation
    {
        return $this->findOneWhere('r.id = :id', ['id' => $id]);
    }

    public function findByCode(string $code): ?Reservation
    {
        return $this->findOneWhere('r.code = :code', ['code' => $code]);
    }

    public function lockById(int $id): void
    {
        $this->fetchOne('SELECT id FROM reservations WHERE id = :id FOR UPDATE', ['id' => $id]);
    }

    public function paginate(ReservationQuery $query, AccessScope $scope): PageResult
    {
        [$scopeSql, $params] = $scope->toSql();
        $where = ['r.deleted_at IS NULL', $scopeSql];
        if ($query->status !== null) {
            $where[] = 'r.status = :status';
            $params['status'] = $query->status->value;
        }
        if ($query->stadiumCode !== '') {
            $where[] = 's.code = :stadium_code';
            $params['stadium_code'] = $query->stadiumCode;
        }
        if ($query->from !== null) {
            $where[] = 'r.start_at >= :from_date';
            $params['from_date'] = $query->from . ' 00:00:00';
        }
        if ($query->to !== null) {
            $where[] = 'r.start_at <= :to_date';
            $params['to_date'] = $query->to . ' 23:59:59';
        }
        if ($query->search !== '') {
            $where[] = '(r.code LIKE :q1 OR u.full_name LIKE :q2 OR u.phone LIKE :q3 OR s.name LIKE :q4)';
            $like = '%' . $query->search . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like];
        }
        return Paginator::paginate(
            $this->pdo,
            self::SELECT,
            self::FROM . ' WHERE ' . implode(' AND ', $where),
            $params,
            $query->page,
            self::SORT_COLUMNS,
            static fn (array $row) => Reservation::fromRow($row)
        );
    }

    /**
     * Blocking reservations of a stadium overlapping [start, end) — the core conflict query.
     * @return array<int, array{start_at: string, end_at: string}>
     */
    public function findOverlapping(int $stadiumId, string $startAt, string $endAt, ?int $excludeId = null): array
    {
        $sql = 'SELECT start_at, end_at FROM reservations
                WHERE stadium_id = :stadium_id AND deleted_at IS NULL
                  AND status IN (:pending, :confirmed)
                  AND start_at < :end_at AND end_at > :start_at';
        $params = [
            'stadium_id' => $stadiumId,
            'pending' => ReservationStatus::PENDING->value,
            'confirmed' => ReservationStatus::CONFIRMED->value,
            'end_at' => $endAt,
            'start_at' => $startAt,
        ];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        return $this->fetchAllRows($sql . ' ORDER BY start_at', $params);
    }

    /** Cancels pending bookings whose start time passed; returns how many were expired. */
    public function expireStalePending(): int
    {
        return $this->execute(
            'UPDATE reservations SET status = :cancelled, status_reason = :reason, updated_at = :now
             WHERE status = :pending AND start_at <= :now_check AND deleted_at IS NULL',
            [
                'cancelled' => ReservationStatus::CANCELLED->value,
                'reason' => ReservationRules::EXPIRED_REASON,
                'now' => self::now(),
                'pending' => ReservationStatus::PENDING->value,
                'now_check' => self::now(),
            ]
        )->rowCount();
    }

    public function countFutureBlocking(int $stadiumId): int
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM reservations WHERE stadium_id = :id AND end_at > :now
             AND status IN (:pending, :confirmed) AND deleted_at IS NULL',
            [
                'id' => $stadiumId,
                'now' => self::now(),
                'pending' => ReservationStatus::PENDING->value,
                'confirmed' => ReservationStatus::CONFIRMED->value,
            ]
        );
    }

    /** @return Reservation[] next blocking reservations within scope */
    public function listUpcoming(AccessScope $scope, int $limit): array
    {
        [$scopeSql, $params] = $scope->toSql();
        $sql = 'SELECT ' . self::SELECT . ' ' . self::FROM . "
                WHERE r.deleted_at IS NULL AND $scopeSql AND r.end_at > :now AND r.status IN (:pending, :confirmed)
                ORDER BY r.start_at LIMIT " . (int) $limit;
        $rows = $this->fetchAllRows($sql, [
            ...$params,
            'now' => self::now(),
            'pending' => ReservationStatus::PENDING->value,
            'confirmed' => ReservationStatus::CONFIRMED->value,
        ]);
        return array_map(static fn (array $row) => Reservation::fromRow($row), $rows);
    }

    /** @return array<string, int> status => count within scope */
    public function countByStatus(AccessScope $scope): array
    {
        [$scopeSql, $params] = $scope->toSql();
        $rows = $this->fetchAllRows(
            'SELECT r.status, COUNT(*) AS total ' . self::FROM . " WHERE r.deleted_at IS NULL AND $scopeSql GROUP BY r.status",
            $params
        );
        return array_column($rows, 'total', 'status');
    }

    private function findOneWhere(string $condition, array $params): ?Reservation
    {
        $row = $this->fetchOne('SELECT ' . self::SELECT . ' ' . self::FROM . " WHERE $condition AND r.deleted_at IS NULL", $params);
        return $row ? Reservation::fromRow($row) : null;
    }
}
