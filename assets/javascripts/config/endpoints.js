/**
 * API paths used by the JavaScript modules, mirroring config/Routes/routes.php.
 * Forms and action buttons post to the URL the server rendered into their markup instead.
 * The base path comes from <meta name="app-base">.
 */
import { meta } from '../core/dom.js';

const base = meta('app-base');
const enc = encodeURIComponent;

export const url = (path) => `${base}${path}`;

export const endpoints = {
    stadiums: {
        availability: (code, date) => url(`/api/stadiums/${enc(code)}/availability?date=${enc(date)}`),
        calendar: (code, month) => url(`/api/stadiums/${enc(code)}/calendar?month=${enc(month)}`),
        quote: (code, params) => url(`/api/stadiums/${enc(code)}/quote?${new URLSearchParams(params)}`),
        reviews: (code, page) => url(`/api/stadiums/${enc(code)}/reviews?page=${enc(page)}`),
    },
    reservations: {
        create: () => url('/api/reservations'),
    },
    reviews: {
        remove: (code) => url(`/api/reviews/${enc(code)}`),
    },
    notifications: {
        feed: () => url('/api/notifications'),
        unreadCount: () => url('/api/notifications/unread-count'),
        markRead: (code) => url(`/api/notifications/${enc(code)}/read`),
        markAllRead: () => url('/api/notifications/read-all'),
    },
};
