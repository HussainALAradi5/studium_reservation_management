<?php
/**
 * Lifecycle buttons for one reservation. Each button calls the API through data-action
 * (assets/javascripts/ui/action-buttons.js), which asks for confirmation and, when needed, a reason.
 *
 * @var \App\Models\Reservations\Reservation $reservation
 * @var bool $canManage
 * @var bool $canCancel
 * @var bool|null $compact   icon-only buttons for tables
 */
use App\Enums\Reservations\ReservationStatus;

$compact ??= false;
$api = static fn (string $action) => url("/api/reservations/{$reservation->code}/$action");
$size = $compact ? 'sm' : 'md';
$isCustomer = current_user()?->isCustomer() ?? false;
?>
<div class="btn-group">
    <?php if ($canManage && $reservation->status === ReservationStatus::PENDING): ?>
        <?= button('Confirm', ['variant' => 'success', 'size' => $size, 'icon' => 'check', 'iconOnly' => $compact, 'attrs' => [
            'data-action' => $api('confirm'),
            'data-confirm' => 'Confirm this booking? The time slot will be locked for the customer.',
            'data-confirm-title' => 'Confirm booking',
            'data-confirm-tone' => 'success',
        ]]) ?>
        <?= button('Reject', ['variant' => 'danger-soft', 'size' => $size, 'icon' => 'x', 'iconOnly' => $compact, 'attrs' => [
            'data-action' => $api('reject'),
            'data-confirm' => 'Reject this booking request? The slot becomes free again.',
            'data-confirm-title' => 'Reject booking',
            'data-confirm-tone' => 'danger',
            'data-reason' => 'required',
        ]]) ?>
    <?php endif; ?>
    <?php if ($canManage && $reservation->status === ReservationStatus::CONFIRMED && $reservation->hasEnded()): ?>
        <?= button('Mark completed', ['variant' => 'secondary', 'size' => $size, 'icon' => 'check-circle', 'iconOnly' => $compact, 'attrs' => [
            'data-action' => $api('complete'),
            'data-confirm' => 'Mark this booking as played?',
            'data-confirm-title' => 'Complete booking',
        ]]) ?>
    <?php endif; ?>
    <?php if ($canCancel): ?>
        <?= button('Cancel booking', ['variant' => 'danger-soft', 'size' => $size, 'icon' => 'x-circle', 'iconOnly' => $compact, 'attrs' => [
            'data-action' => $api('cancel'),
            'data-confirm' => $reservation->paidAmount > 0
                ? 'Cancel this booking? ' . money($reservation->paidAmount) . ' was paid and will be marked as refund due.'
                : 'Cancel this booking? The time slot will be released.',
            'data-confirm-title' => 'Cancel booking',
            'data-confirm-tone' => 'danger',
            'data-confirm-button' => 'Yes, cancel it',
            'data-reason' => $isCustomer ? 'optional' : 'required',
        ]]) ?>
    <?php endif; ?>
    <?php if ($compact): ?>
        <?= button('Open', ['variant' => 'ghost', 'size' => 'sm', 'icon' => 'arrow-right', 'iconOnly' => true, 'href' => url('/reservations/' . $reservation->code)]) ?>
    <?php endif; ?>
</div>
