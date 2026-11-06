/** Date formatting shared by widgets (money and prices are formatted by the server). */

export function pad(value) {
    return String(value).padStart(2, '0');
}

/** Local YYYY-MM-DD (never toISOString, which shifts to UTC). */
export function isoDate(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

export function isoMonth(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}`;
}

export function parseIsoDate(value) {
    const [year, month, day] = String(value).split('-').map(Number);
    return new Date(year, (month || 1) - 1, day || 1);
}

/** Parses the server's "YYYY-MM-DD HH:MM:SS" as local time. */
export function parseServerDateTime(value) {
    return new Date(String(value).replace(' ', 'T'));
}

export function plural(count, word) {
    return `${count} ${word}${Number(count) === 1 ? '' : 's'}`;
}
