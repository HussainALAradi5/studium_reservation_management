<?php
/** @var \App\Models\Stadiums\Stadium $stadium */
?>
<a class="card card--hover stadium-card sport-<?= e($stadium->sportType->value) ?>" href="<?= e(url('/stadiums/' . $stadium->code)) ?>">
    <div class="stadium-card__cover">
        <div class="pitch"><span class="pitch__line"></span></div>
        <span class="badge"><?= icon('ball') ?><?= e($stadium->sportType->label()) ?></span>
        <span class="stadium-card__price"><?= e(money($stadium->pricePerHour)) ?> / hr</span>
    </div>
    <div class="stadium-card__body">
        <h3 class="stadium-card__name"><?= e($stadium->name) ?></h3>
        <div class="stadium-card__meta">
            <span><?= icon('pin') ?><?= e($stadium->address->cityName) ?><?= $stadium->address->area ? ', ' . e($stadium->address->area) : '' ?></span>
            <span><?= icon('clock') ?><?= e($stadium->hoursLabel()) ?></span>
        </div>
        <div class="row" style="gap:6px">
            <?= badge($stadium->surfaceType->label(), 'neutral', ['dot' => false]) ?>
            <?= badge($stadium->capacity . ' players', 'info', ['icon' => 'users']) ?>
            <?php if ($stadium->allowsDailyBooking()): ?>
                <?= badge('Full-day rental', 'primary', ['icon' => 'calendar']) ?>
            <?php endif; ?>
            <?php if (!$stadium->isActive): ?>
                <?= badge('Inactive', 'danger') ?>
            <?php endif; ?>
        </div>
    </div>
</a>
