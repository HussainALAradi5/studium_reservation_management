<?php

declare(strict_types=1);

namespace App\Services\Reviews;

use App\Config\Database\Database;
use App\Constants\Common\ErrorCodes;
use App\Constants\Reviews\ReviewRules;
use App\Dtos\Common\PageRequest;
use App\Dtos\Common\PageResult;
use App\Dtos\Reviews\ReviewFormDto;
use App\Enums\Notifications\NotificationTemplate;
use App\Enums\Reviews\ReviewEligibility;
use App\Enums\Users\Permission;
use App\Exceptions\Common\AppException;
use App\Exceptions\Common\ConflictException;
use App\Exceptions\Common\NotFoundException;
use App\Models\Reviews\Review;
use App\Models\Stadiums\Stadium;
use App\Models\Users\User;
use App\Repositories\Reviews\ReviewRepository;
use App\Repositories\Stadiums\StadiumRepository;
use App\Services\Common\AccessPolicy;
use App\Services\Notifications\NotificationService;
use App\Services\Users\PermissionService;

/**
 * Stadium ratings and comments. Only signed-in users read them; only customers who played at the stadium
 * write one, and only once per stadium (no edits, even after a moderator removed it).
 */
final class ReviewService
{
    public function __construct(
        private readonly ReviewRepository $reviews = new ReviewRepository(),
        private readonly StadiumRepository $stadiums = new StadiumRepository(),
        private readonly PermissionService $permissions = new PermissionService(),
        private readonly NotificationService $notifications = new NotificationService()
    ) {
    }

    public function list(Stadium $stadium, int $page): PageResult
    {
        return $this->reviews->paginateForStadium($stadium->id, new PageRequest(max(1, $page), ReviewRules::PAGE_SIZE, 'created'));
    }

    /** Why the user can or cannot rate: drives whether the form is shown at all. */
    public function eligibility(User $user, Stadium $stadium): ReviewEligibility
    {
        if (!$this->permissions->can($user, Permission::REVIEWS_WRITE)) {
            return ReviewEligibility::NOT_A_CUSTOMER;
        }
        if ($this->reviews->existsForCustomer($stadium->id, $user->id)) {
            return ReviewEligibility::ALREADY_REVIEWED;
        }
        return $this->reviews->hasPlayedAt($stadium->id, $user->id) ? ReviewEligibility::CAN_REVIEW : ReviewEligibility::NOT_PLAYED;
    }

    /**
     * Creates the user's only review of the stadium. Re-checks eligibility on the server, so a hidden form
     * opened by hand still fails; the unique (stadium, customer) index is the last guard.
     */
    public function create(User $user, Stadium $stadium, ReviewFormDto $dto): Review
    {
        match ($this->eligibility($user, $stadium)) {
            ReviewEligibility::ALREADY_REVIEWED => throw new ConflictException('You have already rated this stadium.', ErrorCodes::REVIEW_ALREADY_EXISTS),
            ReviewEligibility::NOT_PLAYED => throw new AppException(ErrorCodes::REVIEW_NOT_ALLOWED, 'You can rate a stadium after you have played there.', 403),
            ReviewEligibility::NOT_A_CUSTOMER => throw new AppException(ErrorCodes::REVIEW_NOT_ALLOWED, 'Only customers can rate stadiums.', 403),
            ReviewEligibility::CAN_REVIEW => null,
        };

        try {
            $id = Database::transaction(function () use ($user, $stadium, $dto) {
                $id = $this->reviews->insert([
                    'stadium_id' => $stadium->id,
                    'customer_id' => $user->id,
                    'rating' => $dto->rating,
                    'comment' => $dto->comment,
                ]);
                $this->reviews->refreshStadiumRating($stadium->id);
                return $id;
            });
        } catch (\PDOException $error) {
            if ($error->getCode() === '23000') {
                throw new ConflictException('You have already rated this stadium.', ErrorCodes::REVIEW_ALREADY_EXISTS);
            }
            throw $error;
        }

        $this->notifications->notifyStadiumStaff($stadium->managerId, $stadium->code, NotificationTemplate::REVIEW_CREATED, [
            'customer' => $user->fullName,
            'stadium' => $stadium->name,
            'rating' => (string) $dto->rating,
        ]);
        return $this->reviews->findById($id);
    }

    /**
     * Moderation: staff with reviews.moderate who manage the stadium (admins: any) hide a review.
     * Authors cannot delete theirs, because a customer rates a stadium only once.
     */
    public function delete(User $user, string $code): void
    {
        $review = $this->reviews->findByCode($code) ?? throw new NotFoundException('Review not found.');
        $canModerate = $this->permissions->can($user, Permission::REVIEWS_MODERATE)
            && AccessPolicy::canManageStadium($user, $review->stadiumManagerId);
        if (!$canModerate) {
            throw new NotFoundException('Review not found.');
        }
        Database::transaction(function () use ($review) {
            $this->reviews->softDelete($review->id);
            $this->reviews->refreshStadiumRating($review->stadiumId);
        });
    }
}
