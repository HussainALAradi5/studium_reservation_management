/** Display formatting shared by widgets. */
import { meta } from './dom.js';

const currency = meta('currency') || 'BHD';

export function money(amount) {
    return `${Number(amount ?? 0).toLocaleString('en-US', { minimumFractionDigits: 3, maximumFractionDigits: 3 })} ${currency}`;
}

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

export function plural(count, word) {
    return `${count} ${word}${Number(count) === 1 ? '' : 's'}`;
}

export function durationLabel(minutes) {
    const hours = minutes / 60;
    return Number.isInteger(hours) ? plural(hours, 'hour') : `${hours.toFixed(1)} hours`;
}
