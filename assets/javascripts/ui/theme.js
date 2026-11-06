/** Dark / light mode: follows the OS until the user picks one, then remembers it. */
import { on, qsa } from '../core/dom.js';

const KEY = 'studium-theme';
const root = document.documentElement;

function currentTheme() {
    const explicit = root.getAttribute('data-theme');
    if (explicit) return explicit;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function syncIcons() {
    const isDark = currentTheme() === 'dark';
    qsa('[data-theme-toggle]').forEach((button) => {
        button.querySelector('use')?.setAttribute('href', isDark ? '#i-sun' : '#i-moon');
        button.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        button.setAttribute('aria-pressed', String(isDark));
    });
}

export function initTheme() {
    syncIcons();
    on(document, 'click', '[data-theme-toggle]', () => {
        const next = currentTheme() === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        try { localStorage.setItem(KEY, next); } catch { /* storage unavailable */ }
        syncIcons();
    });
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', syncIcons);
}
