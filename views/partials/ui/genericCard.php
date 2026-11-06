<?php
/**
 * Glass card.
 *
 * @var string|null $title
 * @var string|null $subtitle
 * @var string|null $icon
 * @var string|null $actions  pre-rendered HTML in the header
 * @var string      $body     pre-rendered HTML
 * @var string|null $footer   pre-rendered HTML
 * @var string|null $class
 * @var string|null $animate  data-animate value
 */
?>
<section class="card <?= e($class ?? '') ?>"<?= !empty($animate) ? ' data-animate="' . e($animate) . '"' : '' ?>>
    <?php if (!empty($title) || !empty($actions)): ?>
        <div class="card__header">
            <div>
                <?php if (!empty($title)): ?><h2 class="card__title"><?= !empty($icon) ? icon($icon) : '' ?><?= e($title) ?></h2><?php endif; ?>
                <?php if (!empty($subtitle)): ?><p class="card__subtitle"><?= e($subtitle) ?></p><?php endif; ?>
            </div>
            <?php if (!empty($actions)): ?><div class="btn-group"><?= $actions ?></div><?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="card__body"><?= $body ?></div>
    <?php if (!empty($footer)): ?><div class="card__footer"><?= $footer ?></div><?php endif; ?>
</section>
