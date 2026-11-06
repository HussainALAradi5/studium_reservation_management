<?php
/**
 * @var \App\Models\Stadiums\Stadium[] $stadiums
 * @var \App\Models\Regional\City[] $cities
 * @var array<string, string> $sports
 */
$cityOptions = [];
foreach ($cities as $city) {
    $cityOptions[$city->code] = $city->name;
}
?>
<section class="hero">
    <div class="anim-fade-up">
        <span class="badge tone-primary badge--lg"><?= icon('sparkles') ?>Real-time availability</span>
        <h1 class="hero__title" style="margin-top:16px">Rent the perfect <span class="gradient-text">stadium</span> in seconds.</h1>
        <p class="hero__lead">Browse fields and courts across your city, see live free slots, and book by the hour or for full days — with zero double bookings.</p>
        <form class="hero__search glass" method="get" action="<?= e(url('/stadiums')) ?>">
            <?= partial('forms/genericField', ['field' => ['name' => 'city', 'type' => 'select', 'icon' => 'pin', 'options' => $cityOptions, 'emptyOption' => 'Any city']]) ?>
            <?= partial('forms/genericField', ['field' => ['name' => 'sport', 'type' => 'select', 'icon' => 'ball', 'options' => $sports, 'emptyOption' => 'Any sport']]) ?>
            <?= button('Search', ['type' => 'submit', 'icon' => 'search', 'size' => 'lg']) ?>
        </form>
    </div>
    <div class="hero__visual anim-scale-in">
        <div class="pitch"><span class="pitch__line"></span></div>
        <div class="hero__float hero__float--a glass"><?= icon('check-circle') ?> Booking confirmed</div>
        <div class="hero__float hero__float--b glass"><?= icon('clock') ?> 18:00 – 20:00 free</div>
    </div>
</section>

<section class="features" data-stagger>
    <div class="card feature"><div class="feature__icon"><?= icon('calendar') ?></div><h3>Hourly or full day</h3><p class="muted">Quick matches or whole-day tournaments, priced automatically.</p></div>
    <div class="card feature"><div class="feature__icon"><?= icon('shield') ?></div><h3>No double bookings</h3><p class="muted">Every booking is checked under a lock; if a slot is taken you get instant alternatives.</p></div>
    <div class="card feature"><div class="feature__icon"><?= icon('map') ?></div><h3>Find it on the map</h3><p class="muted">Exact locations with one-tap directions in Google Maps.</p></div>
</section>

<div class="row-between" style="margin:12px 0 16px">
    <h2 style="margin:0">Featured stadiums</h2>
    <?= button('View all', ['variant' => 'ghost', 'href' => url('/stadiums'), 'iconRight' => 'arrow-right']) ?>
</div>
<?php if ($stadiums === []): ?>
    <?= partial('ui/emptyState', ['title' => 'No stadiums yet', 'message' => 'Check back soon.', 'icon' => 'stadium']) ?>
<?php else: ?>
    <div class="grid grid-3" data-stagger>
        <?php foreach ($stadiums as $stadium): ?>
            <?= partial('ui/stadiumCard', ['stadium' => $stadium]) ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
