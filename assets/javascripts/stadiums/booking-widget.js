/**
 * Booking widget on the stadium page ([data-booking-widget]).
 * Hourly: pick a day in the date strip, then a start slot and (optionally) a later slot to extend.
 * Full day: pick a start day on the month calendar and the number of days.
 * Every selection is priced by the server (quote endpoint); booking posts to the API and, on a
 * conflict, offers the free alternatives the server suggests.
 */
import { endpoints } from '../config/endpoints.js';
import { el, qs, qsa, readJson } from '../core/dom.js';
import { http, latestOnly } from '../core/http.js';
import { setLoading } from '../core/button.js';
import { toast } from '../core/toast.js';
import { isoDate, isoMonth, parseIsoDate, plural } from '../core/format.js';
import { pickAlternative } from '../reservations/conflict-dialog.js';

const STRIP_DAYS = 21;
const WEEKDAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

export function initBookingWidget(root) {
    const config = readJson(root.dataset.config, {});
    const ui = {
        strip: qs('[data-date-strip]', root),
        slots: qs('[data-slots]', root),
        calendar: qs('[data-calendar]', root),
        monthLabel: qs('[data-month-label]', root),
        daysValue: qs('[data-days-value]', root),
        summary: qs('[data-summary]', root),
        when: qs('[data-summary-when]', root),
        size: qs('[data-summary-size]', root),
        total: qs('[data-summary-total]', root),
        notes: qs('[data-notes]', root),
        book: qs('[data-book]', root),
    };
    if (!ui.book) return;
    const bookLabel = ui.book.querySelector('.btn__label');

    const state = {
        unit: 'hour',
        date: isoDate(new Date()),
        slots: [],
        start: null,
        end: null,
        month: new Date(),
        dayStart: null,
        days: 1,
        quote: null,
    };

    const fetchSlots = latestOnly((date, signal) => http.get(endpoints.stadiums.availability(config.code, date), { signal }));
    const fetchCalendar = latestOnly((month, signal) => http.get(endpoints.stadiums.calendar(config.code, month), { signal }));
    const fetchQuote = latestOnly((params, signal) => http.get(endpoints.stadiums.quote(config.code, params), { signal }));

    // ---------- request payload ----------
    function selection() {
        if (state.unit === 'day') {
            return state.dayStart ? { bookingUnit: 'day', date: state.dayStart, days: state.days } : null;
        }
        if (state.start === null) return null;
        const first = state.slots[state.start];
        return {
            bookingUnit: 'hour',
            date: first.startAt.slice(0, 10),
            startTime: first.start,
            durationMinutes: (state.end - state.start + 1) * config.slotMinutes,
        };
    }

    // ---------- summary + button ----------
    function setButton(text, isEnabled) {
        bookLabel.textContent = text;
        ui.book.disabled = !isEnabled;
    }

    async function refreshQuote() {
        const params = selection();
        state.quote = null;
        if (!params) {
            ui.summary.hidden = true;
            setButton('Select a time first', false);
            return;
        }
        ui.summary.hidden = false;
        ui.total.replaceChildren(el('span', { class: 'skeleton', style: 'display:inline-block;width:90px' }, ' '));
        setButton('Checking…', false);
        try {
            const quote = await fetchQuote(params);
            state.quote = quote;
            ui.when.textContent = quote.label;
            ui.size.textContent = quote.size;
            ui.total.textContent = quote.totalLabel;
            if (quote.isAvailable) setButton(config.isSignedIn ? `Book for ${quote.totalLabel}` : 'Sign in to book', true);
            else setButton('Not available — see alternatives', true);
        } catch (error) {
            if (error.name === 'AbortError') return;
            ui.total.textContent = '—';
            ui.when.textContent = error.message;
            ui.size.textContent = '';
            setButton('Choose another time', false);
        }
    }

    // ---------- hourly ----------
    function renderStrip() {
        const today = new Date();
        ui.strip.replaceChildren(...Array.from({ length: STRIP_DAYS }, (_, offset) => {
            const day = new Date(today.getFullYear(), today.getMonth(), today.getDate() + offset);
            const iso = isoDate(day);
            return el('button', {
                type: 'button',
                class: `date-chip${iso === state.date ? ' is-active' : ''}`,
                dataset: { date: iso },
                'aria-pressed': String(iso === state.date),
                onclick: () => selectDate(iso),
            },
            el('span', { class: 'date-chip__dow' }, offset === 0 ? 'Today' : day.toLocaleDateString('en-US', { weekday: 'short' })),
            el('span', { class: 'date-chip__day' }, day.getDate()),
            el('span', { class: 'date-chip__dow' }, day.toLocaleDateString('en-US', { month: 'short' })));
        }));
    }

    async function selectDate(iso, keep = null) {
        state.date = iso;
        state.start = state.end = null;
        qsa('.date-chip', ui.strip).forEach((chip) => {
            const isActive = chip.dataset.date === iso;
            chip.classList.toggle('is-active', isActive);
            chip.setAttribute('aria-pressed', String(isActive));
            if (isActive) chip.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
        });
        ui.slots.replaceChildren(...Array.from({ length: 8 }, () => el('div', { class: 'skeleton', style: 'height:40px' })));
        refreshQuote();
        try {
            const data = await fetchSlots(iso);
            state.slots = data.slots;
            if (keep) applyHourSelection(keep.startTime, keep.durationMinutes);
            renderSlots();
            refreshQuote();
        } catch (error) {
            if (error.name !== 'AbortError') ui.slots.replaceChildren(el('p', { class: 'muted' }, error.message));
        }
    }

    function renderSlots() {
        if (!state.slots.length) {
            ui.slots.replaceChildren(el('p', { class: 'muted' }, 'Closed on this day.'));
            return;
        }
        ui.slots.replaceChildren(...state.slots.map((slot, index) => {
            const isSelected = state.start !== null && index >= state.start && index <= state.end;
            return el('button', {
                type: 'button',
                class: `slot is-${slot.state}${isSelected ? ' is-selected' : ''}`,
                disabled: slot.state !== 'free',
                title: slot.state === 'booked' ? 'Already booked' : `${slot.start} – ${slot.end}`,
                'aria-pressed': String(isSelected),
                onclick: () => toggleSlot(index),
            }, slot.start);
        }));
    }

    function isRangeFree(from, to) {
        return state.slots.slice(from, to + 1).every((slot) => slot.state === 'free');
    }

    function toggleSlot(index) {
        const maxSlots = Math.floor((config.maxHours * 60) / config.slotMinutes);
        if (state.start === index && state.end === index) {
            state.start = state.end = null;
        } else if (state.start !== null && index > state.end && index - state.start + 1 <= maxSlots && isRangeFree(state.start, index)) {
            state.end = index;
        } else if (state.start !== null && index > state.end && index - state.start + 1 > maxSlots) {
            toast.info(`Hourly bookings can last up to ${plural(config.maxHours, 'hour')}. Switch to full day for longer events.`);
            return;
        } else {
            state.start = state.end = index;
        }
        renderSlots();
        refreshQuote();
    }

    function applyHourSelection(startTime, durationMinutes) {
        const index = state.slots.findIndex((slot) => slot.start === startTime);
        if (index < 0) return;
        const count = Math.max(1, Math.round(durationMinutes / config.slotMinutes));
        state.start = index;
        state.end = Math.min(index + count - 1, state.slots.length - 1);
    }

    // ---------- full day ----------
    async function renderCalendar() {
        const month = isoMonth(state.month);
        ui.monthLabel.textContent = state.month.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        ui.calendar.replaceChildren(...WEEKDAYS.map((day) => el('div', { class: 'calendar__dow' }, day)),
            ...Array.from({ length: 35 }, () => el('div', { class: 'skeleton', style: 'aspect-ratio:1' })));
        try {
            const data = await fetchCalendar(month);
            const blanks = data.days.length ? data.days[0].weekday - 1 : 0;
            ui.calendar.replaceChildren(
                ...WEEKDAYS.map((day) => el('div', { class: 'calendar__dow' }, day)),
                ...Array.from({ length: blanks }, () => el('div')),
                ...data.days.map((day) => el('button', {
                    type: 'button',
                    class: 'calendar__day',
                    dataset: { state: day.state, date: day.date },
                    disabled: day.state === 'past' || day.state === 'full',
                    title: { free: 'Free all day', partial: 'Partly booked', full: 'Fully booked', past: '' }[day.state],
                    onclick: () => selectDayStart(day.date),
                }, day.day)),
            );
            paintRange();
        } catch (error) {
            if (error.name !== 'AbortError') ui.calendar.replaceChildren(el('p', { class: 'muted' }, error.message));
        }
    }

    function paintRange() {
        const start = state.dayStart ? parseIsoDate(state.dayStart) : null;
        qsa('.calendar__day', ui.calendar).forEach((button) => {
            const day = parseIsoDate(button.dataset.date);
            const offset = start ? Math.round((day - start) / 86400000) : -1;
            button.classList.toggle('is-selected', offset === 0);
            button.classList.toggle('is-in-range', offset > 0 && offset < state.days);
        });
        ui.daysValue.textContent = plural(state.days, 'day');
    }

    function selectDayStart(iso) {
        state.dayStart = state.dayStart === iso ? null : iso;
        paintRange();
        refreshQuote();
    }

    function changeDays(delta) {
        state.days = Math.min(config.maxDays, Math.max(1, state.days + delta));
        paintRange();
        refreshQuote();
    }

    // ---------- unit switch ----------
    function switchUnit(unit) {
        state.unit = unit;
        qsa('[data-unit]', root).forEach((tab) => {
            const isActive = tab.dataset.unit === unit;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
        });
        qsa('[data-panel]', root).forEach((panel) => { panel.hidden = panel.dataset.panel !== unit; });
        if (unit === 'day' && !ui.calendar.children.length) renderCalendar();
        refreshQuote();
    }

    // ---------- apply a server suggestion ----------
    function applySuggestion(choice) {
        if (choice.bookingUnit === 'day') {
            if (state.unit !== 'day') switchUnit('day');
            state.dayStart = choice.date;
            state.days = choice.days;
            state.month = parseIsoDate(choice.date);
            renderCalendar();
            refreshQuote();
            return;
        }
        if (state.unit !== 'hour') switchUnit('hour');
        selectDate(choice.date, choice);
    }

    // ---------- booking ----------
    async function book() {
        if (!config.isSignedIn) {
            window.location.assign(config.loginUrl);
            return;
        }
        if (state.quote && !state.quote.isAvailable) {
            const choice = await pickAlternative({ message: 'Part of this time is already booked.', details: { suggestions: state.quote.suggestions } });
            if (choice) applySuggestion(choice);
            return;
        }
        const params = selection();
        if (!params) return;
        setLoading(ui.book, true);
        try {
            const data = await http.post(endpoints.reservations.create(), {
                stadiumCode: config.code,
                ...params,
                notes: ui.notes?.value.trim() || '',
            });
            toast.success(data.message);
            navigate(data.redirect);
        } catch (error) {
            setLoading(ui.book, false);
            if (error.code === 'RESERVATION_CONFLICT') {
                const choice = await pickAlternative(error);
                if (choice) applySuggestion(choice);
                else state.unit === 'hour' ? selectDate(state.date) : renderCalendar();
                return;
            }
            toast.error(Object.values(error.fieldErrors)[0] ?? error.message);
        }
    }

    // ---------- wire up ----------
    qsa('[data-unit]', root).forEach((tab) => tab.addEventListener('click', () => switchUnit(tab.dataset.unit)));
    qs('[data-month-prev]', root)?.addEventListener('click', () => {
        const now = new Date();
        const previous = new Date(state.month.getFullYear(), state.month.getMonth() - 1, 1);
        if (previous >= new Date(now.getFullYear(), now.getMonth(), 1)) {
            state.month = previous;
            renderCalendar();
        }
    });
    qs('[data-month-next]', root)?.addEventListener('click', () => {
        state.month = new Date(state.month.getFullYear(), state.month.getMonth() + 1, 1);
        renderCalendar();
    });
    qs('[data-days-minus]', root)?.addEventListener('click', () => changeDays(-1));
    qs('[data-days-plus]', root)?.addEventListener('click', () => changeDays(1));
    ui.book.addEventListener('click', book);

    renderStrip();
    selectDate(state.date);
}
