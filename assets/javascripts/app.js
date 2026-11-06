/**
 * Entry point (loaded as a module on every page). Shared behaviour starts immediately; page widgets
 * (maps, booking) are imported lazily only when their element is on the page.
 */
import { qsa, readJson } from './core/dom.js';
import { initRipple } from './core/button.js';
import { toast } from './core/toast.js';
import { initAjaxForms } from './forms/ajax-form.js';
import { initFieldHelpers } from './forms/field-helpers.js';
import { initWheelPickers } from './forms/wheel-picker.js';
import { initActionButtons } from './ui/action-buttons.js';
import { initAnimations } from './ui/animate.js';
import { initBackLinks } from './ui/back-link.js';
import { initDialogTriggers } from './ui/dialog-triggers.js';
import { initFilters } from './ui/filters.js';
import { initNotificationBell, initNotificationLinks } from './ui/notification-bell.js';
import { initSidebar } from './ui/sidebar.js';
import { initTheme } from './ui/theme.js';

const lazyWidgets = [
    ['[data-booking-widget]', () => import('./stadiums/booking-widget.js'), 'initBookingWidget'],
    ['[data-map-view]', () => import('./stadiums/map-view.js'), 'initMapView'],
    ['[data-map-picker]', () => import('./stadiums/map-picker.js'), 'initMapPicker'],
    ['[data-reviews]', () => import('./reviews/reviews-list.js'), 'initReviewsList'],
];

function showFlash() {
    const flash = readJson(document.body.dataset.flash);
    if (flash) toast.show(flash.type === 'success' ? 'success' : 'info', flash.message);
}

function initLazyWidgets() {
    for (const [selector, load, initName] of lazyWidgets) {
        const elements = qsa(selector);
        if (!elements.length) continue;
        load().then((module) => elements.forEach((element) => module[initName](element)));
    }
}

initTheme();
initSidebar();
initRipple();
initAjaxForms();
initFieldHelpers();
initWheelPickers();
initActionButtons();
initDialogTriggers();
initFilters();
initAnimations();
initBackLinks();
initNotificationBell();
initNotificationLinks();
initLazyWidgets();
showFlash();
