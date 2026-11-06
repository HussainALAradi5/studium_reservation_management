<?php

declare(strict_types=1);

namespace App\Enums\Notifications;

use App\Constants\Common\RegexPatterns;
use App\Utilities\Formatting\IdentityNormalizer;

/**
 * Every in-app message the system sends. The template key is stored on the row; title and body are
 * rendered once at send time from named parameters (:stadium, :when, :customer, :amount, :reason).
 */
enum NotificationTemplate: string
{
    case RESERVATION_REQUESTED = 'reservation.requested';
    case RESERVATION_BOOKED = 'reservation.booked';
    case RESERVATION_CONFIRMED = 'reservation.confirmed';
    case RESERVATION_REJECTED = 'reservation.rejected';
    case RESERVATION_CANCELLED = 'reservation.cancelled';
    case RESERVATION_RESCHEDULED = 'reservation.rescheduled';
    case RESERVATION_COMPLETED = 'reservation.completed';
    case RESERVATION_EXPIRED = 'reservation.expired';
    case PAYMENT_RECORDED = 'payment.recorded';
    case PAYMENT_REFUNDED = 'payment.refunded';
    case REVIEW_CREATED = 'review.created';

    public function kind(): NotificationKind
    {
        return match ($this) {
            self::PAYMENT_RECORDED, self::PAYMENT_REFUNDED => NotificationKind::PAYMENT,
            self::REVIEW_CREATED => NotificationKind::REVIEW,
            self::RESERVATION_REJECTED, self::RESERVATION_CANCELLED, self::RESERVATION_EXPIRED => NotificationKind::ALERT,
            default => NotificationKind::BOOKING,
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::RESERVATION_REQUESTED => 'New booking request',
            self::RESERVATION_BOOKED => 'New confirmed booking',
            self::RESERVATION_CONFIRMED => 'Booking confirmed',
            self::RESERVATION_REJECTED => 'Booking rejected',
            self::RESERVATION_CANCELLED => 'Booking cancelled',
            self::RESERVATION_RESCHEDULED => 'Booking moved',
            self::RESERVATION_COMPLETED => 'Thanks for playing!',
            self::RESERVATION_EXPIRED => 'Booking request expired',
            self::PAYMENT_RECORDED => 'Payment received',
            self::PAYMENT_REFUNDED => 'Refund issued',
            self::REVIEW_CREATED => 'New review',
        };
    }

    public function bodyPattern(): string
    {
        return match ($this) {
            self::RESERVATION_REQUESTED => ':customer requested :stadium for :when.',
            self::RESERVATION_BOOKED => ':customer booked :stadium for :when.',
            self::RESERVATION_CONFIRMED => 'Your booking at :stadium for :when is confirmed.',
            self::RESERVATION_REJECTED => 'Your request for :stadium on :when was rejected. :reason',
            self::RESERVATION_CANCELLED => 'The booking at :stadium for :when was cancelled. :reason',
            self::RESERVATION_RESCHEDULED => 'The booking at :stadium now starts :when.',
            self::RESERVATION_COMPLETED => 'Your game at :stadium is complete. Rate the stadium to help other players.',
            self::RESERVATION_EXPIRED => 'Your request for :stadium on :when expired before it was confirmed.',
            self::PAYMENT_RECORDED => ':amount was received for your booking at :stadium.',
            self::PAYMENT_REFUNDED => ':amount was refunded for your booking at :stadium.',
            self::REVIEW_CREATED => ':customer rated :stadium :rating/5.',
        };
    }

    /**
     * Fills the pattern's own placeholders (missing ones become empty); values are inserted once,
     * so user text such as a reason is never treated as a placeholder.
     * @param array<string, string> $params
     */
    public function body(array $params): string
    {
        preg_match_all(RegexPatterns::TEMPLATE_PLACEHOLDER, $this->bodyPattern(), $matches);
        $replacements = [];
        foreach ($matches[1] as $name) {
            $replacements[':' . $name] = $params[$name] ?? '';
        }
        return IdentityNormalizer::text(strtr($this->bodyPattern(), $replacements));
    }
}
