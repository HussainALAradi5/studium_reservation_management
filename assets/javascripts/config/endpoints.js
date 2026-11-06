/** API paths, mirroring config/Routes/routes.php. The base path comes from <meta name="app-base">. */
import { meta } from '../core/dom.js';

const base = meta('app-base');
const enc = encodeURIComponent;

export const url = (path) => `${base}${path}`;

export const endpoints = {
    auth: {
        login: () => url('/api/auth/login'),
        register: () => url('/api/auth/register'),
        logout: () => url('/api/auth/logout'),
    },
    stadiums: {
        availability: (code, date) => url(`/api/stadiums/${enc(code)}/availability?date=${enc(date)}`),
        calendar: (code, month) => url(`/api/stadiums/${enc(code)}/calendar?month=${enc(month)}`),
        quote: (code, params) => url(`/api/stadiums/${enc(code)}/quote?${new URLSearchParams(params)}`),
    },
    reservations: {
        create: () => url('/api/reservations'),
        show: (code) => url(`/reservations/${enc(code)}`),
    },
};
