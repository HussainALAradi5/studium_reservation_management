<?php
/**
 * Read-only star rating.
 *
 * @var float     $rating   0–5
 * @var int|null  $count    number of reviews (shown when given)
 * @var bool|null $compact  small inline version
 */
use App\Constants\Reviews\ReviewRules;

$filled = (int) round($rating);
?>
<span class="stars<?= !empty($compact) ? ' stars--compact' : '' ?>" role="img" aria-label="<?= e(number_format($rating, 1)) ?> out of <?= ReviewRules::MAX_RATING ?> stars">
    <?php for ($star = 1; $star <= ReviewRules::MAX_RATING; $star++): ?>
        <?= icon('star', 'icon stars__star' . ($star <= $filled ? ' is-filled' : '')) ?>
    <?php endfor; ?>
    <?php if (isset($count)): ?>
        <span class="stars__value"><?= $count > 0 ? e(number_format($rating, 1)) : '—' ?></span>
        <span class="stars__count">(<?= (int) $count ?>)</span>
    <?php endif; ?>
</span>
