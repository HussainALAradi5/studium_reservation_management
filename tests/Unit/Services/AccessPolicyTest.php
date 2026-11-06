<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\Reservations\ReservationStatus;
use App\Enums\Users\UserRole;
use App\Models\Reservations\Reservation;
use App\Models\Users\User;
use App\Services\Common\AccessPolicy;
use Tests\TestCase;

final class AccessPolicyTest extends TestCase
{
    private const CUTOFF_HOURS = 2;

    private function user(int $id, UserRole $role): User
    {
        $user = new User();
        $user->id = $id;
        $user->role = $role;
        return $user;
    }

    private function reservation(int $customerId, ?int $managerId, ReservationStatus $status, string $startsIn): Reservation
    {
        $reservation = new Reservation();
        $reservation->customerId = $customerId;
        $reservation->stadiumManagerId = $managerId;
        $reservation->status = $status;
        $reservation->startAt = date('Y-m-d H:i:s', strtotime($startsIn));
        return $reservation;
    }

    public function testScopeByRole(): void
    {
        $this->assertSame(null, AccessPolicy::scopeFor($this->user(1, UserRole::ADMIN))->managerId);
        $this->assertSame(7, AccessPolicy::scopeFor($this->user(7, UserRole::MANAGER))->managerId);
        $this->assertSame(9, AccessPolicy::scopeFor($this->user(9, UserRole::CUSTOMER))->customerId);
    }

    public function testManagersOnlyManageTheirStadiums(): void
    {
        $manager = $this->user(7, UserRole::MANAGER);
        $this->assertTrue(AccessPolicy::canManageStadium($manager, 7));
        $this->assertFalse(AccessPolicy::canManageStadium($manager, 8));
        $this->assertTrue(AccessPolicy::canManageStadium($this->user(1, UserRole::ADMIN), 8));
    }

    public function testCustomersSeeOnlyTheirOwnBookings(): void
    {
        $booking = $this->reservation(9, 7, ReservationStatus::PENDING, '+1 day');
        $this->assertTrue(AccessPolicy::canViewReservation($this->user(9, UserRole::CUSTOMER), $booking));
        $this->assertFalse(AccessPolicy::canViewReservation($this->user(10, UserRole::CUSTOMER), $booking));
    }

    public function testCustomerCancelCutoff(): void
    {
        $owner = $this->user(9, UserRole::CUSTOMER);
        $this->assertTrue(AccessPolicy::canCancelReservation($owner, $this->reservation(9, 7, ReservationStatus::CONFIRMED, '+5 hours'), self::CUTOFF_HOURS));
        $this->assertFalse(AccessPolicy::canCancelReservation($owner, $this->reservation(9, 7, ReservationStatus::CONFIRMED, '+1 hour'), self::CUTOFF_HOURS));
        $this->assertTrue(AccessPolicy::canCancelReservation($owner, $this->reservation(9, 7, ReservationStatus::PENDING, '+1 hour'), self::CUTOFF_HOURS));
        $this->assertTrue(AccessPolicy::canCancelReservation($this->user(7, UserRole::MANAGER), $this->reservation(9, 7, ReservationStatus::CONFIRMED, '+1 hour'), self::CUTOFF_HOURS));
    }

    public function testOnlyPendingCanBeRescheduledByCustomer(): void
    {
        $owner = $this->user(9, UserRole::CUSTOMER);
        $this->assertTrue(AccessPolicy::canRescheduleReservation($owner, $this->reservation(9, 7, ReservationStatus::PENDING, '+1 day')));
        $this->assertFalse(AccessPolicy::canRescheduleReservation($owner, $this->reservation(9, 7, ReservationStatus::CONFIRMED, '+1 day')));
    }
}
