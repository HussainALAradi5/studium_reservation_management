/**
 * Back buttons ([data-back]): return to the previous page of this site when there is one,
 * otherwise follow the link's own href (e.g. when the page was opened directly).
 */
import { on } from '../core/dom.js';

function cameFromThisSite() {
    try {
        return document.referrer !== '' && new URL(document.referrer).origin === window.location.origin;
    } catch {
        return false;
    }
}

export function initBackLinks(root = document) {
    on(root, 'click', '[data-back]', (event) => {
        if (cameFromThisSite() && window.history.length > 1) {
            event.preventDefault();
            window.history.back();
        }
    });
}
