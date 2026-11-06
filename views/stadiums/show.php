<?php
/**
 * @var \App\Models\Stadiums\Stadium $stadium
 * @var array $map
 * @var bool $canBook
 * @var array<string, string> $bookingUnits
 * @var int $maxHours
 * @var int $maxDays
 * @var \App\Dtos\Common\PageResult|null $reviewPage
 * @var \App\Enums\Reviews\ReviewEligibility|null $eligibility
 * @var bool $canModerateReviews
 */
use App\Utilities\Formatting\AddressFormatter;

$address = $stadium->address;
$user = current_user();
$config = [
    'code' => $stadium->code,
    'slotMinutes' => $stadium->slotMinutes,
    'pricePerHour' => $stadium->pricePerHour,
    'pricePerDay' => $stadium->pricePerDay,
    'maxHours' => $maxHours,
    'maxDays' => $maxDays,
    'isSignedIn' => $user !== null,
    'loginUrl' => url('/login', ['next' => '/stadiums/' . $stadium->code]),
];
?>
<div class="stadium-hero sport-<?= e($stadium->sportType->value) ?> anim-fade-in">
    <div class="pitch"><span class="pitch__line"></span></div>
    <div class="stadium-hero__overlay">
        <div class="row" style="gap:6px;margin-bottom:8px">
            <?= badge($stadium->sportType->label(), 'success', ['solid' => true, 'icon' => 'ball']) ?>
            <?= badge($stadium->surfaceType->label(), 'neutral', ['solid' => true, 'dot' => false]) ?>
        </div>
        <h1><?= e($stadium->name) ?></h1>
        <a class="stadium-hero__rating" href="#reviews"><?= partial('ui/stars', ['rating' => $stadium->ratingAverage, 'count' => $stadium->ratingCount]) ?></a>
        <div class="row small" style="opacity:.9"><?= icon('pin') ?><?= e($address->formatted()) ?></div>
    </div>
</div>

<div class="split">
    <div class="stack">
        <?= partial('ui/genericCard', [
            'title' => 'About this stadium',
            'icon' => 'info',
            'animate' => 'fade-up',
            'body' => ($stadium->description ? '<p>' . nl2br(e($stadium->description)) . '</p>' : '')
                . '<dl class="details">'
                . '<div><dt>Hourly price</dt><dd>' . e(money($stadium->pricePerHour)) . '</dd></div>'
                . '<div><dt>Full day</dt><dd>' . ($stadium->allowsDailyBooking() ? e(money($stadium->pricePerDay)) : '<span class="muted">Hourly only</span>') . '</dd></div>'
                . '<div><dt>Opening hours</dt><dd>' . e($stadium->hoursLabel()) . '</dd></div>'
                . '<div><dt>Slot length</dt><dd>' . e($stadium->slotMinutes) . ' min</dd></div>'
                . '<div><dt>Capacity</dt><dd>' . e($stadium->capacity) . ' players</dd></div>'
                . '<div><dt>Managed by</dt><dd>' . e($stadium->managerName ?? '—') . '</dd></div>'
                . '</dl>',
        ]) ?>

        <?php ob_start(); ?>
        <?php if ($address->hasLocation()): ?>
            <div class="map" data-map-view
                 data-lat="<?= e($address->latitude) ?>" data-lng="<?= e($address->longitude) ?>"
                 data-tile-url="<?= e($map['tileUrl']) ?>" data-label="<?= e($stadium->name) ?>"></div>
            <div class="row" style="margin-top:14px">
                <?= button('Open in Google Maps', ['variant' => 'glass', 'icon' => 'map', 'href' => AddressFormatter::googleMapsUrl($address->latitude, $address->longitude), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
                <?= button('Directions', ['variant' => 'secondary', 'icon' => 'navigation', 'href' => AddressFormatter::googleDirectionsUrl($address->latitude, $address->longitude), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
            </div>
        <?php else: ?>
            <?= partial('ui/emptyState', ['title' => 'Location not pinned yet', 'message' => $address->formatted(), 'icon' => 'pin']) ?>
        <?php endif; ?>
        <?php $mapBody = ob_get_clean(); ?>
        <?= partial('ui/genericCard', ['title' => 'Location', 'icon' => 'pin', 'subtitle' => $address->formatted(), 'animate' => 'fade-up', 'body' => $mapBody]) ?>

        <?= partial('reviews/section', [
            'stadium' => $stadium,
            'reviewPage' => $reviewPage,
            'eligibility' => $eligibility,
            'canModerateReviews' => $canModerateReviews,
        ]) ?>
    </div>

    <section class="card booking" data-booking-widget data-config="<?= e(json_encode($config)) ?>" data-animate="slide-in-right">
        <div class="card__header">
            <h2 class="card__title"><?= icon('calendar') ?>Book this stadium</h2>
        </div>
        <div class="card__body stack<?= $user === null ? ' locked' : '' ?>">
            <?php if ($user === null): ?>
                <div class="locked__overlay" role="region" aria-label="Booking locked">
                    <div class="locked__icon"><?= icon('lock', 'icon icon-lg') ?></div>
                    <strong class="locked__title">You must register first</strong>
                    <p class="muted small" style="margin:0">Create a free account to see prices for your times and book this stadium.</p>
                    <div class="btn-group" style="justify-content:center">
                        <?= button('Register', ['icon' => 'sparkles', 'href' => url('/register', ['next' => '/stadiums/' . $stadium->code])]) ?>
                        <?= button('Sign in', ['variant' => 'secondary', 'icon' => 'login', 'href' => $config['loginUrl']]) ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!$canBook): ?>
                <div class="alert tone-info"><?= icon('info') ?><div>Bookings are made by customers. You can review this stadium's bookings from Reservations.</div></div>
            <?php else: ?>
                <?php if ($user === null): ?><div class="locked__preview" inert aria-hidden="true"><?php endif; ?>
                <?php if ($stadium->allowsDailyBooking()): ?>
                    <div class="segmented" role="tablist" aria-label="Booking type">
                        <?php foreach ($bookingUnits as $value => $label): ?>
                            <button type="button" class="segmented__option<?= $value === 'hour' ? ' is-active' : '' ?>" role="tab" data-unit="<?= e($value) ?>" aria-selected="<?= $value === 'hour' ? 'true' : 'false' ?>">
                                <?= icon($value === 'hour' ? 'clock' : 'calendar') ?><?= e($label) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div data-panel="hour" class="stack">
                    <div class="date-strip" data-date-strip aria-label="Choose a day"></div>
                    <div class="legend">
                        <span><i class="legend__dot" style="background:var(--glass-strong);border:1px solid var(--line)"></i>Free</span>
                        <span><i class="legend__dot" style="background:var(--primary)"></i>Selected</span>
                        <span><i class="legend__dot" style="background:var(--danger-soft)"></i>Booked</span>
                    </div>
                    <div class="slots" data-slots aria-live="polite"></div>
                    <p class="small muted" style="margin:0">Tap a start time, then tap a later free slot to extend (up to <?= e($maxHours) ?> hours).</p>
                </div>

                <div data-panel="day" class="stack" hidden>
                    <div class="calendar">
                        <div class="calendar__head">
                            <?= button('Previous month', ['variant' => 'ghost', 'size' => 'sm', 'iconOnly' => true, 'icon' => 'chevron-left', 'attrs' => ['data-month-prev' => true]]) ?>
                            <strong data-month-label></strong>
                            <?= button('Next month', ['variant' => 'ghost', 'size' => 'sm', 'iconOnly' => true, 'icon' => 'chevron-right', 'attrs' => ['data-month-next' => true]]) ?>
                        </div>
                        <div class="calendar__grid" data-calendar></div>
                    </div>
                    <div class="row-between">
                        <span class="field__label">Number of days</span>
                        <div class="stepper">
                            <?= button('Fewer days', ['variant' => 'ghost', 'size' => 'sm', 'iconOnly' => true, 'icon' => 'chevron-left', 'attrs' => ['data-days-minus' => true]]) ?>
                            <span class="stepper__value" data-days-value>1 day</span>
                            <?= button('More days', ['variant' => 'ghost', 'size' => 'sm', 'iconOnly' => true, 'icon' => 'chevron-right', 'attrs' => ['data-days-plus' => true]]) ?>
                        </div>
                    </div>
                    <div class="legend">
                        <span><i class="legend__dot" style="background:var(--success)"></i>Free</span>
                        <span><i class="legend__dot" style="background:var(--warning)"></i>Partly booked</span>
                        <span><i class="legend__dot" style="background:var(--danger)"></i>Full</span>
                    </div>
                </div>

                <div class="summary" data-summary hidden>
                    <div class="summary__row"><span class="muted">When</span><strong data-summary-when></strong></div>
                    <div class="summary__row"><span class="muted">Length</span><span data-summary-size></span></div>
                    <hr class="divider">
                    <div class="summary__row"><span>Total</span><span class="summary__total" data-summary-total></span></div>
                </div>
                <div class="field">
                    <label class="field__label" for="booking-notes">Notes for the stadium (optional)</label>
                    <textarea class="textarea" id="booking-notes" maxlength="500" data-notes placeholder="Team name, equipment needs…" style="min-height:70px"></textarea>
                </div>
                <?= button('Select a time first', ['size' => 'lg', 'block' => true, 'icon' => 'check', 'loadingText' => 'Booking…', 'disabled' => true, 'attrs' => ['data-book' => true]]) ?>
                <?php if ($user === null): ?></div><?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
