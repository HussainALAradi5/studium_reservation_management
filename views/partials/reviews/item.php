<?php
/**
 * @var \App\Models\Reviews\Review $review
 * @var bool $canModerate
 */
$initials = mb_strtoupper(implode('', array_map(static fn ($word) => mb_substr($word, 0, 1), array_slice(preg_split('/\s+/', trim($review->customerName)) ?: [], 0, 2))));
?>
<article class="review">
    <span class="avatar"><?= e($initials) ?></span>
    <div class="review__body">
        <div class="row-between">
            <div>
                <strong><?= e($review->customerName) ?></strong>
                <div class="cell-sub"><?= e(format_date($review->createdAt, 'j M Y')) ?></div>
            </div>
            <div class="row" style="gap:6px">
                <?= partial('ui/stars', ['rating' => (float) $review->rating, 'compact' => true]) ?>
                <?php if ($canModerate): ?>
                    <?= button('Remove review', ['variant' => 'ghost', 'size' => 'sm', 'icon' => 'trash', 'iconOnly' => true, 'attrs' => [
                        'data-action' => url('/api/reviews/' . $review->code),
                        'data-method' => 'DELETE',
                        'data-confirm' => 'Remove this review? It will no longer count toward the rating.',
                        'data-confirm-title' => 'Remove review',
                        'data-confirm-tone' => 'danger',
                        'data-confirm-button' => 'Remove',
                    ]]) ?>
                <?php endif; ?>
            </div>
        </div>
        <p class="review__text"><?= nl2br(e($review->comment)) ?></p>
    </div>
</article>
