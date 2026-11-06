<?php
/**
 * Reservations table shared by the dashboard and the reservations list.
 *
 * @var \App\Models\Reservations\Reservation[] $reservations
 * @var bool $showCustomer
 * @var array|null $empty
 */
use App\Services\Common\AccessPolicy;
use App\Services\Reservations\ReservationService;

$user = current_user();
$cutoff = (new ReservationService())->cancelCutoffHours();
$columns = ['booking' => 'Booking', 'when' => 'When', 'size' => 'Rental'];
if ($showCustomer) {
    $columns['customer'] = 'Customer';
}
$columns += ['total' => 'Total', 'status' => 'Status', 'actions' => ''];

$rows = [];
foreach ($reservations as $reservation) {
    $canManage = AccessPolicy::canManageReservation($user, $reservation);
    $canCancel = $reservation->status->blocksSlot() && AccessPolicy::canCancelReservation($user, $reservation, $cutoff);
    $sameDay = substr($reservation->startAt, 0, 10) === substr($reservation->endAt, 0, 10);
    $rows[] = [
        'booking' => '<a class="cell-title" href="' . e(url('/reservations/' . $reservation->code)) . '">' . e($reservation->stadiumName) . '</a>'
            . '<div class="cell-sub mono">' . e($reservation->code) . '</div>',
        'when' => '<div class="cell-title">' . e(format_date($reservation->startAt)) . '</div><div class="cell-sub">'
            . e(format_time($reservation->startAt)) . ' – ' . e($sameDay ? format_time($reservation->endAt) : format_date($reservation->endAt, 'D j M, H:i')) . '</div>',
        'size' => badge($reservation->sizeLabel(), $reservation->bookingUnit->value === 'day' ? 'primary' : 'info', ['icon' => $reservation->bookingUnit->value === 'day' ? 'calendar' : 'clock']),
        'customer' => '<div class="cell-title">' . e($reservation->customerName) . '</div><div class="cell-sub">' . e($reservation->customerPhone) . '</div>',
        'total' => '<div class="cell-title nowrap">' . e(money($reservation->totalAmount)) . '</div>'
            . ($reservation->balance() > 0 && $reservation->status->blocksSlot() ? '<div class="cell-sub">Due ' . e(money($reservation->balance())) . '</div>' : ''),
        'status' => partial('ui/statusBadges', ['reservation' => $reservation]),
        'actions' => '<div class="actions">' . partial('reservations/actions', ['reservation' => $reservation, 'canManage' => $canManage, 'canCancel' => $canCancel, 'compact' => true]) . '</div>',
    ];
}
?>
<?= partial('ui/genericTable', ['columns' => $columns, 'rows' => $rows, 'alignRight' => ['actions'], 'empty' => $empty ?? ['title' => 'No bookings yet', 'icon' => 'calendar']]) ?>
