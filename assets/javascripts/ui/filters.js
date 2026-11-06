/**
 * genericFilter: submits on change (selects, wheel pickers) and after typing stops (search),
 * dropping empty values from the URL and resetting to page 1.
 */
import { debounce, on } from '../core/dom.js';

function apply(form) {
    const params = new URLSearchParams();
    for (const [name, value] of new FormData(form)) {
        if (String(value).trim() !== '') params.set(name, String(value).trim());
    }
    const query = params.toString();
    window.location.assign(form.action + (query ? `?${query}` : ''));
}

export function initFilters(root = document) {
    const applyLater = debounce(apply, 450);
    on(root, 'change', 'form[data-filter-form]', (event, form) => {
        if (!event.target.matches('[data-filter-search]')) apply(form);
    });
    on(root, 'input', 'form[data-filter-form] [data-filter-search]', (event, input) => applyLater(input.form));
    on(root, 'submit', 'form[data-filter-form]', (event, form) => {
        event.preventDefault();
        apply(form);
    });
}
