<?php
/**
 * Topbar bell (assets/javascripts/ui/notification-bell.js): unread badge polled every minute (paused in
 * hidden tabs), dropdown with the latest notifications, open = mark read + go, "Mark all read".
 */
?>
<div class="bell" data-notification-bell>
    <button type="button" class="btn btn--glass btn--icon bell__button" aria-haspopup="true" aria-expanded="false" aria-label="Notifications" data-bell-toggle>
        <span class="btn__content"><?= icon('bell') ?></span>
        <span class="bell__badge" data-bell-badge hidden>0</span>
    </button>
    <div class="bell__panel glass" data-bell-panel hidden role="dialog" aria-label="Notifications">
        <div class="bell__header">
            <strong>Notifications</strong>
            <button type="button" class="btn btn--ghost btn--sm" data-bell-read-all><span class="btn__spinner" aria-hidden="true"></span><span class="btn__content"><?= icon('check') ?><span class="btn__label">Mark all read</span></span></button>
        </div>
        <div class="bell__list" data-bell-list aria-live="polite"></div>
        <a class="bell__footer" href="<?= e(url('/notifications')) ?>">View all notifications <?= icon('arrow-right') ?></a>
    </div>
</div>
