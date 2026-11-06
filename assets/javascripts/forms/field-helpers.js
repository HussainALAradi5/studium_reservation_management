/**
 * Small field behaviours: show/hide password, and booking-type switches that show only the fields of
 * the chosen unit (elements with .unit-hour / .unit-day inside the same form).
 */
import { on, qsa } from '../core/dom.js';

function applyUnit(select) {
    const form = select.form;
    if (!form) return;
    const unit = select.value;
    qsa('.unit-hour, .unit-day', form).forEach((field) => {
        const isVisible = field.classList.contains(`unit-${unit}`);
        field.hidden = !isVisible;
        field.querySelectorAll('input, select, textarea').forEach((control) => { control.disabled = !isVisible; });
    });
}

export function initFieldHelpers(root = document) {
    on(root, 'click', '[data-toggle-password]', (event, button) => {
        const input = button.parentElement.querySelector('input');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        button.querySelector('use')?.setAttribute('href', isHidden ? '#i-x' : '#i-eye');
    });
    qsa('[data-unit-switch]', root).forEach(applyUnit);
    on(root, 'change', '[data-unit-switch]', (event, select) => applyUnit(select));
}
