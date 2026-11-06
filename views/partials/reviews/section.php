<?php
/**
 * Stadium reviews. Guests see the average only, with the comments locked behind sign-in.
 * The form appears only when the user may rate (played here, not rated yet); the server re-checks.
 *
 * @var \App\Models\Stadiums\Stadium $stadium
 * @var \App\Dtos\Common\PageResult|null $reviewPage
 * @var \App\Enums\Reviews\ReviewEligibility|null $eligibility
 * @var bool $canModerateReviews
 */
use App\Constants\Reviews\ReviewRules;
use App\Enums\Reviews\ReviewEligibility;

$user = current_user();
ob_start();
?>
<?php if ($user === null): ?>
    <div class="locked" style="min-height:220px">
        <div class="locked__overlay" role="region" aria-label="Reviews locked">
            <div class="locked__icon"><?= icon('lock', 'icon icon-lg') ?></div>
            <strong class="locked__title">Sign in to read reviews</strong>
            <p class="muted small" style="margin:0">Comments from players are visible to members only.</p>
            <div class="btn-group" style="justify-content:center">
                <?= button('Sign in', ['icon' => 'login', 'href' => url('/login', ['next' => '/stadiums/' . $stadium->code])]) ?>
                <?= button('Register', ['variant' => 'secondary', 'icon' => 'sparkles', 'href' => url('/register', ['next' => '/stadiums/' . $stadium->code])]) ?>
            </div>
        </div>
        <div class="locked__preview" aria-hidden="true">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="review"><span class="avatar">··</span><div class="review__body"><div class="skeleton" style="width:40%"></div><div class="skeleton" style="margin-top:8px"></div></div></div>
            <?php endfor; ?>
        </div>
    </div>
<?php else: ?>
    <div class="stack">
        <?php if ($eligibility === ReviewEligibility::CAN_REVIEW): ?>
            <div class="review-form">
                <?= partial('forms/genericForm', [
                    'action' => '/api/stadiums/' . $stadium->code . '/reviews',
                    'submitText' => 'Post review',
                    'submitIcon' => 'star',
                    'loadingText' => 'Posting…',
                    'fields' => [
                        ['name' => 'rating', 'label' => 'Your rating', 'type' => 'rating', 'required' => true],
                        ['name' => 'comment', 'label' => 'Your review', 'type' => 'textarea', 'required' => true, 'maxlength' => ReviewRules::MAX_COMMENT, 'placeholder' => 'Pitch quality, lighting, changing rooms…', 'hint' => 'You can rate each stadium once.'],
                    ],
                ]) ?>
            </div>
        <?php elseif ($eligibility !== null && $eligibility !== ReviewEligibility::NOT_A_CUSTOMER): ?>
            <div class="alert tone-<?= $eligibility === ReviewEligibility::ALREADY_REVIEWED ? 'success' : 'info' ?>">
                <?= icon($eligibility === ReviewEligibility::ALREADY_REVIEWED ? 'check-circle' : 'info') ?>
                <div><?= e($eligibility->message()) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($reviewPage->isEmpty()): ?>
            <?= partial('ui/emptyState', ['title' => 'No reviews yet', 'message' => 'Players who rent this stadium can rate it after their game.', 'icon' => 'star']) ?>
        <?php else: ?>
            <div class="reviews" data-reviews data-stagger
                 data-stadium-code="<?= e($stadium->code) ?>"
                 data-can-moderate="<?= $canModerateReviews ? '1' : '0' ?>"
                 data-page="1">
                <?php foreach ($reviewPage->items as $review): ?>
                    <?= partial('reviews/item', ['review' => $review, 'canModerate' => $canModerateReviews]) ?>
                <?php endforeach; ?>
            </div>
            <?php if ($reviewPage->totalPages() > 1): ?>
                <?= button('Show more reviews', ['variant' => 'secondary', 'icon' => 'refresh', 'loadingText' => 'Loading…', 'attrs' => ['data-reviews-more' => true]]) ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php
$body = ob_get_clean();
?>
<div id="reviews">
    <?= partial('ui/genericCard', [
        'title' => 'Reviews',
        'icon' => 'message',
        'animate' => 'fade-up',
        'actions' => partial('ui/stars', ['rating' => $stadium->ratingAverage, 'count' => $stadium->ratingCount]),
        'body' => $body,
    ]) ?>
</div>
