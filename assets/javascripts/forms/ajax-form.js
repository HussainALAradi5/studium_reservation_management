/**
 * Generic AJAX form (views/partials/forms/genericForm.php):
 *   <form data-ajax-form action="/api/..." data-method="PUT" data-on-success="redirect|reload|reset|close|none">
 * Sends JSON, shows the button spinner, renders per-field errors, handles booking conflicts with
 * suggested alternatives, then toasts and follows the server redirect.
 */
import { closeDialog } from '../core/dialog.js';
import { on, qsa } from '../core/dom.js';
import { http } from '../core/http.js';
import { navigate } from '../core/navigation.js';
import { setLoading } from '../core/button.js';
import { toast } from '../core/toast.js';
import { pickAlternative } from '../reservations/conflict-dialog.js';
import { fillForm, serializeForm } from './serialize.js';

const CONFLICT = 'RESERVATION_CONFLICT';

export function clearErrors(form) {
    qsa('.field.has-error', form).forEach((field) => field.classList.remove('has-error'));
    qsa('[data-error-for]', form).forEach((slot) => { slot.textContent = ''; });
}

function showErrors(form, fieldErrors) {
    let first = null;
    for (const [name, message] of Object.entries(fieldErrors ?? {})) {
        const slot = form.querySelector(`[data-error-for="${CSS.escape(name)}"]`);
        if (!slot) continue;
        slot.textContent = message;
        const field = slot.closest('.field');
        field?.classList.add('has-error');
        first ??= field;
    }
    first?.querySelector('input:not([type=hidden]), select, textarea, button')?.focus();
    return first !== null;
}

/** Applies the form's success behaviour; returns true when the page is about to navigate. */
function afterSuccess(form, data) {
    toast.success(data?.message ?? 'Saved.');
    const mode = form.dataset.onSuccess ?? 'redirect';
    if (mode === 'redirect' && data?.redirect) {
        navigate(data.redirect);
        return true;
    }
    if (mode === 'reload' || mode === 'redirect') {
        setTimeout(() => window.location.reload(), 350);
        return true;
    }
    if (mode === 'reset') form.reset();
    if (mode === 'close') closeDialog(form.closest('dialog'));
    return false;
}

async function submit(form, submitter) {
    clearErrors(form);
    const button = submitter ?? form.querySelector('[type=submit]');
    let isNavigating = false;
    setLoading(button, true);
    try {
        const data = await http.send(form.dataset.method ?? 'POST', form.action, serializeForm(form));
        isNavigating = afterSuccess(form, data);
    } catch (error) {
        if (error.code === CONFLICT) {
            setLoading(button, false);
            const choice = await pickAlternative(error);
            if (choice) {
                fillForm(form, choice);
                form.requestSubmit();
            }
            return;
        }
        const hasFieldErrors = showErrors(form, error.fieldErrors);
        if (!hasFieldErrors || error.status !== 422) toast.error(error.message);
    } finally {
        // Keep the spinner while the page navigates away.
        if (!isNavigating) setLoading(button, false);
    }
}

export function initAjaxForms(root = document) {
    on(root, 'submit', 'form[data-ajax-form]', (event, form) => {
        event.preventDefault();
        submit(form, event.submitter).catch(() => toast.error('Something went wrong.'));
    });
    // Clear a field's error as soon as the user edits it.
    on(root, 'input', 'form[data-ajax-form] .field.has-error', (event, field) => field.classList.remove('has-error'));
}

/** Errors from a failed request that did not navigate: always release the spinner. */
window.addEventListener('pageshow', () => qsa('.btn.is-loading').forEach((button) => setLoading(button, false)));
