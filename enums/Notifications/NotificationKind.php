<?php

declare(strict_types=1);

namespace App\Enums\Notifications;

use App\Enums\Common\HasOptions;

/** What a notification is about, so it looks different in the bell and inbox (icon + color tone). */
enum NotificationKind: string
{
    use HasOptions;

    case BOOKING = 'booking';
    case PAYMENT = 'payment';
    case REVIEW = 'review';
    case ALERT = 'alert';

    public function icon(): string
    {
        return match ($this) {
            self::BOOKING => 'calendar',
            self::PAYMENT => 'wallet',
            self::REVIEW => 'star',
            self::ALERT => 'alert',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::BOOKING => 'info',
            self::PAYMENT => 'success',
            self::REVIEW => 'warning',
            self::ALERT => 'danger',
        };
    }
}
