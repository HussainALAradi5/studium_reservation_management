<?php

declare(strict_types=1);

namespace App\Repositories\Accounting;

use App\Constants\Common\CodePrefixes;
use App\Enums\Accounting\TransactionType;
use App\Models\Accounting\Payment;
use App\Repositories\Base\BaseRepository;

final class PaymentRepository extends BaseRepository
{
    protected function table(): string
    {
        return 'payments';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::PAYMENT;
    }

    /** @return Payment[] ledger of one reservation, oldest first */
    public function listForReservation(int $reservationId): array
    {
        $rows = $this->fetchAllRows(
            'SELECT p.*, u.full_name AS recorded_by_name FROM payments p
             LEFT JOIN users u ON u.id = p.created_by
             WHERE p.reservation_id = :id AND p.deleted_at IS NULL ORDER BY p.paid_at, p.id',
            ['id' => $reservationId]
        );
        return array_map(static fn (array $row) => Payment::fromRow($row), $rows);
    }

    public function hasRefunds(int $reservationId): bool
    {
        return $this->fetchOne(
            'SELECT 1 FROM payments WHERE reservation_id = :id AND type = :refund AND deleted_at IS NULL LIMIT 1',
            ['id' => $reservationId, 'refund' => TransactionType::REFUND->value]
        ) !== null;
    }
}
