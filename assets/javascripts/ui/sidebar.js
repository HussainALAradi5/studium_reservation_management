/** Mobile drawer for the sidebar. */
import { on, qs } from '../core/dom.js';

export function initSidebar() {
    const shell = qs('[data-shell]');
    if (!shell) return;
    const setOpen = (isOpen) => {
        shell.classList.toggle('is-nav-open', isOpen);
        qs('[data-nav-toggle]')?.setAttribute('aria-expanded', String(isOpen));
    };
    on(document, 'click', '[data-nav-toggle]', () => setOpen(!shell.classList.contains('is-nav-open')));
    on(document, 'click', '[data-nav-close]', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
}
