<?php

declare(strict_types=1);

namespace App\Models\Notifications;

use App\Enums\Notifications\NotificationKind;
use App\Enums\Notifications\NotificationTemplate;
use App\Models\Base\BaseEntity;

final class Notification extends BaseEntity
{
    public const RELATED_RESERVATION = 'reservation';
    public const RELATED_STADIUM = 'stadium';

    public int $userId = 0;
    public NotificationTemplate $template;
    public string $title = '';
    public string $body = '';
    public ?string $relatedType = null;
    public ?string $relatedCode = null;
    public ?string $readAt = null;

    public static function fromRow(array $row): static
    {
        $notification = new self();
        $notification->fillBase($row);
        $notification->userId = (int) $row['user_id'];
        $notification->template = NotificationTemplate::from($row['template']);
        $notification->title = $row['title'];
        $notification->body = $row['body'];
        $notification->relatedType = $row['related_type'];
        $notification->relatedCode = $row['related_code'];
        $notification->readAt = $row['read_at'];
        return $notification;
    }

    public function kind(): NotificationKind
    {
        return $this->template->kind();
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    /** App path the notification opens. */
    public function link(): ?string
    {
        return match ($this->relatedType) {
            self::RELATED_RESERVATION => '/reservations/' . $this->relatedCode,
            self::RELATED_STADIUM => '/stadiums/' . $this->relatedCode . '#reviews',
            default => null,
        };
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'kind' => $this->kind()->value,
            'icon' => $this->kind()->icon(),
            'tone' => $this->kind()->tone(),
            'title' => $this->title,
            'body' => $this->body,
            'link' => $this->link() !== null ? url($this->link()) : null,
            'isRead' => $this->isRead(),
            'createdAt' => $this->createdAt,
        ];
    }
}
