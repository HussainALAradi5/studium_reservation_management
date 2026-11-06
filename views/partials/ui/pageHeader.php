<?php
/**
 * @var string      $title
 * @var string|null $subtitle
 * @var string|null $eyebrow
 * @var string|null $eyebrowIcon
 * @var string|null $actions   pre-rendered HTML (buttons)
 */
?>
<header class="page-header anim-fade-up">
    <div>
        <?php if (!empty($eyebrow)): ?>
            <div class="page-header__eyebrow"><?= !empty($eyebrowIcon) ? icon($eyebrowIcon) : '' ?><?= e($eyebrow) ?></div>
        <?php endif; ?>
        <h1 class="page-header__title"><?= e($title) ?></h1>
        <?php if (!empty($subtitle)): ?><p class="page-header__subtitle"><?= e($subtitle) ?></p><?php endif; ?>
    </div>
    <?php if (!empty($actions)): ?><div class="btn-group"><?= $actions ?></div><?php endif; ?>
</header>
