<?php
/**
 * customButton — the one button for the whole app.
 *
 * @var string      $text
 * @var string|null $variant     primary | secondary | outline | ghost | glass | danger | danger-soft | success
 * @var string|null $size        sm | md | lg
 * @var string|null $icon        icon before the text (views/partials/ui/icons.php)
 * @var string|null $iconRight   icon after the text
 * @var string|null $href        renders an <a> instead of a <button>
 * @var string|null $type        button | submit
 * @var string|null $loadingText text shown next to the spinner while processing (optional)
 * @var bool|null   $iconOnly    square icon button; $text becomes the accessible label
 * @var bool|null   $block       full width
 * @var bool|null   $pill        fully rounded
 * @var bool|null   $disabled
 * @var array|null  $attrs       extra attributes (data-*, aria-*, id, ...)
 *
 * While a request runs, assets/javascripts/core/button.js adds .is-loading + disabled, which hides the
 * label and shows the spinner; a ripple follows the pointer on press.
 */
$variant ??= 'primary';
$size ??= 'md';
$iconOnly ??= false;
$classes = trim(implode(' ', array_filter([
    'btn',
    "btn--$variant",
    $size !== 'md' ? "btn--$size" : null,
    $iconOnly ? 'btn--icon' : null,
    !empty($block) ? 'btn--block' : null,
    !empty($pill) ? 'btn--pill' : null,
    $attrs['class'] ?? null,
])));
$attributes = [...($attrs ?? []), 'class' => $classes, 'data-loading-text' => $loadingText ?? null];
if ($iconOnly) {
    $attributes['aria-label'] ??= $text;
    $attributes['title'] ??= $text;
}
$inner = '<span class="btn__spinner" aria-hidden="true"></span><span class="btn__content">'
    . (!empty($icon) ? icon($icon) : '')
    . ($iconOnly ? '' : '<span class="btn__label">' . e($text) . '</span>')
    . (!empty($iconRight) ? icon($iconRight) : '')
    . '</span>';
?>
<?php if (!empty($href)): ?>
<a href="<?= e($href) ?>"<?= attrs($attributes) ?><?= !empty($disabled) ? ' aria-disabled="true"' : '' ?>><?= $inner ?></a>
<?php else: ?>
<button type="<?= e($type ?? 'button') ?>"<?= attrs($attributes) ?><?= !empty($disabled) ? ' disabled' : '' ?>><?= $inner ?></button>
<?php endif; ?>
