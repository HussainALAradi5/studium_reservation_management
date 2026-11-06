/**
 * Notification bell ([data-notification-bell]) and inbox links ([data-notification-open]).
 * - Unread count polled every minute; paused while the tab is hidden and refreshed when it returns.
 * - A toast appears when new notifications arrive.
 * - Opening the panel loads the latest items; opening an item marks it read, then follows its link.
 */
import { endpoints } from '../config/endpoints.js';
import { el, icon, on, qs } from '../core/dom.js';
import { parseServerDateTime } from '../core/format.js';
import { http } from '../core/http.js';
import { setLoading } from '../core/button.js';
import { toast } from '../core/toast.js';

const POLL_MS = 60000;
const MAX_BADGE = 99;

function timeAgo(value) {
    const seconds = Math.max(0, (Date.now() - parseServerDateTime(value).getTime()) / 1000);
    if (seconds < 60) return 'just now';
    const steps = [[60, 'min'], [24, 'h'], [7, 'd']];
    let amount = seconds / 60;
    for (const [limit, unit] of steps) {
        if (amount < limit) return `${Math.floor(amount)} ${unit} ago`;
        amount /= limit;
    }
    return parseServerDateTime(value).toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
}

/** Marks read without blocking navigation (keepalive lets the request finish while the page changes). */
function markReadInBackground(code) {
    return http.post(endpoints.notifications.markRead(code), {}, { keepalive: true }).catch(() => null);
}

export function initNotificationBell(root = document) {
    const bell = qs('[data-notification-bell]', root);
    if (!bell) return;
    const toggle = qs('[data-bell-toggle]', bell);
    const panel = qs('[data-bell-panel]', bell);
    const list = qs('[data-bell-list]', bell);
    const badge = qs('[data-bell-badge]', bell);
    const readAll = qs('[data-bell-read-all]', bell);
    let unread = null;
    let timer = 0;

    const setUnread = (count) => {
        if (unread !== null && count > unread) {
            const added = count - unread;
            toast.info(added === 1 ? 'You have a new notification.' : `You have ${added} new notifications.`);
            badge.classList.remove('is-bumped');
            void badge.offsetWidth;
            badge.classList.add('is-bumped');
        }
        unread = count;
        badge.hidden = count === 0;
        badge.textContent = count > MAX_BADGE ? `${MAX_BADGE}+` : String(count);
        toggle.setAttribute('aria-label', count ? `Notifications (${count} unread)` : 'Notifications');
    };

    const refreshCount = async () => {
        try {
            setUnread((await http.get(endpoints.notifications.unreadCount())).unreadCount);
        } catch {
            /* offline or signed out: keep the last count */
        }
    };

    const schedule = () => {
        clearTimeout(timer);
        if (!document.hidden) timer = setTimeout(async () => {
            await refreshCount();
            schedule();
        }, POLL_MS);
    };

    const renderItems = (items) => {
        if (!items.length) {
            list.replaceChildren(el('div', { class: 'bell__empty' }, icon('bell', 'icon icon-lg'), el('p', {}, 'You are all caught up.')));
            return;
        }
        list.replaceChildren(...items.map((item) => el('a', {
            class: `notice${item.isRead ? '' : ' is-unread'}`,
            href: item.link ?? '#',
            onclick: async (event) => {
                event.preventDefault();
                if (!item.isRead) await markReadInBackground(item.code);
                if (item.link) window.location.assign(item.link);
                else loadFeed();
            },
        },
        el('span', { class: `notice__icon tone-${item.tone}` }, icon(item.icon)),
        el('span', { class: 'notice__text' },
            el('strong', {}, item.title),
            el('span', { class: 'muted' }, item.body),
            el('span', { class: 'notice__time' }, timeAgo(item.createdAt))),
        item.isRead ? null : el('span', { class: 'notice__dot', 'aria-label': 'Unread' }))));
    };

    const loadFeed = async () => {
        list.replaceChildren(...Array.from({ length: 3 }, () => el('div', { class: 'skeleton', style: 'height:58px;margin:8px' })));
        try {
            const data = await http.get(endpoints.notifications.feed());
            setUnread(data.unreadCount);
            renderItems(data.items);
        } catch (error) {
            list.replaceChildren(el('p', { class: 'muted', style: 'padding:16px' }, error.message));
        }
    };

    const setOpen = (isOpen) => {
        panel.hidden = !isOpen;
        toggle.setAttribute('aria-expanded', String(isOpen));
        if (isOpen) loadFeed();
    };

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    document.addEventListener('click', (event) => {
        if (!panel.hidden && !bell.contains(event.target)) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            setOpen(false);
            toggle.focus();
        }
    });
    readAll.addEventListener('click', async () => {
        setLoading(readAll, true);
        try {
            await http.post(endpoints.notifications.markAllRead(), {});
            setUnread(0);
            await loadFeed();
        } catch (error) {
            toast.error(error.message);
        } finally {
            setLoading(readAll, false);
        }
    });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshCount();
        schedule();
    });

    refreshCount().then(schedule);
}

/** Inbox page rows (data-notification-open="<code>"): mark read, then follow the link. */
export function initNotificationLinks(root = document) {
    on(root, 'click', '[data-notification-open]', async (event, link) => {
        if (!link.classList.contains('is-unread')) return;
        event.preventDefault();
        await markReadInBackground(link.dataset.notificationOpen);
        window.location.assign(link.getAttribute('href') === '#' ? window.location.href : link.href);
    });
}
