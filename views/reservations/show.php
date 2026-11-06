<?php
/**
 * @var \App\Models\Reservations\Reservation $reservation
 * @var \App\Models\Accounting\Payment[] $payments
 * @var bool $canManage
 * @var bool $canCancel
 * @var bool $canReschedule
 * @var array<string, string> $methods
 * @var array<string, string> $transactionTypes
 * @var array<string, string> $bookingUnits
 */
use App\Constants\Common\RegexPatterns;
use App\Enums\Reservations\BookingUnit;
use App\Enums\Users\Permission;

$canRecordPayments = $canManage && can(Permission::PAYMENTS_RECORD);
$isDaily = $reservation->bookingUnit === BookingUnit::DAY;
$sameDay = substr($reservation->startAt, 0, 10) === substr($reservation->endAt, 0, 10);
?>
<?= partial('ui/pageHeader', [
    'eyebrow' => 'Booking ' . $reservation->code,
    'eyebrowIcon' => 'receipt',
    'title' => $reservation->stadiumName,
    'subtitle' => $reservation->cityName,
    'actions' => button('Back', ['variant' => 'ghost', 'href' => url('/reservations'), 'icon' => 'chevron-left'])
        . button('Stadium', ['variant' => 'glass', 'href' => url('/stadiums/' . $reservation->stadiumCode), 'icon' => 'stadium']),
]) ?>

<div class="split">
    <div class="stack">
        <?= partial('ui/genericCard', [
            'title' => 'Booking details',
            'icon' => 'calendar',
            'animate' => 'fade-up',
            'actions' => partial('ui/statusBadges', ['reservation' => $reservation, 'large' => true]),
            'body' => '<dl class="details">'
                . '<div><dt>Type</dt><dd>' . badge($reservation->bookingUnit->label(), $isDaily ? 'primary' : 'info', ['icon' => $isDaily ? 'calendar' : 'clock']) . '</dd></div>'
                . '<div><dt>Starts</dt><dd>' . e(format_date($reservation->startAt, 'D, j M Y · H:i')) . '</dd></div>'
                . '<div><dt>Ends</dt><dd>' . e(format_date($reservation->endAt, $sameDay ? 'H:i' : 'D, j M Y · H:i')) . '</dd></div>'
                . '<div><dt>Length</dt><dd>' . e($reservation->sizeLabel()) . '</dd></div>'
                . '<div><dt>Customer</dt><dd>' . e($reservation->customerName) . '<div class="cell-sub">' . e($reservation->customerPhone) . '</div></dd></div>'
                . '<div><dt>Requested</dt><dd>' . e(format_date($reservation->createdAt, 'j M Y, H:i')) . '</dd></div>'
                . '</dl>'
                . ($reservation->notes ? '<hr class="divider" style="margin:18px 0"><div class="small muted">Notes</div><p style="margin:4px 0 0">' . e($reservation->notes) . '</p>' : '')
                . ($reservation->statusReason ? '<div class="alert tone-' . e($reservation->status->tone()) . '" style="margin-top:16px">' . icon('info') . '<div><strong>' . e($reservation->status->label()) . ':</strong> ' . e($reservation->statusReason) . '</div></div>' : ''),
            'footer' => ($canCancel || $canManage || $canReschedule)
                ? ($canReschedule ? button('Reschedule', ['variant' => 'glass', 'icon' => 'refresh', 'attrs' => ['data-open-dialog' => '#reschedule-dialog']]) : '')
                    . partial('reservations/actions', ['reservation' => $reservation, 'canManage' => $canManage, 'canCancel' => $canCancel])
                : null,
        ]) ?>

        <?php
        ob_start();
        if ($payments === []) {
            echo partial('ui/emptyState', ['title' => 'No payments yet', 'icon' => 'wallet', 'message' => $canRecordPayments ? 'Record cash, card, or transfer payments here.' : 'Payments made at the stadium appear here.']);
        } else {
            echo '<ul class="ledger" data-stagger>';
            foreach ($payments as $payment) {
                $tone = $payment->isRefund() ? 'danger' : 'success';
                echo '<li class="ledger__item"><span class="ledger__icon tone-' . $tone . '">' . icon($payment->isRefund() ? 'undo' : 'cash') . '</span><div>'
                    . '<div class="cell-title">' . e($payment->type->label()) . ' · ' . e($payment->method->label()) . '</div>'
                    . '<div class="cell-sub">' . e(format_date($payment->paidAt, 'j M Y, H:i')) . ($payment->reference ? ' · Ref ' . e($payment->reference) : '') . ($payment->recordedByName ? ' · by ' . e($payment->recordedByName) : '') . '</div>'
                    . '</div><span class="ledger__amount ' . ($payment->isRefund() ? 'text-danger' : 'text-success') . '">' . ($payment->isRefund() ? '−' : '+') . e(money($payment->amount)) . '</span></li>';
            }
            echo '</ul>';
        }
        $ledger = ob_get_clean();
        ?>
        <?= partial('ui/genericCard', [
            'title' => 'Payments',
            'icon' => 'wallet',
            'animate' => 'fade-up',
            'actions' => $canRecordPayments
                ? button('Add payment', ['size' => 'sm', 'icon' => 'plus', 'attrs' => ['data-open-dialog' => '#payment-dialog', 'data-fill' => json_encode(['type' => 'payment', 'amount' => number_format($reservation->balance(), 3, '.', '')])]])
                    . ($reservation->paidAmount > 0 ? button('Refund', ['size' => 'sm', 'variant' => 'danger-soft', 'icon' => 'undo', 'attrs' => ['data-open-dialog' => '#payment-dialog', 'data-fill' => json_encode(['type' => 'refund', 'amount' => number_format($reservation->paidAmount, 3, '.', '')])]]) : '')
                : null,
            'body' => $ledger,
        ]) ?>
    </div>

    <?= partial('ui/genericCard', [
        'title' => 'Amount',
        'icon' => 'receipt',
        'animate' => 'slide-in-right',
        'class' => 'booking',
        'body' => '<div class="summary">'
            . '<div class="summary__row"><span class="muted">' . e($reservation->sizeLabel()) . ' × ' . e(money($reservation->unitPrice)) . '</span><span>' . e(money($reservation->totalAmount)) . '</span></div>'
            . '<div class="summary__row"><span class="muted">Paid</span><span class="text-success">' . e(money($reservation->paidAmount)) . '</span></div>'
            . '<hr class="divider">'
            . '<div class="summary__row"><strong>Balance</strong><span class="summary__total">' . e(money($reservation->balance())) . '</span></div>'
            . ($reservation->overpaidAmount() > 0 ? '<div class="summary__row text-danger"><span>Overpaid</span><strong>' . e(money($reservation->overpaidAmount())) . '</strong></div>' : '')
            . '</div>',
    ]) ?>
</div>

<?php if ($canReschedule): ?>
    <?= partial('ui/dialog', [
        'id' => 'reschedule-dialog',
        'title' => 'Reschedule booking',
        'body' => '<p class="muted">Pick a new time. We check for conflicts and suggest free alternatives if it is taken.</p>'
            . partial('forms/genericForm', [
                'action' => "/api/reservations/{$reservation->code}/reschedule",
                'onSuccess' => 'reload',
                'submitText' => 'Move booking',
                'submitIcon' => 'refresh',
                'loadingText' => 'Checking…',
                'cancelDialog' => true,
                'columns' => 2,
                'fields' => [
                    ['name' => 'bookingUnit', 'label' => 'Booking type', 'type' => 'select', 'options' => $bookingUnits, 'value' => $reservation->bookingUnit->value, 'full' => true, 'attrs' => ['data-unit-switch' => true]],
                    ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'value' => substr($reservation->startAt, 0, 10), 'min' => date('Y-m-d')],
                    ['name' => 'startTime', 'label' => 'Start time', 'type' => 'time', 'value' => substr($reservation->startAt, 11, 5), 'class' => 'unit-hour', 'minuteStep' => 30],
                    ['name' => 'durationMinutes', 'label' => 'Duration (minutes)', 'type' => 'number', 'value' => $isDaily ? 60 : $reservation->durationMinutes, 'min' => 30, 'step' => 30, 'class' => 'unit-hour'],
                    ['name' => 'days', 'label' => 'Number of days', 'type' => 'number', 'value' => $isDaily ? (int) $reservation->unitCount : 1, 'min' => 1, 'class' => 'unit-day'],
                ],
            ]),
    ]) ?>
<?php endif; ?>

<?php if ($canRecordPayments): ?>
    <?= partial('ui/dialog', [
        'id' => 'payment-dialog',
        'title' => 'Record payment',
        'icon' => 'wallet',
        'body' => partial('forms/genericForm', [
            'action' => "/api/reservations/{$reservation->code}/payments",
            'onSuccess' => 'reload',
            'submitText' => 'Save',
            'loadingText' => 'Saving…',
            'cancelDialog' => true,
            'columns' => 2,
            'fields' => [
                ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => $transactionTypes, 'required' => true],
                ['name' => 'method', 'label' => 'Method', 'type' => 'select', 'options' => $methods, 'required' => true],
                ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'step' => '0.001', 'min' => '0.001', 'required' => true, 'icon' => 'cash', 'hint' => 'Balance: ' . money($reservation->balance()) . ' · Paid: ' . money($reservation->paidAmount)],
                ['name' => 'paidAt', 'label' => 'Paid at', 'type' => 'datetime', 'required' => true, 'value' => date('Y-m-d H:i'), 'max' => date('Y-m-d')],
                ['name' => 'reference', 'label' => 'Reference', 'placeholder' => 'Receipt or transaction no.', 'pattern' => RegexPatterns::PAYMENT_REFERENCE],
                ['name' => 'notes', 'label' => 'Notes'],
            ],
        ]),
    ]) ?>
<?php endif; ?>
