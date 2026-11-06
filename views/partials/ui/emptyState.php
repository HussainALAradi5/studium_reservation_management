<?php
/**
 * @var string      $title
 * @var string|null $message
 * @var string|null $icon
 * @var string|null $action  pre-rendered HTML
 */
?>
<div class="empty anim-fade-in">
    <div class="empty__icon"><?= icon($icon ?? 'inbox', 'icon icon-lg') ?></div>
    <div class="empty__title"><?= e($title) ?></div>
    <?php if (!empty($message)): ?><p><?= e($message) ?></p><?php endif; ?>
    <?php if (!empty($action)): ?><div><?= $action ?></div><?php endif; ?>
</div>
