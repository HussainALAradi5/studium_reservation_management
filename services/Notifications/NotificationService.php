<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Constants\Notifications\NotificationRules;
use App\Dtos\Common\PageResult;
use App\Dtos\Notifications\NotificationQuery;
use App\Enums\Notifications\NotificationTemplate;
use App\Exceptions\Common\NotFoundException;
use App\Globals\Auth\CurrentUser;
use App\Models\Notifications\Notification;
use App\Models\Reservations\Reservation;
use App\Models\Users\User;
use App\Repositories\Notifications\NotificationRepository;
use App\Repositories\Users\UserRepository;
use App\Utilities\Logging\Logger;

/**
 * In-app notifications (bell + inbox). Business services call the notify* helpers after their
 * transaction commits. Delivery problems are logged and never undo the action that triggered them.
 */
final class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notifications = new NotificationRepository(),
        private readonly UserRepository $users = new UserRepository()
    ) {
    }

    public function list(User $user, NotificationQuery $query): PageResult
    {
        return $this->notifications->paginateForUser($user->id, $query);
    }

    public function unreadCount(User $user): int
    {
        return $this->notifications->countUnread($user->id);
    }

    public function markRead(User $user, string $code): void
    {
        if (!$this->notifications->markRead($user->id, $code)) {
            throw new NotFoundException('Notification not found.');
        }
    }

    public function markAllRead(User $user): int
    {
        return $this->notifications->markAllRead($user->id);
    }

    /**
     * Sends a reservation event to the customer. System events (expiry) pass $skipActor = false, because
     * the sweep may run while the same customer is the signed-in user.
     */
    public function notifyCustomer(Reservation $reservation, NotificationTemplate $template, array $params = [], bool $skipActor = true): void
    {
        $this->send([$reservation->customerId], $template, [...$this->reservationParams($reservation), ...$params], Notification::RELATED_RESERVATION, $reservation->code, $skipActor);
    }

    /** Sends a reservation event to the stadium's manager, or to all admins when it has none. */
    public function notifyStaff(Reservation $reservation, NotificationTemplate $template, array $params = []): void
    {
        $this->send($this->staffRecipients($reservation->stadiumManagerId), $template, [...$this->reservationParams($reservation), ...$params], Notification::RELATED_RESERVATION, $reservation->code);
    }

    /** Customer acted → tell the staff; staff acted → tell the customer. */
    public function notifyOtherParty(User $actor, Reservation $reservation, NotificationTemplate $template, array $params = []): void
    {
        $actor->id === $reservation->customerId
            ? $this->notifyStaff($reservation, $template, $params)
            : $this->notifyCustomer($reservation, $template, $params);
    }

    public function notifyStadiumStaff(?int $managerId, string $stadiumCode, NotificationTemplate $template, array $params): void
    {
        $this->send($this->staffRecipients($managerId), $template, $params, Notification::RELATED_STADIUM, $stadiumCode);
    }

    /** @param int[] $userIds */
    private function send(array $userIds, NotificationTemplate $template, array $params, string $relatedType, string $relatedCode, bool $skipActor = true): void
    {
        $actorId = $skipActor ? CurrentUser::id() : null;
        $recipients = array_values(array_unique(array_filter($userIds, static fn (int $id) => $id !== $actorId)));
        $body = $template->body($params);
        foreach ($recipients as $userId) {
            try {
                $this->notifications->insert([
                    'user_id' => $userId,
                    'template' => $template->value,
                    'title' => $template->title(),
                    'body' => mb_substr($body, 0, 500),
                    'related_type' => $relatedType,
                    'related_code' => $relatedCode,
                ]);
            } catch (\PDOException $error) {
                Logger::error("Notification {$template->value} to user $userId", $error);
            }
        }
    }

    /** @return int[] */
    private function staffRecipients(?int $managerId): array
    {
        return $managerId !== null ? [$managerId] : $this->users->listActiveAdminIds();
    }

    private function reservationParams(Reservation $reservation): array
    {
        return [
            'stadium' => $reservation->stadiumName,
            'customer' => $reservation->customerName,
            'when' => date(NotificationRules::DATETIME_LABEL, strtotime($reservation->startAt)) . ' (' . $reservation->sizeLabel() . ')',
            'reason' => $reservation->statusReason ? 'Reason: ' . $reservation->statusReason : '',
        ];
    }
}
