/**
 * Wheel picker for [data-wheel-picker] fields (views/partials/forms/dateField.php).
 * Modes: date (day / month / year), month (month / year), time (hour / minute), datetime (all five).
 * Styles: "ios" (3D drum) or "android" (flat with lines), chosen by device and switchable in the sheet;
 * the choice is remembered per browser. Keyboard: arrows move a column, Enter confirms, Escape cancels.
 */
import { el, on, qsa } from '../core/dom.js';
import { pad } from '../core/format.js';

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const ITEM_HEIGHT = 42;
const STYLE_KEY = 'studium-wheel-style';
const SETTLE_MS = 110;

function preferredStyle() {
    try {
        const saved = localStorage.getItem(STYLE_KEY);
        if (saved === 'ios' || saved === 'android') return saved;
    } catch { /* storage unavailable */ }
    return /android/i.test(navigator.userAgent) ? 'android' : 'ios';
}

function saveStyle(style) {
    try { localStorage.setItem(STYLE_KEY, style); } catch { /* storage unavailable */ }
}

const range = (from, to, step = 1) => {
    const list = [];
    for (let value = from; value <= to; value += step) list.push(value);
    return list;
};
const daysInMonth = (year, month) => new Date(year, month, 0).getDate();

/** Parses the hidden value into parts; falls back to now (rounded) for empty values. */
function parseValue(mode, value, minuteStep) {
    const now = new Date();
    const parts = {
        year: now.getFullYear(),
        month: now.getMonth() + 1,
        day: now.getDate(),
        hour: now.getHours(),
        minute: Math.ceil(now.getMinutes() / minuteStep) * minuteStep % 60,
    };
    const date = /^(\d{4})-(\d{2})(?:-(\d{2}))?/.exec(value);
    const time = /(\d{2}):(\d{2})$/.exec(value);
    if (date) {
        parts.year = Number(date[1]);
        parts.month = Number(date[2]);
        if (date[3]) parts.day = Number(date[3]);
    }
    if (time && mode !== 'date' && mode !== 'month') {
        parts.hour = Number(time[1]);
        parts.minute = Number(time[2]);
    }
    return parts;
}

function formatValue(mode, p) {
    const date = `${p.year}-${pad(p.month)}-${pad(p.day)}`;
    const time = `${pad(p.hour)}:${pad(p.minute)}`;
    return { date, time, month: `${p.year}-${pad(p.month)}`, datetime: `${date} ${time}` }[mode];
}

export function formatLabel(mode, value) {
    if (!value) return '';
    const p = parseValue(mode, value, 1);
    const weekday = new Date(p.year, p.month - 1, p.day).toLocaleDateString('en-US', { weekday: 'short' });
    const dateLabel = `${weekday}, ${p.day} ${MONTHS[p.month - 1]} ${p.year}`;
    const timeLabel = `${pad(p.hour)}:${pad(p.minute)}`;
    return { date: dateLabel, time: timeLabel, month: `${MONTHS[p.month - 1]} ${p.year}`, datetime: `${dateLabel} · ${timeLabel}` }[mode];
}

class WheelPicker {
    constructor(control) {
        this.control = control;
        this.mode = control.dataset.mode || 'date';
        this.input = control.querySelector('[data-picker-value]');
        this.trigger = control.querySelector('.picker-trigger');
        this.minuteStep = Number(control.dataset.minuteStep || 5);
        this.min = control.dataset.min || '';
        this.max = control.dataset.max || '';
        this.style = preferredStyle();
        this.refreshLabel();
        this.trigger.addEventListener('click', () => this.open());
        this.input.addEventListener('change', () => this.refreshLabel());
    }

    refreshLabel() {
        const label = formatLabel(this.mode, this.input.value);
        this.trigger.textContent = label || this.trigger.dataset.placeholder;
        this.trigger.classList.toggle('is-empty', !label);
    }

    yearRange() {
        const now = new Date().getFullYear();
        const minYear = this.min ? Number(this.min.slice(0, 4)) : now - 100;
        const maxYear = this.max ? Number(this.max.slice(0, 4)) : now + 5;
        return range(minYear, maxYear);
    }

    columnsFor(parts) {
        const columns = [];
        if (this.mode !== 'time') {
            if (this.mode !== 'month') {
                columns.push({ key: 'day', items: range(1, daysInMonth(parts.year, parts.month)).map((d) => ({ value: d, label: pad(d) })) });
            }
            columns.push({ key: 'month', items: range(1, 12).map((m) => ({ value: m, label: MONTHS[m - 1] })) });
            columns.push({ key: 'year', items: this.yearRange().map((y) => ({ value: y, label: String(y) })) });
        }
        if (this.mode === 'time' || this.mode === 'datetime') {
            columns.push({ key: 'hour', items: range(0, 23).map((h) => ({ value: h, label: pad(h) })) });
            columns.push({ key: 'minute', items: range(0, 59, this.minuteStep).map((m) => ({ value: m, label: pad(m) })) });
        }
        return columns;
    }

    open() {
        this.parts = parseValue(this.mode, this.input.value || this.min, this.minuteStep);
        this.parts.minute -= this.parts.minute % this.minuteStep;
        this.lastFocus = document.activeElement;
        this.trigger.setAttribute('aria-expanded', 'true');

        this.preview = el('div', { class: 'picker-sheet__preview', 'aria-live': 'polite' });
        this.wheels = el('div', { class: `wheels${this.style === 'android' ? ' wheels--android' : ''}` });
        const styleToggle = el('div', { class: 'segmented', role: 'group', 'aria-label': 'Picker style' },
            ['ios', 'android'].map((style) => el('button', {
                type: 'button',
                class: `segmented__option${this.style === style ? ' is-active' : ''}`,
                dataset: { style },
                onclick: (event) => this.switchStyle(style, event.currentTarget),
            }, style === 'ios' ? 'iPhone style' : 'Android style')),
        );

        this.backdrop = el('div', { class: 'picker-backdrop', onclick: () => this.close(false) });
        this.sheet = el('div', { class: 'picker-sheet', role: 'dialog', 'aria-modal': 'true', 'aria-label': this.control.dataset.title },
            el('div', { class: 'picker-sheet__grabber' }),
            el('div', { class: 'picker-sheet__header' },
                el('button', { type: 'button', class: 'btn btn--ghost btn--sm', onclick: () => this.close(false) }, el('span', { class: 'btn__content' }, 'Cancel')),
                el('div', { class: 'picker-sheet__title' }, this.control.dataset.title || 'Select'),
                el('button', { type: 'button', class: 'btn btn--primary btn--sm', onclick: () => this.close(true) }, el('span', { class: 'btn__content' }, 'Done')),
            ),
            this.preview,
            this.wheels,
            el('div', { class: 'picker-style' }, styleToggle),
        );
        this.sheet.addEventListener('keydown', (event) => this.onKey(event));
        document.body.append(this.backdrop, this.sheet);
        this.render();
    }

    render() {
        this.wheels.replaceChildren();
        this.columns = this.columnsFor(this.parts).map((column) => {
            const list = el('ul', { class: 'wheel', tabindex: '0', role: 'listbox', 'aria-label': column.key },
                column.items.map((item) => el('li', { class: 'wheel__item', role: 'option', dataset: { value: item.value } }, item.label)));
            this.wheels.append(list);
            const state = { ...column, list, frame: 0, timer: 0 };
            list.addEventListener('scroll', () => this.onScroll(state), { passive: true });
            list.addEventListener('click', (event) => {
                const item = event.target.closest('.wheel__item');
                if (item) this.scrollTo(state, column.items.findIndex((i) => String(i.value) === item.dataset.value), true);
            });
            return state;
        });
        requestAnimationFrame(() => {
            this.columns.forEach((state) => {
                const index = Math.max(0, state.items.findIndex((item) => item.value === this.parts[state.key]));
                this.scrollTo(state, index, false);
                this.paint(state);
            });
            this.columns[0]?.list.focus({ preventScroll: true });
            this.updatePreview();
        });
    }

    scrollTo(state, index, smooth) {
        const clamped = Math.min(Math.max(index, 0), state.items.length - 1);
        state.list.scrollTo({ top: clamped * ITEM_HEIGHT, behavior: smooth ? 'smooth' : 'auto' });
    }

    onScroll(state) {
        cancelAnimationFrame(state.frame);
        state.frame = requestAnimationFrame(() => this.paint(state));
        clearTimeout(state.timer);
        state.timer = setTimeout(() => this.settle(state), SETTLE_MS);
    }

    /** Highlights the centred item and tilts the others for the 3D drum look. */
    paint(state) {
        const centre = state.list.scrollTop / ITEM_HEIGHT;
        const isIos = this.style === 'ios';
        Array.from(state.list.children).forEach((item, index) => {
            const offset = index - centre;
            item.classList.toggle('is-selected', Math.round(centre) === index);
            item.setAttribute('aria-selected', Math.round(centre) === index ? 'true' : 'false');
            if (Math.abs(offset) > 4) return;
            item.style.transform = isIos ? `rotateX(${Math.max(-80, Math.min(80, offset * -20))}deg)` : '';
            item.style.opacity = isIos ? String(Math.max(0.25, 1 - Math.abs(offset) * 0.22)) : '';
        });
    }

    settle(state) {
        const index = Math.min(Math.max(Math.round(state.list.scrollTop / ITEM_HEIGHT), 0), state.items.length - 1);
        const value = state.items[index].value;
        if (this.parts[state.key] === value) return;
        this.parts[state.key] = value;
        if (state.key === 'month' || state.key === 'year') {
            const maxDay = daysInMonth(this.parts.year, this.parts.month);
            const dayColumn = this.columns.find((column) => column.key === 'day');
            if (dayColumn && dayColumn.items.length !== maxDay) {
                this.parts.day = Math.min(this.parts.day, maxDay);
                this.render();
                return;
            }
        }
        this.clampToRange();
        this.updatePreview();
    }

    /** Keeps the chosen date inside data-min / data-max by snapping the columns back. */
    clampToRange() {
        if (this.mode === 'time') return;
        const chosen = `${this.parts.year}-${pad(this.parts.month)}-${pad(this.parts.day)}`;
        const bound = (this.min && chosen < this.min) ? this.min : ((this.max && chosen > this.max) ? this.max : null);
        if (!bound) return;
        const [year, month, day] = bound.split('-').map(Number);
        Object.assign(this.parts, { year, month, day: day || 1 });
        this.render();
    }

    updatePreview() {
        this.preview.textContent = formatLabel(this.mode, formatValue(this.mode, this.parts));
    }

    switchStyle(style, button) {
        this.style = style;
        saveStyle(style);
        this.wheels.classList.toggle('wheels--android', style === 'android');
        qsa('.segmented__option', button.parentElement).forEach((option) => option.classList.toggle('is-active', option === button));
        this.columns.forEach((state) => this.paint(state));
    }

    onKey(event) {
        if (event.key === 'Escape') {
            event.preventDefault();
            this.close(false);
        } else if (event.key === 'Enter') {
            event.preventDefault();
            this.close(true);
        } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            const state = this.columns.find((column) => column.list === document.activeElement);
            if (!state) return;
            event.preventDefault();
            const index = Math.round(state.list.scrollTop / ITEM_HEIGHT) + (event.key === 'ArrowDown' ? 1 : -1);
            this.scrollTo(state, index, true);
        }
    }

    close(shouldApply) {
        if (shouldApply) {
            this.columns.forEach((state) => this.settle(state));
            this.input.value = formatValue(this.mode, this.parts);
            this.input.dispatchEvent(new Event('change', { bubbles: true }));
            this.control.closest('.field')?.classList.remove('has-error');
        }
        this.sheet.remove();
        this.backdrop.remove();
        this.trigger.setAttribute('aria-expanded', 'false');
        (this.lastFocus ?? this.trigger).focus?.();
    }
}

export function initWheelPickers(root = document) {
    qsa('[data-wheel-picker]', root).forEach((control) => {
        if (!control.wheelPicker) control.wheelPicker = new WheelPicker(control);
    });
}

/** Lets other modules set a picker value programmatically (keeps the label in sync). */
export function setPickerValue(input, value) {
    input.value = value;
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

on(document, 'keydown', '.picker-trigger', (event, trigger) => {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        trigger.click();
    }
});
