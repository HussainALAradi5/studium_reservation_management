/**
 * Form → plain object for JSON bodies. Names ending in [] become arrays; for repeated plain names
 * (hidden "0" + switch "1") the last value wins.
 */
export function serializeForm(form) {
    const data = {};
    for (const [name, value] of new FormData(form)) {
        if (name.endsWith('[]')) {
            const key = name.slice(0, -2);
            (data[key] ??= []).push(value);
        } else {
            data[name] = value;
        }
    }
    return data;
}

/** Fills named controls (and wheel pickers) from an object. */
export function fillForm(form, values) {
    for (const [name, value] of Object.entries(values ?? {})) {
        for (const control of form.querySelectorAll(`[name="${CSS.escape(name)}"]`)) {
            if (control.type === 'checkbox') control.checked = Boolean(value) && value !== '0';
            else if (control.type === 'hidden' && control.closest('.switch')) continue;
            else control.value = value ?? '';
            control.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}
