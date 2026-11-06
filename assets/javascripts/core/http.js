/**
 * The one HTTP client: JSON in/out, CSRF header, same-origin cookies, abortable requests,
 * and a single error type matching the server response shape {success, error{code, message, details}}.
 */
import { meta } from './dom.js';

export class ApiError extends Error {
    constructor(status, code, message, details = {}) {
        super(message);
        this.status = status;
        this.code = code;
        this.details = details;
    }

    get fieldErrors() {
        return this.details?.fields ?? {};
    }
}

const NETWORK_ERROR = 'Network error. Check your connection and try again.';

export async function request(url, { method = 'GET', body, signal } = {}) {
    const headers = { Accept: 'application/json', 'X-Requested-With': 'fetch' };
    if (method !== 'GET') {
        headers['Content-Type'] = 'application/json';
        headers['X-CSRF-Token'] = meta('csrf-token');
    }

    let response;
    try {
        response = await fetch(url, {
            method,
            headers,
            body: body === undefined ? undefined : JSON.stringify(body),
            credentials: 'same-origin',
            signal,
        });
    } catch (error) {
        if (error.name === 'AbortError') throw error;
        throw new ApiError(0, 'NETWORK_ERROR', NETWORK_ERROR);
    }

    const payload = await response.json().catch(() => null);
    if (!response.ok || !payload?.success) {
        const error = payload?.error ?? {};
        throw new ApiError(response.status, error.code ?? 'SERVER_ERROR', error.message ?? 'Something went wrong.', error.details ?? {});
    }
    return payload.data;
}

export const http = {
    get: (url, options) => request(url, { ...options, method: 'GET' }),
    post: (url, body, options) => request(url, { ...options, method: 'POST', body }),
    put: (url, body, options) => request(url, { ...options, method: 'PUT', body }),
    delete: (url, options) => request(url, { ...options, method: 'DELETE' }),
    send: (method, url, body, options) => request(url, { ...options, method, body }),
};

/** Wraps a fetch-like function so each new call aborts the previous one (stale responses are ignored). */
export function latestOnly(fn) {
    let controller;
    return (...args) => {
        controller?.abort();
        controller = new AbortController();
        return fn(...args, controller.signal);
    };
}
