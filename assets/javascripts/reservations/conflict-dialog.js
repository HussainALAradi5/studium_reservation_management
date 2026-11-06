/**
 * Shown when the server answers 409 RESERVATION_CONFLICT. Lists the free alternatives it suggested and
 * resolves with the picked one (or null).
 */
import { buildDialog, openDialog } from '../core/dialog.js';
import { el, icon } from '../core/dom.js';

export function pickAlternative(error) {
    const suggestions = error.details?.suggestions ?? [];
    return new Promise((resolve) => {
        let picked = null;
        const list = el('ul', { class: 'suggestions', 'data-stagger': true },
            suggestions.map((suggestion) => el('li', {},
                el('button', {
                    type: 'button',
                    class: 'suggestion',
                    onclick: () => {
                        picked = suggestion;
                        dialog.close();
                    },
                }, el('span', {}, suggestion.label, el('div', { class: 'cell-sub' }, suggestion.size)), icon('arrow-right')),
            )),
        );
        const content = el('div', {},
            el('p', { class: 'muted', style: 'margin:0' }, error.message),
            suggestions.length
                ? el('p', { style: 'margin:12px 0 0;font-weight:650' }, 'These times are free:')
                : el('p', { class: 'muted', style: 'margin:12px 0 0' }, 'No free alternatives in the next week. Try another day.'),
            list,
        );
        const close = el('button', { type: 'button', class: 'btn btn--ghost', onclick: () => dialog.close() }, el('span', { class: 'btn__content' }, 'Choose myself'));
        const dialog = buildDialog({ title: 'That time was just taken', tone: 'warning', content, actions: [close], size: 'sm' });
        dialog.addEventListener('close', () => resolve(picked), { once: true });
        openDialog(dialog);
    });
}
