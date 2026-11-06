<?php

declare(strict_types=1);

namespace App\Enums\Reservations;

use App\Enums\Common\HasOptions;

/** Lifecycle states (docs: workflows/reservation-lifecycle.md). */
enum ReservationStatus: string
{
    use HasOptions;

    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    /** Statuses that hold the time slot and take part in overlap checks. */
    public static function blockingValues(): array
    {
        return [self::PENDING->value, self::CONFIRMED->value];
    }

    public function blocksSlot(): bool
    {
        return in_array($this->value, self::blockingValues(), true);
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, match ($this) {
            self::PENDING => [self::CONFIRMED, self::REJECTED, self::CANCELLED],
            self::CONFIRMED => [self::CANCELLED, self::COMPLETED],
            default => [],
        }, true);
    }

    /** Badge colour token used by the views. */
    public function tone(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::COMPLETED => 'success',
            self::REJECTED, self::CANCELLED => 'danger',
        };
    }
}
