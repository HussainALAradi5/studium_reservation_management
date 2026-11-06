<?php

declare(strict_types=1);

namespace App\Dtos\Notifications;

use App\Dtos\Common\PageRequest;
use App\Enums\Notifications\NotificationKind;
use App\Enums\Notifications\NotificationTemplate;
use App\Validations\Common\QueryFilters;

/** Inbox filters: unread only and kind (booking / payment / review / alert). */
final class NotificationQuery
{
    /** @param string[] $templates template values of the chosen kind (empty = all) */
    public function __construct(
        public readonly PageRequest $page,
        public readonly bool $isUnreadOnly = false,
        public readonly ?NotificationKind $kind = null,
        public readonly array $templates = []
    ) {
    }

    public static function fromQuery(array $query, ?int $pageSize = null): self
    {
        if ($pageSize !== null) {
            $query['pageSize'] = $pageSize;
        }
        $kind = QueryFilters::enum($query, 'kind', NotificationKind::class);
        $templates = $kind === null ? [] : array_values(array_map(
            static fn (NotificationTemplate $template) => $template->value,
            array_filter(NotificationTemplate::cases(), static fn (NotificationTemplate $template) => $template->kind() === $kind)
        ));
        return new self(
            PageRequest::fromQuery($query, ['created'], 'created'),
            ($query['status'] ?? '') === 'unread',
            $kind,
            $templates
        );
    }
}
