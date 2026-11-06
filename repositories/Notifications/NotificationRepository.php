<?php

declare(strict_types=1);

namespace App\Repositories\Notifications;

use App\Constants\Common\CodePrefixes;
use App\Dtos\Common\PageResult;
use App\Dtos\Notifications\NotificationQuery;
use App\Models\Notifications\Notification;
use App\Repositories\Base\BaseRepository;
use App\Utilities\Formatting\IdentityNormalizer;
use App\Utilities\Pagination\Paginator;

final class NotificationRepository extends BaseRepository
{
    private const SORT_COLUMNS = ['created' => 'n.created_at'];

    protected function table(): string
    {
        return 'notifications';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::NOTIFICATION;
    }

    public function paginateForUser(int $userId, NotificationQuery $query): PageResult
    {
        $where = 'WHERE n.user_id = :user_id AND n.deleted_at IS NULL';
        $params = ['user_id' => $userId];
        if ($query->isUnreadOnly) {
            $where .= ' AND n.read_at IS NULL';
        }
        if ($query->templates !== []) {
            $placeholders = [];
            foreach ($query->templates as $index => $template) {
                $placeholders[] = ":template$index";
                $params["template$index"] = $template;
            }
            $where .= ' AND n.template IN (' . implode(', ', $placeholders) . ')';
        }
        return Paginator::paginate(
            $this->pdo,
            'n.*',
            "FROM notifications n $where",
            $params,
            $query->page,
            self::SORT_COLUMNS,
            static fn (array $row) => Notification::fromRow($row)
        );
    }

    public function countUnread(int $userId): int
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND read_at IS NULL AND deleted_at IS NULL',
            ['user_id' => $userId]
        );
    }

    /** Marks one of the user's notifications read; returns false when it is not theirs or does not exist. */
    public function markRead(int $userId, string $code): bool
    {
        return $this->execute(
            'UPDATE notifications SET read_at = COALESCE(read_at, :now) WHERE user_id = :user_id AND code = :code AND deleted_at IS NULL',
            ['now' => self::now(), 'user_id' => $userId, 'code' => IdentityNormalizer::code($code)]
        )->rowCount() > 0 || $this->existsForUser($userId, $code);
    }

    public function markAllRead(int $userId): int
    {
        return $this->execute(
            'UPDATE notifications SET read_at = :now WHERE user_id = :user_id AND read_at IS NULL AND deleted_at IS NULL',
            ['now' => self::now(), 'user_id' => $userId]
        )->rowCount();
    }

    private function existsForUser(int $userId, string $code): bool
    {
        return $this->fetchOne(
            'SELECT 1 FROM notifications WHERE user_id = :user_id AND code = :code AND deleted_at IS NULL',
            ['user_id' => $userId, 'code' => IdentityNormalizer::code($code)]
        ) !== null;
    }
}
