<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\Notifications\NotificationTemplate;
use App\Enums\Reservations\BookingUnit;
use App\Enums\Reservations\PaymentStatus;
use App\Enums\Reservations\ReservationStatus;
use App\Enums\Users\Permission;
use App\Enums\Users\UserRole;
use Tests\TestCase;

final class EnumRulesTest extends TestCase
{
    public function testReservationTransitions(): void
    {
        $this->assertTrue(ReservationStatus::PENDING->canTransitionTo(ReservationStatus::CONFIRMED));
        $this->assertTrue(ReservationStatus::CONFIRMED->canTransitionTo(ReservationStatus::COMPLETED));
        $this->assertFalse(ReservationStatus::PENDING->canTransitionTo(ReservationStatus::COMPLETED));
        $this->assertFalse(ReservationStatus::CANCELLED->canTransitionTo(ReservationStatus::CONFIRMED));
        $this->assertFalse(ReservationStatus::CONFIRMED->canTransitionTo(ReservationStatus::CONFIRMED));
        $this->assertSame(['pending', 'confirmed'], ReservationStatus::blockingValues());
    }

    public function testPaymentStatusFromAmounts(): void
    {
        $this->assertSame(PaymentStatus::UNPAID, PaymentStatus::fromAmounts(0, 50, false));
        $this->assertSame(PaymentStatus::PARTIAL, PaymentStatus::fromAmounts(20, 50, false));
        $this->assertSame(PaymentStatus::PAID, PaymentStatus::fromAmounts(50, 50, false));
        $this->assertSame(PaymentStatus::REFUNDED, PaymentStatus::fromAmounts(0, 50, true));
    }

    public function testBookingUnitDescribe(): void
    {
        $this->assertSame('1.5 hours', BookingUnit::HOUR->describe(1.5));
        $this->assertSame('1 hour', BookingUnit::HOUR->describe(1));
        $this->assertSame('3 days', BookingUnit::DAY->describe(3));
    }

    public function testDefaultPermissions(): void
    {
        $this->assertSame(Permission::cases(), Permission::defaultsFor(UserRole::ADMIN));
        $this->assertTrue(in_array(Permission::REVIEWS_WRITE, Permission::defaultsFor(UserRole::CUSTOMER), true));
        $this->assertFalse(in_array(Permission::USERS_MANAGE, Permission::defaultsFor(UserRole::MANAGER), true));
    }

    public function testNotificationBodyNeverTreatsUserTextAsPlaceholder(): void
    {
        $body = NotificationTemplate::RESERVATION_CANCELLED->body(['stadium' => 'Seef Arena', 'when' => 'Mon 18:00', 'reason' => 'Reason: note:late']);
        $this->assertSame('The booking at Seef Arena for Mon 18:00 was cancelled. Reason: note:late', $body);
        $this->assertSame('Your booking at A for B is confirmed.', NotificationTemplate::RESERVATION_CONFIRMED->body(['stadium' => 'A', 'when' => 'B']));
    }
}
