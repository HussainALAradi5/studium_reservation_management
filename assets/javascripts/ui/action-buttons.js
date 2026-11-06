/**
 * One-click API actions: confirm, reject, cancel, toggle, delete, sign out.
 *   <button data-action="/api/..." data-method="POST" data-confirm="Sure?" data-confirm-title="…"
 *           data-confirm-tone="danger" data-confirm-button="Yes" data-reason="required|optional"
 *           data-on-success="reload|none">
 * The button shows its spinner while the request runs; a conflict opens the suggestions dialog.
 */
import { confirmDialog } from '../core/dialog.js';
import { on } from '../core/dom.js';
import { http } from '../core/http.js';
import { setLoading } from '../core/button.js';
import { toast } from '../core/toast.js';

async function run(button) {
    const { action, method = 'POST', confirm, reason, onSuccess = 'reload' } = button.dataset;
    let body = {};
    if (confirm) {
        const answer = await confirmDialog({
            title: button.dataset.confirmTitle ?? 'Are you sure?',
            message: confirm,
            tone: button.dataset.confirmTone ?? 'primary',
            confirmText: button.dataset.confirmButton ?? 'Confirm',
            reason: reason ?? null,
        });
        if (!answer) return;
        if (answer.reason) body = { reason: answer.reason };
    }

    setLoading(button, true);
    try {
        const data = await http.send(method, action, method === 'DELETE' ? undefined : body);
        toast.success(data?.message ?? 'Done.');
        if (data?.redirect) {
            window.location.assign(data.redirect);
            return;
        }
        if (onSuccess === 'reload') {
            setTimeout(() => window.location.reload(), 350);
            return;
        }
        setLoading(button, false);
    } catch (error) {
        setLoading(button, false);
        if (error.code === 'RESERVATION_CONFLICT') {
            const { pickAlternative } = await import('../reservations/conflict-dialog.js');
            await pickAlternative(error);
            toast.warning('Reschedule the booking to one of the free times, or reject it.');
            return;
        }
        toast.error(error.message);
    }
}

export function initActionButtons(root = document) {
    on(root, 'click', '[data-action]', (event, button) => {
        event.preventDefault();
        if (!button.classList.contains('is-loading')) run(button);
    });
}
