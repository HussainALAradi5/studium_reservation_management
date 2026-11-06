<?php
/**
 * Reservation status + payment status badges, used in lists and detail pages.
 *
 * @var \App\Models\Reservations\Reservation $reservation
 * @var bool|null $large
 */
use App\Enums\Reservations\ReservationStatus;

$large ??= false;
?>
<span class="row" style="gap:6px">
    <?= badge($reservation->status->label(), $reservation->status->tone(), ['live' => $reservation->status === ReservationStatus::PENDING, 'large' => $large]) ?>
    <?= badge($reservation->paymentStatus->label(), $reservation->paymentStatus->tone(), ['icon' => 'wallet', 'large' => $large]) ?>
    <?php if ($reservation->isRefundDue()): ?>
        <?= badge('Refund due', 'danger', ['icon' => 'alert', 'large' => $large]) ?>
    <?php endif; ?>
</span>
