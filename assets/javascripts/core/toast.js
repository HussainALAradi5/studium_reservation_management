/** Glass toasts: toast.success('Saved'), toast.error('Failed'). */
import { el, icon, qs } from './dom.js';

const TONES = {
    success: { tone: 'success', icon: 'check-circle' },
    error: { tone: 'danger', icon: 'alert' },
    info: { tone: 'info', icon: 'info' },
    warning: { tone: 'warning', icon: 'alert' },
};
const DURATION = 4500;

function show(type, message) {
    const container = qs('[data-toasts]');
    if (!container || !message) return;
    const { tone, icon: iconName } = TONES[type] ?? TONES.info;
    const close = () => {
        item.classList.add('is-leaving');
        setTimeout(() => item.remove(), 260);
    };
    const item = el('div', { class: `toast tone-${tone}`, role: type === 'error' ? 'alert' : 'status' },
        el('span', { class: 'toast__icon' }, icon(iconName)),
        el('div', { class: 'toast__message' }, message),
        el('button', { class: 'toast__close', type: 'button', 'aria-label': 'Dismiss', onclick: close }, icon('x')),
    );
    container.append(item);
    setTimeout(close, DURATION);
}

export const toast = {
    success: (message) => show('success', message),
    error: (message) => show('error', message),
    info: (message) => show('info', message),
    warning: (message) => show('warning', message),
    show,
};
