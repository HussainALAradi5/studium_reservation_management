<?php
/**
 * @var \App\Dtos\Common\PageResult $page
 * @var array $filters
 * @var array<string, string> $kinds
 * @var string $currentPath
 */
?>
<?= partial('ui/pageHeader', [
    'title' => 'Notifications',
    'subtitle' => 'Updates about your bookings, payments, and reviews.',
    'actions' => button('Mark all read', ['variant' => 'secondary', 'icon' => 'check', 'loadingText' => 'Marking…', 'attrs' => [
        'data-action' => url('/api/notifications/read-all'),
        'data-method' => 'POST',
    ]]),
]) ?>
<div class="stack">
    <?= partial('filters/genericFilter', [
        'path' => $currentPath,
        'values' => $filters,
        'fields' => [
            ['name' => 'status', 'label' => 'Show', 'type' => 'select', 'options' => ['unread' => 'Unread only'], 'emptyOption' => 'All'],
            ['name' => 'kind', 'label' => 'Type', 'type' => 'select', 'options' => $kinds, 'emptyOption' => 'All types'],
        ],
    ]) ?>
    <div class="card" data-animate="fade-up">
        <div class="card__body">
            <?php if ($page->isEmpty()): ?>
                <?= partial('ui/emptyState', ['title' => 'You are all caught up', 'message' => 'New notifications will appear here.', 'icon' => 'bell']) ?>
            <?php else: ?>
                <ul class="inbox" data-stagger>
                    <?php foreach ($page->items as $item): ?>
                        <li>
                            <a class="notice<?= $item->isRead() ? '' : ' is-unread' ?>"
                               href="<?= e($item->link() !== null ? url($item->link()) : '#') ?>"
                               data-notification-open="<?= e($item->code) ?>">
                                <span class="notice__icon tone-<?= e($item->kind()->tone()) ?>"><?= icon($item->kind()->icon()) ?></span>
                                <span class="notice__text">
                                    <strong><?= e($item->title) ?></strong>
                                    <span class="muted"><?= e($item->body) ?></span>
                                    <span class="notice__time"><?= e(format_date($item->createdAt, 'D j M, H:i')) ?></span>
                                </span>
                                <?php if (!$item->isRead()): ?><span class="notice__dot" aria-label="Unread"></span><?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?= partial('ui/pagination', ['page' => $page, 'path' => $currentPath, 'filters' => $filters]) ?>
        </div>
    </div>
</div>
