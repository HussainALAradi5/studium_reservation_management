<?php
/**
 * @var string      $label
 * @var string      $value
 * @var string      $icon
 * @var string|null $tone
 * @var string|null $hint
 */
?>
<div class="card stat tone-<?= e($tone ?? 'primary') ?>">
    <div class="stat__icon"><?= icon($icon) ?></div>
    <div>
        <div class="stat__label"><?= e($label) ?></div>
        <div class="stat__value"><?= e($value) ?></div>
        <?php if (!empty($hint)): ?><div class="stat__hint"><?= e($hint) ?></div><?php endif; ?>
    </div>
</div>
