<?php
/**
 * Colored status badge.
 *
 * @var string      $text
 * @var string      $tone   success | warning | danger | info | neutral | primary (enums expose tone())
 * @var bool|null   $dot    leading dot (default true)
 * @var bool|null   $live   pulsing dot for "happening now / waiting" states
 * @var string|null $icon   icon instead of the dot
 * @var bool|null   $solid
 * @var bool|null   $large
 */
$classes = implode(' ', array_filter([
    'badge',
    'tone-' . $tone,
    !empty($live) ? 'badge--live' : null,
    !empty($solid) ? 'badge--solid' : null,
    !empty($large) ? 'badge--lg' : null,
]));
?>
<span class="<?= e($classes) ?>"><?php if (!empty($icon)): ?><?= icon($icon) ?><?php elseif ($dot ?? true): ?><span class="badge__dot" aria-hidden="true"></span><?php endif; ?><?= e($text) ?></span>
