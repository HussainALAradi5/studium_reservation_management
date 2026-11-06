/**
 * customButton behaviour: a ripple that follows the pointer, and a loading state that disables
 * the button and shows the spinner (CSS in assets/css/components/buttons.css).
 */
import { on } from './dom.js';

export function setLoading(button, isLoading) {
    if (!button) return;
    const label = button.querySelector('.btn__label');
    if (isLoading) {
        button.dataset.wasDisabled = button.disabled ? '1' : '';
        if (label && button.dataset.loadingText) {
            button.dataset.originalText = label.textContent;
            label.textContent = button.dataset.loadingText;
        }
        button.classList.add('is-loading');
        button.setAttribute('aria-busy', 'true');
        button.disabled = true;
        return;
    }
    if (label && button.dataset.originalText) label.textContent = button.dataset.originalText;
    button.classList.remove('is-loading');
    button.removeAttribute('aria-busy');
    button.disabled = button.dataset.wasDisabled === '1';
}

/** Runs an async task with the button in its loading state. */
export async function withLoading(button, task) {
    setLoading(button, true);
    try {
        return await task();
    } finally {
        setLoading(button, false);
    }
}

export function initRipple(root = document) {
    on(root, 'pointerdown', '.btn', (event, button) => {
        if (button.disabled) return;
        const rect = button.getBoundingClientRect();
        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.left = `${event.clientX - rect.left}px`;
        ripple.style.top = `${event.clientY - rect.top}px`;
        button.append(ripple);
        ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
    });
}
