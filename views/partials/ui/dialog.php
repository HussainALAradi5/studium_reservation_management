<?php
/**
 * Native <dialog> shell. Open with data-open-dialog="#id" (assets/javascripts/ui/dialog-triggers.js).
 *
 * @var string      $id
 * @var string      $title
 * @var string      $body     pre-rendered HTML
 * @var string|null $size     sm | lg
 * @var string|null $icon
 * @var string|null $tone
 */
?>
<dialog class="dialog<?= !empty($size) ? ' dialog--' . e($size) : '' ?>" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-title">
    <div class="dialog__header">
        <h2 class="dialog__title" id="<?= e($id) ?>-title" data-dialog-title><?= e($title) ?></h2>
        <?= button('Close', ['variant' => 'ghost', 'size' => 'sm', 'icon' => 'x', 'iconOnly' => true, 'attrs' => ['data-close-dialog' => true]]) ?>
    </div>
    <div class="dialog__body">
        <?php if (!empty($icon)): ?>
            <div class="dialog__icon tone-<?= e($tone ?? 'primary') ?>"><?= icon($icon, 'icon icon-lg') ?></div>
        <?php endif; ?>
        <?= $body ?>
    </div>
</dialog>
