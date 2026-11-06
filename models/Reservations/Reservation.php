<?php

declare(strict_types=1);

namespace App\Models\Reservations;

use App\Enums\Reservations\BookingUnit;
use App\Enums\Reservations\PaymentStatus;
use App\Enums\Reservations\ReservationStatus;
use App\Models\Base\BaseEntity;

final class Reservation extends BaseEntity
{
    public int $stadiumId = 0;
    public int $customerId = 0;
    public string $startAt = '';
    public string $endAt = '';
    public BookingUnit $bookingUnit;
    /** Hours (may be fractional, e.g. 1.5) or days. */
    public float $unitCount = 0.0;
    public float $unitPrice = 0.0;
    public int $durationMinutes = 0;
    public float $totalAmount = 0.0;
    public float $paidAmount = 0.0;
    public ReservationStatus $status;
    public PaymentStatus $paymentStatus;
    public ?string $notes = null;
    public ?string $statusReason = null;

    // Joined columns used by lists, details, and access checks.
    public string $stadiumCode = '';
    public string $stadiumName = '';
    public ?int $stadiumManagerId = null;
    public string $customerCode = '';
    public string $customerName = '';
    public string $customerPhone = '';
    public string $cityName = '';

    public static function fromRow(array $row): static
    {
        $reservation = new self();
        $reservation->fillBase($row);
        $reservation->stadiumId = (int) $row['stadium_id'];
        $reservation->customerId = (int) $row['customer_id'];
        $reservation->startAt = $row['start_at'];
        $reservation->endAt = $row['end_at'];
        $reservation->durationMinutes = (int) $row['duration_minutes'];
        $reservation->bookingUnit = BookingUnit::from($row['booking_unit']);
        $reservation->unitCount = (float) $row['unit_count'];
        $reservation->unitPrice = (float) $row['unit_price'];
        $reservation->totalAmount = (float) $row['total_amount'];
        $reservation->paidAmount = (float) $row['paid_amount'];
        $reservation->status = ReservationStatus::from($row['status']);
        $reservation->paymentStatus = PaymentStatus::from($row['payment_status']);
        $reservation->notes = $row['notes'];
        $reservation->statusReason = $row['status_reason'];
        $reservation->stadiumCode = (string) ($row['stadium_code'] ?? '');
        $reservation->stadiumName = (string) ($row['stadium_name'] ?? '');
        $reservation->stadiumManagerId = self::nullableInt($row['stadium_manager_id'] ?? null);
        $reservation->customerCode = (string) ($row['customer_code'] ?? '');
        $reservation->customerName = (string) ($row['customer_name'] ?? '');
        $reservation->customerPhone = (string) ($row['customer_phone'] ?? '');
        $reservation->cityName = (string) ($row['city_name'] ?? '');
        return $reservation;
    }

    /** "2 hours" or "3 days". */
    public function sizeLabel(): string
    {
        return $this->bookingUnit->describe($this->unitCount);
    }

    public function balance(): float
    {
        return max(0.0, round($this->totalAmount - $this->paidAmount, 3));
    }

    /** Money is held on a reservation that will not be played. */
    public function isRefundDue(): bool
    {
        return $this->paidAmount > 0
            && in_array($this->status, [ReservationStatus::CANCELLED, ReservationStatus::REJECTED], true);
    }

    public function overpaidAmount(): float
    {
        return max(0.0, round($this->paidAmount - $this->totalAmount, 3));
    }

    public function hasEnded(): bool
    {
        return strtotime($this->endAt) <= time();
    }

    public function minutesUntilStart(): int
    {
        return intdiv(strtotime($this->startAt) - time(), 60);
    }
}
