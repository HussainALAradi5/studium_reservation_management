<?php

declare(strict_types=1);

namespace App\Controllers\Notifications;

use App\Constants\Notifications\NotificationRules;
use App\Controllers\Base\Controller;
use App\Dtos\Notifications\NotificationQuery;
use App\Enums\Notifications\NotificationKind;
use App\Models\Notifications\Notification;
use App\Services\Notifications\NotificationService;
use App\Utilities\Http\Request;

/** The signed-in user's own notifications: inbox page, bell feed, unread count, mark read. */
final class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $this->render($request, 'notifications/index', [
            'title' => 'Notifications',
            'page' => (new NotificationService())->list($this->user(), NotificationQuery::fromQuery($request->queryAll(), NotificationRules::INBOX_PAGE_SIZE)),
            'filters' => $request->queryAll(),
            'kinds' => NotificationKind::options(),
        ]);
    }

    /** GET /api/notifications — latest items for the bell plus the unread count. */
    public function feed(Request $request): void
    {
        $service = new NotificationService();
        $page = $service->list($this->user(), NotificationQuery::fromQuery([], NotificationRules::BELL_SIZE));
        $this->json([
            'items' => array_map(static fn (Notification $item) => $item->toArray(), $page->items),
            'unreadCount' => $service->unreadCount($this->user()),
        ]);
    }

    public function unreadCount(Request $request): void
    {
        $this->json(['unreadCount' => (new NotificationService())->unreadCount($this->user())]);
    }

    public function markRead(Request $request): void
    {
        $service = new NotificationService();
        $service->markRead($this->user(), $request->param('code'));
        $this->json(['unreadCount' => $service->unreadCount($this->user())]);
    }

    public function markAllRead(Request $request): void
    {
        $count = (new NotificationService())->markAllRead($this->user());
        $this->json(['message' => $count > 0 ? 'All notifications marked as read.' : 'Nothing new.', 'unreadCount' => 0]);
    }
}
