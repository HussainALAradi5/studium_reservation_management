/**
 * Opens server-rendered dialogs (views/partials/ui/dialog.php):
 *   data-open-dialog="#id"  data-dialog-title="Edit city"  data-form-action="/api/cities/CODE"
 *   data-form-method="PUT"  data-fill='{"name": "Manama"}'
 * The same dialog serves create and edit by swapping the inner form's action, method, and values.
 */
import { closeDialog, openDialog } from '../core/dialog.js';
import { on, qs, readJson } from '../core/dom.js';
import { clearErrors } from '../forms/ajax-form.js';
import { fillForm } from '../forms/serialize.js';

export function initDialogTriggers(root = document) {
    on(root, 'click', '[data-open-dialog]', (event, trigger) => {
        const dialog = qs(trigger.dataset.openDialog);
        if (!dialog) return;
        event.preventDefault();
        const form = dialog.querySelector('form');
        if (trigger.dataset.dialogTitle) {
            const title = dialog.querySelector('[data-dialog-title]');
            if (title) title.textContent = trigger.dataset.dialogTitle;
        }
        if (form) {
            clearErrors(form);
            if (trigger.dataset.formAction) form.action = trigger.dataset.formAction;
            if (trigger.dataset.formMethod) form.dataset.method = trigger.dataset.formMethod;
            fillForm(form, readJson(trigger.dataset.fill, {}));
        }
        openDialog(dialog);
    });
    on(root, 'click', '[data-close-dialog]', (event, button) => closeDialog(button.closest('dialog')));
    // Click on the backdrop closes the dialog.
    on(root, 'click', 'dialog.dialog', (event, dialog) => {
        if (event.target === dialog) closeDialog(dialog);
    });
}
