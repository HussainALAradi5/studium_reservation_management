<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Config\Database\Database;
use App\Constants\Common\ErrorCodes;
use App\Dtos\Accounting\PaymentFormDto;
use App\Enums\Accounting\TransactionType;
use App\Enums\Notifications\NotificationTemplate;
use App\Enums\Reservations\PaymentStatus;
use App\Enums\Reservations\ReservationStatus;
use App\Exceptions\Common\AppException;
use App\Exceptions\Common\ConflictException;
use App\Exceptions\Common\NotFoundException;
use App\Models\Accounting\Payment;
use App\Models\Reservations\Reservation;
use App\Models\Users\User;
use App\Repositories\Accounting\PaymentRepository;
use App\Repositories\Reservations\ReservationRepository;
use App\Services\Common\AccessPolicy;
use App\Services\Notifications\NotificationService;
use App\Utilities\Formatting\Money;

/** Rental payments and refunds (docs: workflows/accounting.md). */
final class PaymentService
{
    public function __construct(
        private readonly PaymentRepository $payments = new PaymentRepository(),
        private readonly ReservationRepository $reservations = new ReservationRepository(),
        private readonly NotificationService $notifications = new NotificationService()
    ) {
    }

    /** @return Payment[] */
    public function listForReservation(Reservation $reservation): array
    {
        return $this->payments->listForReservation($reservation->id);
    }

    /**
     * Records a payment or refund: locks the reservation, checks the balance, inserts the ledger row,
     * and updates paid amount and payment status in one transaction.
     */
    public function recordTransaction(User $actor, string $reservationCode, PaymentFormDto $dto): Reservation
    {
        $reservation = $this->reservations->findByCode($reservationCode);
        if ($reservation === null || !AccessPolicy::canManageReservation($actor, $reservation)) {
            throw new NotFoundException('Reservation not found.');
        }

        Database::transaction(function () use ($reservation, $dto) {
            $this->reservations->lockById($reservation->id);
            $current = $this->reservations->findById($reservation->id);
            $this->assertAllowed($current, $dto);

            $this->payments->insert([
                'reservation_id' => $current->id,
                'type' => $dto->type->value,
                'method' => $dto->method->value,
                'amount' => $dto->amount,
                'paid_at' => $dto->paidAt->format('Y-m-d H:i:s'),
                'reference' => $dto->reference,
                'notes' => $dto->notes,
            ]);

            $isRefund = $dto->type === TransactionType::REFUND;
            $paid = Money::round($current->paidAmount + ($isRefund ? -$dto->amount : $dto->amount));
            $hasRefunds = $isRefund || $this->payments->hasRefunds($current->id);
            $this->reservations->update($current->id, [
                'paid_amount' => $paid,
                'payment_status' => PaymentStatus::fromAmounts($paid, $current->totalAmount, $hasRefunds)->value,
            ]);
        });
        $updated = $this->reservations->findById($reservation->id);
        $this->notifications->notifyCustomer(
            $updated,
            $dto->type === TransactionType::REFUND ? NotificationTemplate::PAYMENT_REFUNDED : NotificationTemplate::PAYMENT_RECORDED,
            ['amount' => Money::format($dto->amount)]
        );
        return $updated;
    }

    private function assertAllowed(Reservation $reservation, PaymentFormDto $dto): void
    {
        if ($dto->type === TransactionType::REFUND) {
            if ($dto->amount > $reservation->paidAmount) {
                throw $this->exceedsBalance('Refund cannot exceed the paid amount of ' . Money::format($reservation->paidAmount) . '.');
            }
            return;
        }
        if (in_array($reservation->status, [ReservationStatus::REJECTED, ReservationStatus::CANCELLED], true)) {
            throw new ConflictException('Payments cannot be added to a rejected or cancelled booking.', ErrorCodes::INVALID_TRANSITION);
        }
        if ($dto->amount > $reservation->balance()) {
            throw $this->exceedsBalance('Payment cannot exceed the outstanding balance of ' . Money::format($reservation->balance()) . '.');
        }
    }

    private function exceedsBalance(string $message): AppException
    {
        return new AppException(ErrorCodes::AMOUNT_EXCEEDS_BALANCE, $message, 422, ['fields' => ['amount' => $message]]);
    }
}
