<?php
/**
 * @var int    $status
 * @var string $message
 */
$titles = [403 => 'Access denied', 404 => 'Not found', 429 => 'Slow down', 500 => 'Something went wrong'];
?>
<div class="card anim-scale-in" style="max-width:560px;margin:40px auto">
    <div class="card__body">
        <?= partial('ui/emptyState', [
            'icon' => $status === 404 ? 'search' : ($status === 403 ? 'shield' : 'alert'),
            'title' => $status . ' · ' . ($titles[$status] ?? 'Error'),
            'message' => $message,
            'action' => button('Go home', ['href' => url(current_user() ? '/dashboard' : '/'), 'icon' => 'home']),
        ]) ?>
    </div>
</div>
