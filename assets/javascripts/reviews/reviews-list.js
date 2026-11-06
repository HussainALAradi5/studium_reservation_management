/**
 * "Show more reviews": loads the next page from the API and appends it to [data-reviews], rendering the
 * same markup as views/partials/reviews/item.php (including the moderator's Remove button).
 */
import { endpoints } from '../config/endpoints.js';
import { el, icon, qs } from '../core/dom.js';
import { http } from '../core/http.js';
import { withLoading } from '../core/button.js';
import { parseServerDateTime } from '../core/format.js';
import { toast } from '../core/toast.js';

const MAX_STARS = 5;

function stars(rating) {
    return el('span', { class: 'stars stars--compact', role: 'img', 'aria-label': `${rating} out of ${MAX_STARS} stars` },
        Array.from({ length: MAX_STARS }, (_, index) => icon('star', `icon stars__star${index < rating ? ' is-filled' : ''}`)));
}

function removeButton(review) {
    return el('button', {
        type: 'button',
        class: 'btn btn--ghost btn--sm btn--icon',
        'aria-label': 'Remove review',
        title: 'Remove review',
        dataset: {
            action: endpoints.reviews.remove(review.code),
            method: 'DELETE',
            confirm: 'Remove this review? It will no longer count toward the rating.',
            confirmTitle: 'Remove review',
            confirmTone: 'danger',
            confirmButton: 'Remove',
        },
    }, el('span', { class: 'btn__spinner', 'aria-hidden': 'true' }), el('span', { class: 'btn__content' }, icon('trash')));
}

function reviewItem(review, canModerate) {
    const initials = review.customerName.split(/\s+/).slice(0, 2).map((word) => word[0] ?? '').join('').toUpperCase();
    const date = parseServerDateTime(review.createdAt).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
    return el('article', { class: 'review anim-fade-up' },
        el('span', { class: 'avatar' }, initials),
        el('div', { class: 'review__body' },
            el('div', { class: 'row-between' },
                el('div', {}, el('strong', {}, review.customerName), el('div', { class: 'cell-sub' }, date)),
                el('div', { class: 'row', style: 'gap:6px' }, stars(review.rating), canModerate ? removeButton(review) : null)),
            el('p', { class: 'review__text' }, review.comment)));
}

export function initReviewsList(list) {
    const button = qs('[data-reviews-more]', list.parentElement);
    if (!button) return;
    const canModerate = list.dataset.canModerate === '1';
    button.addEventListener('click', () => withLoading(button, async () => {
        const next = Number(list.dataset.page) + 1;
        try {
            const { data, meta } = await http.get(endpoints.stadiums.reviews(list.dataset.stadiumCode, next), { withMeta: true });
            data.forEach((review) => list.append(reviewItem(review, canModerate)));
            list.dataset.page = String(next);
            if (next >= meta.totalPages) button.remove();
        } catch (error) {
            toast.error(error.message);
        }
    }));
}
