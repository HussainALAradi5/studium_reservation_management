/**
 * Promise-based dialogs built on native <dialog>:
 *   const result = await confirmDialog({ title, message, tone, reason: 'required' | 'optional' | null });
 *   result === false when dismissed, otherwise { reason }.
 */
import { el, icon } from './dom.js';

const TONE_ICONS = { danger: 'alert', success: 'check-circle', warning: 'alert', info: 'info', primary: 'info' };

export function openDialog(dialog) {
    if (!dialog.open) dialog.showModal();
    dialog.querySelector('[autofocus], input:not([type=hidden]), select, textarea, button.btn--primary')?.focus();
}

export function closeDialog(dialog) {
    if (dialog?.open) dialog.close();
}

/** Builds a temporary dialog, resolves with the choice, then removes itself. */
export function buildDialog({ title, tone = 'primary', content, actions, size = 'sm' }) {
    const dialog = el('dialog', { class: `dialog dialog--${size}` },
        el('div', { class: 'dialog__header' }, el('h2', { class: 'dialog__title' }, title)),
        el('div', { class: 'dialog__body' },
            el('div', { class: `dialog__icon tone-${tone}` }, icon(TONE_ICONS[tone] ?? 'info', 'icon icon-lg')),
            content,
            actions ? el('div', { class: 'form__actions', style: 'margin-top:18px' }, actions) : null,
        ),
    );
    document.body.append(dialog);
    dialog.addEventListener('close', () => setTimeout(() => dialog.remove(), 200), { once: true });
    return dialog;
}

export function confirmDialog({ title = 'Are you sure?', message = '', tone = 'primary', confirmText = 'Confirm', reason = null }) {
    return new Promise((resolve) => {
        const reasonInput = reason
            ? el('textarea', { class: 'textarea', maxlength: 255, placeholder: reason === 'required' ? 'Reason (required)' : 'Reason (optional)', style: 'min-height:80px;margin-top:12px' })
            : null;
        const error = el('div', { class: 'field__error', role: 'alert' });
        const confirmVariant = tone === 'danger' ? 'btn--danger' : (tone === 'success' ? 'btn--success' : 'btn--primary');
        const cancel = el('button', { type: 'button', class: 'btn btn--ghost' }, el('span', { class: 'btn__content' }, 'Back'));
        const confirm = el('button', { type: 'button', class: `btn ${confirmVariant}` }, el('span', { class: 'btn__content' }, icon('check'), confirmText));

        let result = false;
        const dialog = buildDialog({
            title,
            tone,
            content: el('div', {}, el('p', { class: 'muted', style: 'margin:0' }, message), reasonInput, error),
            actions: [cancel, confirm],
        });
        cancel.addEventListener('click', () => dialog.close());
        confirm.addEventListener('click', () => {
            const value = reasonInput?.value.trim() ?? '';
            if (reason === 'required' && value.length < 3) {
                error.textContent = 'Please give a short reason (at least 3 characters).';
                error.style.display = 'block';
                reasonInput.focus();
                return;
            }
            result = { reason: value };
            dialog.close();
        });
        dialog.addEventListener('close', () => resolve(result), { once: true });
        openDialog(dialog);
        (reasonInput ?? confirm).focus();
    });
}
