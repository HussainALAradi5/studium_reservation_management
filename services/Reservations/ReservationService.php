<?php

declare(strict_types=1);

namespace App\Services\Reservations;

use App\Config\Database\Database;
use App\Constants\Common\ErrorCodes;
use App\Constants\Reservations\ReservationRules;
use App\Dtos\Common\PageResult;
use App\Dtos\Reservations\BookingRequestDto;
use App\Dtos\Reservations\CreateReservationDto;
use App\Dtos\Reservations\ReservationQuery;
use App\Dtos\Reservations\StatusChangeDto;
use App\Dtos\Reservations\TimeSlotDto;
use App\Enums\Reservations\BookingUnit;
use App\Enums\Reservations\PaymentStatus;
use App\Enums\Reservations\ReservationStatus;
use App\Enums\Settings\SettingsKey;
use App\Exceptions\Common\ConflictException;
use App\Exceptions\Common\NotFoundException;
use App\Models\Reservations\Reservation;
use App\Models\Users\User;
use App\Repositories\Accounting\PaymentRepository;
use App\Repositories\Reservations\ReservationRepository;
use App\Repositories\Stadiums\StadiumRepository;
use App\Services\Common\AccessPolicy;
use App\Services\Settings\SettingsService;
use App\Services\Stadiums\StadiumService;

/** Rental lifecycle for hourly and daily bookings (docs: workflows/reservation-lifecycle.md). */
final class ReservationService
{
    private const FORMAT = ReservationRules::DATETIME_FORMAT;

    public function __construct(
        private readonly ReservationRepository $reservations = new ReservationRepository(),
        private readonly StadiumRepository $stadiums = new StadiumRepository(),
        private readonly PaymentRepository $payments = new PaymentRepository(),
        private readonly StadiumService $stadiumService = new StadiumService(),
        private readonly ConflictService $conflicts = new ConflictService(),
        private readonly SettingsService $settings = new SettingsService()
    ) {
    }

    public function list(User $user, ReservationQuery $query): PageResult
    {
        $this->expireStalePending();
        return $this->reservations->paginate($query, AccessPolicy::scopeFor($user));
    }

    /** @return Reservation[] */
    public function listUpcoming(User $user, int $limit): array
    {
        $this->expireStalePending();
        return $this->reservations->listUpcoming(AccessPolicy::scopeFor($user), $limit);
    }

    /** @return array<string, int> */
    public function countByStatus(User $user): array
    {
        return $this->reservations->countByStatus(AccessPolicy::scopeFor($user));
    }

    /** A reservation the user may see; 404 when out of scope (codes of others are not confirmed). */
    public function findViewable(User $user, string $code): Reservation
    {
        $reservation = $this->reservations->findByCode($code);
        if ($reservation === null || !AccessPolicy::canViewReservation($user, $reservation)) {
            throw new NotFoundException('Reservation not found.');
        }
        return $reservation;
    }

    /**
     * Customer rents a stadium by the hour or by the day: resolve and validate the range, lock the stadium,
     * check overlap, insert. Status is pending, or confirmed when auto-confirm is on.
     */
    public function create(User $customer, CreateReservationDto $dto): Reservation
    {
        $stadium = $this->stadiumService->findVisible(null, $dto->stadiumCode);
        $slot = $this->conflicts->resolve($stadium, $dto->request);
        $this->conflicts->validateSlot($stadium, $slot);
        $unitPrice = $this->conflicts->unitPriceFor($stadium, $slot->unit);
        $status = $this->settings->bool(SettingsKey::AUTO_CONFIRM_BOOKINGS) ? ReservationStatus::CONFIRMED : ReservationStatus::PENDING;

        $id = Database::transaction(function () use ($customer, $stadium, $slot, $unitPrice, $status, $dto) {
            $this->stadiums->lockById($stadium->id);
            $this->conflicts->assertNoConflict($stadium, $slot);
            return $this->reservations->insert([
                'stadium_id' => $stadium->id,
                'customer_id' => $customer->id,
                ...$this->slotColumns($slot),
                'unit_price' => $unitPrice,
                'total_amount' => $this->conflicts->priceFor($slot, $unitPrice),
                'paid_amount' => 0,
                'status' => $status->value,
                'payment_status' => PaymentStatus::UNPAID->value,
                'notes' => $dto->notes,
            ]);
        });
        return $this->reservations->findById($id);
    }

    /** Manager/admin accepts a pending booking; overlap is re-checked under the stadium lock. */
    public function confirm(User $actor, string $code): Reservation
    {
        $reservation = $this->findManageable($actor, $code);
        $this->assertTransition($reservation, ReservationStatus::CONFIRMED);
        $stadium = $this->stadiums->findById($reservation->stadiumId);
        $slot = $this->slotOf($reservation);

        Database::transaction(function () use ($reservation, $stadium, $slot) {
            $this->stadiums->lockById($stadium->id);
            $this->conflicts->assertNoConflict($stadium, $slot, $reservation->id);
            $this->reservations->update($reservation->id, ['status' => ReservationStatus::CONFIRMED->value, 'status_reason' => null]);
        });
        return $this->reservations->findById($reservation->id);
    }

    public function reject(User $actor, string $code, StatusChangeDto $dto): Reservation
    {
        return $this->changeStatus($this->findManageable($actor, $code), ReservationStatus::REJECTED, $dto->reason);
    }

    /** Owner (before the cutoff for confirmed bookings), the stadium's manager, or an admin. */
    public function cancel(User $actor, string $code, StatusChangeDto $dto): Reservation
    {
        $reservation = $this->findViewable($actor, $code);
        $this->assertTransition($reservation, ReservationStatus::CANCELLED);
        $cutoff = $this->cancelCutoffHours();
        if (!AccessPolicy::canCancelReservation($actor, $reservation, $cutoff)) {
            throw new ConflictException(
                "Confirmed bookings can be cancelled up to $cutoff hours before the start. Please contact the stadium.",
                ErrorCodes::CANCEL_CUTOFF_PASSED
            );
        }
        return $this->changeStatus($reservation, ReservationStatus::CANCELLED, $dto->reason);
    }

    public function complete(User $actor, string $code): Reservation
    {
        $reservation = $this->findManageable($actor, $code);
        $this->assertTransition($reservation, ReservationStatus::COMPLETED);
        if (!$reservation->hasEnded()) {
            throw new ConflictException('A booking can be completed only after it ends.', ErrorCodes::INVALID_TRANSITION);
        }
        return $this->changeStatus($reservation, ReservationStatus::COMPLETED, null);
    }

    /**
     * Moves a pending/confirmed booking (it may also switch between hourly and daily). The unit price
     * snapshot is kept when the unit stays the same; the total and payment status are recalculated.
     */
    public function reschedule(User $actor, string $code, BookingRequestDto $request): Reservation
    {
        $reservation = $this->findViewable($actor, $code);
        if (!$reservation->status->blocksSlot()) {
            throw new ConflictException('Only pending or confirmed bookings can be rescheduled.', ErrorCodes::INVALID_TRANSITION);
        }
        if (!AccessPolicy::canRescheduleReservation($actor, $reservation)) {
            throw new ConflictException('Confirmed bookings can be moved only by the stadium. Please contact them.', ErrorCodes::INVALID_TRANSITION);
        }
        $stadium = $this->stadiums->findById($reservation->stadiumId);
        $slot = $this->conflicts->resolve($stadium, $request);
        $this->conflicts->validateSlot($stadium, $slot);
        $unitPrice = $slot->unit === $reservation->bookingUnit
            ? $reservation->unitPrice
            : $this->conflicts->unitPriceFor($stadium, $slot->unit);

        Database::transaction(function () use ($reservation, $stadium, $slot, $unitPrice) {
            $this->stadiums->lockById($stadium->id);
            $this->conflicts->assertNoConflict($stadium, $slot, $reservation->id);
            $total = $this->conflicts->priceFor($slot, $unitPrice);
            $this->reservations->update($reservation->id, [
                ...$this->slotColumns($slot),
                'unit_price' => $unitPrice,
                'total_amount' => $total,
                'payment_status' => PaymentStatus::fromAmounts(
                    $reservation->paidAmount,
                    $total,
                    $this->payments->hasRefunds($reservation->id)
                )->value,
            ]);
        });
        return $this->reservations->findById($reservation->id);
    }

    /** Pending bookings whose start passed become cancelled ("expired"). Runs before lists load. */
    public function expireStalePending(): int
    {
        return $this->reservations->expireStalePending();
    }

    public function cancelCutoffHours(): int
    {
        return $this->settings->int(SettingsKey::CANCEL_CUTOFF_HOURS);
    }

    private function findManageable(User $actor, string $code): Reservation
    {
        $reservation = $this->findViewable($actor, $code);
        if (!AccessPolicy::canManageReservation($actor, $reservation)) {
            throw new NotFoundException('Reservation not found.');
        }
        return $reservation;
    }

    private function changeStatus(Reservation $reservation, ReservationStatus $next, ?string $reason): Reservation
    {
        $this->assertTransition($reservation, $next);
        $this->reservations->update($reservation->id, ['status' => $next->value, 'status_reason' => $reason]);
        return $this->reservations->findById($reservation->id);
    }

    private function assertTransition(Reservation $reservation, ReservationStatus $next): void
    {
        if (!$reservation->status->canTransitionTo($next)) {
            throw new ConflictException(
                "A {$reservation->status->label()} booking cannot be changed to {$next->label()}.",
                ErrorCodes::INVALID_TRANSITION
            );
        }
    }

    private function slotColumns(TimeSlotDto $slot): array
    {
        return [
            'start_at' => $slot->startAt->format(self::FORMAT),
            'end_at' => $slot->endAt->format(self::FORMAT),
            'booking_unit' => $slot->unit->value,
            'unit_count' => $slot->unitCount,
            'duration_minutes' => $slot->durationMinutes(),
        ];
    }

    private function slotOf(Reservation $reservation): TimeSlotDto
    {
        return new TimeSlotDto(
            new \DateTimeImmutable($reservation->startAt),
            new \DateTimeImmutable($reservation->endAt),
            $reservation->bookingUnit,
            $reservation->unitCount
        );
    }
}
