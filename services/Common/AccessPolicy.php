<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Dtos\Common\AccessScope;
use App\Enums\Reservations\ReservationStatus;
use App\Models\Reservations\Reservation;
use App\Models\Stadiums\Stadium;
use App\Models\Users\User;

/** Permission expressions (docs: security/README.md). Pure functions, no database access. */
final class AccessPolicy
{
    public static function scopeFor(User $user): AccessScope
    {
        return match (true) {
            $user->isAdmin() => AccessScope::all(),
            $user->isManager() => AccessScope::manager($user->id),
            default => AccessScope::customer($user->id),
        };
    }

    /** isAdmin OR (isManager AND stadium.managerId == user.id) */
    public static function canManageStadium(User $user, ?int $stadiumManagerId): bool
    {
        return $user->isAdmin() || ($user->isManager() && $stadiumManagerId === $user->id);
    }

    public static function canManageReservation(User $user, Reservation $reservation): bool
    {
        return self::canManageStadium($user, $reservation->stadiumManagerId);
    }

    /** canManageStadium OR (isCustomer AND reservation.customerId == user.id) */
    public static function canViewReservation(User $user, Reservation $reservation): bool
    {
        return self::canManageReservation($user, $reservation)
            || ($user->isCustomer() && $reservation->customerId === $user->id);
    }

    public static function isReservationOwner(User $user, Reservation $reservation): bool
    {
        return $user->isCustomer() && $reservation->customerId === $user->id;
    }

    /**
     * canManage OR (owner AND (pending OR (confirmed AND hoursUntilStart >= cutoff)))
     * $cutoffHours comes from SettingsKey::CANCEL_CUTOFF_HOURS.
     */
    public static function canCancelReservation(User $user, Reservation $reservation, int $cutoffHours): bool
    {
        if (self::canManageReservation($user, $reservation)) {
            return true;
        }
        if (!self::isReservationOwner($user, $reservation)) {
            return false;
        }
        return $reservation->status === ReservationStatus::PENDING
            || ($reservation->status === ReservationStatus::CONFIRMED
                && $reservation->minutesUntilStart() >= $cutoffHours * 60);
    }

    /** canManage OR (owner AND pending) */
    public static function canRescheduleReservation(User $user, Reservation $reservation): bool
    {
        return self::canManageReservation($user, $reservation)
            || (self::isReservationOwner($user, $reservation) && $reservation->status === ReservationStatus::PENDING);
    }

    public static function canEditStadium(User $user, Stadium $stadium): bool
    {
        return self::canManageStadium($user, $stadium->managerId);
    }
}
