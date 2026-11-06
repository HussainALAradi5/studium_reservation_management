<?php

declare(strict_types=1);

namespace App\Enums\Reviews;

/** Outcome of ReviewService::eligibility(); the view shows the form only for CAN_REVIEW. */
enum ReviewEligibility: string
{
    case CAN_REVIEW = 'can_review';
    case ALREADY_REVIEWED = 'already_reviewed';
    case NOT_PLAYED = 'not_played';
    case NOT_A_CUSTOMER = 'not_a_customer';

    public function message(): string
    {
        return match ($this) {
            self::CAN_REVIEW => 'Share how your game went.',
            self::ALREADY_REVIEWED => 'You have already rated this stadium. Thank you!',
            self::NOT_PLAYED => 'You can rate this stadium after you have played here.',
            self::NOT_A_CUSTOMER => 'Only customers who rented this stadium can rate it.',
        };
    }
}
