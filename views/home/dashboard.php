<?php
/**
 * @var \App\Models\Reservations\Reservation[] $upcoming
 * @var array<string, int> $statusCounts
 * @var int $cancelCutoffHours
 * @var array|null $summary
 * @var array<string, int> $roleCounts
 */
use App\Enums\Reservations\ReservationStatus;

$user = current_user();
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$count = static fn (ReservationStatus $status) => (int) ($statusCounts[$status->value] ?? 0);
?>
<?= partial('ui/pageHeader', [
    'eyebrow' => date('l, j F Y'),
    'eyebrowIcon' => 'calendar',
    'title' => $greeting . ', ' . explode(' ', $user->fullName)[0] . ' 👋',
    'subtitle' => $user->isCustomer() ? 'Ready for your next game?' : 'Here is what is happening at your stadiums.',
    'actions' => $user->isCustomer()
        ? button('Book a stadium', ['href' => url('/stadiums'), 'icon' => 'plus'])
        : button('Review bookings', ['href' => url('/reservations', ['status' => 'pending']), 'icon' => 'calendar']),
]) ?>

<div class="grid grid-4" data-stagger style="margin-bottom:24px">
    <?= partial('ui/statCard', ['label' => 'Waiting for approval', 'value' => (string) $count(ReservationStatus::PENDING), 'icon' => 'clock', 'tone' => 'warning']) ?>
    <?= partial('ui/statCard', ['label' => 'Confirmed', 'value' => (string) $count(ReservationStatus::CONFIRMED), 'icon' => 'check-circle', 'tone' => 'info']) ?>
    <?= partial('ui/statCard', ['label' => 'Completed', 'value' => (string) $count(ReservationStatus::COMPLETED), 'icon' => 'ball', 'tone' => 'success']) ?>
    <?php if ($summary): ?>
        <?= partial('ui/statCard', ['label' => 'Net revenue this month', 'value' => money($summary['netRevenue']), 'icon' => 'wallet', 'tone' => 'primary', 'hint' => 'Utilization ' . $summary['utilization'] . '%']) ?>
    <?php else: ?>
        <?= partial('ui/statCard', ['label' => 'Cancelled', 'value' => (string) $count(ReservationStatus::CANCELLED), 'icon' => 'x-circle', 'tone' => 'danger']) ?>
    <?php endif; ?>
</div>

<div class="split">
    <?= partial('ui/genericCard', [
        'title' => 'Upcoming bookings',
        'icon' => 'calendar',
        'animate' => 'fade-up',
        'actions' => button('See all', ['variant' => 'ghost', 'size' => 'sm', 'href' => url('/reservations'), 'iconRight' => 'arrow-right']),
        'body' => partial('reservations/table', [
            'reservations' => $upcoming,
            'cancelCutoffHours' => $cancelCutoffHours,
            'showCustomer' => !$user->isCustomer(),
            'empty' => ['title' => 'No upcoming bookings', 'message' => $user->isCustomer() ? 'Find a stadium and book your first game.' : 'New requests will show up here.', 'icon' => 'calendar'],
        ]),
    ]) ?>

    <div class="stack">
        <?php if ($summary): ?>
            <?= partial('ui/genericCard', [
                'title' => 'This month',
                'icon' => 'chart',
                'animate' => 'slide-in-right',
                'body' => '<dl class="details">'
                    . '<div><dt>Net revenue</dt><dd>' . e(money($summary['netRevenue'])) . '</dd></div>'
                    . '<div><dt>Expenses</dt><dd>' . e(money($summary['expenses'])) . '</dd></div>'
                    . '<div><dt>Profit</dt><dd class="' . ($summary['netProfit'] < 0 ? 'text-danger' : 'text-success') . '">' . e(money($summary['netProfit'])) . '</dd></div>'
                    . '<div><dt>Outstanding</dt><dd>' . e(money($summary['outstanding'])) . '</dd></div>'
                    . '</dl><div style="margin-top:16px"><div class="row-between small"><span class="muted">Utilization</span><strong>' . e($summary['utilization']) . '%</strong></div>'
                    . '<div class="meter" style="margin-top:6px"><div class="meter__fill" style="--w:' . e(min(100, $summary['utilization'])) . '%"></div></div></div>',
                'footer' => button('Open accounting', ['variant' => 'secondary', 'size' => 'sm', 'href' => url('/accounting/reports'), 'iconRight' => 'arrow-right']),
            ]) ?>
        <?php endif; ?>
        <?php if ($roleCounts !== []): ?>
            <?= partial('ui/genericCard', [
                'title' => 'People',
                'icon' => 'users',
                'animate' => 'slide-in-right',
                'body' => '<div class="row">'
                    . badge(($roleCounts['admin'] ?? 0) . ' admins', 'danger')
                    . badge(($roleCounts['manager'] ?? 0) . ' managers', 'info')
                    . badge(($roleCounts['customer'] ?? 0) . ' customers', 'success')
                    . '</div>',
                'footer' => button('Manage users', ['variant' => 'secondary', 'size' => 'sm', 'href' => url('/users'), 'iconRight' => 'arrow-right']),
            ]) ?>
        <?php endif; ?>
        <?php if ($user->isCustomer()): ?>
            <?= partial('ui/genericCard', [
                'title' => 'How booking works',
                'icon' => 'info',
                'animate' => 'slide-in-right',
                'body' => '<ol class="muted" style="margin:0;padding-left:18px;display:grid;gap:6px">'
                    . '<li>Pick a stadium and a day.</li><li>Choose free hourly slots or full days.</li>'
                    . '<li>The stadium confirms your request.</li><li>Pay at the venue — see the balance on your booking.</li></ol>',
            ]) ?>
        <?php endif; ?>
    </div>
</div>
