<?php
/**
 * @var array $report  ReportService::buildSummary()
 * @var array<string, string> $statuses
 * @var array<string, string> $categories
 * @var string $currentPath
 */
use App\Enums\Reservations\ReservationStatus;

$stadiumOptions = [];
foreach ($report['stadiums'] as $stadium) {
    $stadiumOptions[$stadium->code] = $stadium->name;
}
$daily = $report['daily'];
$maxDaily = max(1, ...array_map('abs', array_values($daily ?: [0])));
$maxCategory = max(1, ...array_values($report['expensesByCategory'] ?: [0]));

$stadiumRows = [];
foreach ($report['perStadium'] as $row) {
    $stadiumRows[] = [
        'stadium' => '<span class="cell-title">' . e($row['name']) . '</span>',
        'bookings' => e($row['bookings']),
        'net' => e(money($row['net'])),
        'expenses' => e(money($row['expenses'])),
        'profit' => '<strong class="' . ($row['profit'] < 0 ? 'text-danger' : 'text-success') . '">' . e(money($row['profit'])) . '</strong>',
    ];
}
?>
<?= partial('ui/pageHeader', [
    'eyebrow' => format_date($report['from'], 'j M Y') . ' → ' . format_date($report['to'], 'j M Y'),
    'eyebrowIcon' => 'calendar',
    'title' => 'Accounting',
    'subtitle' => 'Cash received from rentals, refunds, expenses, and profit.',
]) ?>
<div class="stack-lg">
    <?= partial('filters/genericFilter', [
        'path' => $currentPath,
        'values' => ['from' => $report['from'], 'to' => $report['to'], 'stadium' => $report['selectedStadiumCode']],
        'fields' => [
            ['name' => 'stadium', 'label' => 'Stadium', 'type' => 'select', 'options' => $stadiumOptions, 'emptyOption' => 'All my stadiums'],
            ['name' => 'from', 'label' => 'From', 'type' => 'date'],
            ['name' => 'to', 'label' => 'To', 'type' => 'date'],
        ],
    ]) ?>

    <div class="grid grid-4" data-stagger>
        <?= partial('ui/statCard', ['label' => 'Net revenue', 'value' => money($report['netRevenue']), 'icon' => 'wallet', 'tone' => 'primary', 'hint' => 'Gross ' . money($report['grossRevenue']) . ' − refunds ' . money($report['refunds'])]) ?>
        <?= partial('ui/statCard', ['label' => 'Expenses', 'value' => money($report['expenses']), 'icon' => 'receipt', 'tone' => 'warning']) ?>
        <?= partial('ui/statCard', ['label' => 'Net profit', 'value' => money($report['netProfit']), 'icon' => 'chart', 'tone' => $report['netProfit'] < 0 ? 'danger' : 'success']) ?>
        <?= partial('ui/statCard', ['label' => 'Outstanding', 'value' => money($report['outstanding']), 'icon' => 'clock', 'tone' => 'info', 'hint' => 'Booked value ' . money($report['bookedValue'])]) ?>
    </div>

    <div class="split">
        <?php ob_start(); ?>
        <div class="bars" role="img" aria-label="Net revenue per day">
            <?php foreach ($daily as $day => $amount): ?>
                <div class="bars__bar<?= $amount < 0 ? ' is-negative' : '' ?>" style="--h: <?= e(max(2, round(abs($amount) / $maxDaily * 100, 1))) ?>%" title="<?= e(format_date($day, 'j M') . ': ' . money($amount)) ?>"></div>
            <?php endforeach; ?>
        </div>
        <div class="bars__axis"><span><?= e(format_date($report['from'], 'j M')) ?></span><span><?= e(format_date($report['to'], 'j M')) ?></span></div>
        <?php $chart = ob_get_clean(); ?>
        <?= partial('ui/genericCard', ['title' => 'Daily net revenue', 'icon' => 'chart', 'animate' => 'fade-up', 'body' => $chart]) ?>

        <?php ob_start(); ?>
        <div class="stack" style="gap:12px">
            <div>
                <div class="row-between small"><span class="muted">Utilization (<?= e($report['bookedHours']) ?> h booked)</span><strong><?= e($report['utilization']) ?>%</strong></div>
                <div class="meter" style="margin-top:6px"><div class="meter__fill" style="--w: <?= e(min(100, $report['utilization'])) ?>%"></div></div>
            </div>
            <hr class="divider">
            <div class="row" style="gap:6px">
                <?php foreach (ReservationStatus::cases() as $status): ?>
                    <?= badge($status->label() . ': ' . (int) ($report['statusCounts'][$status->value] ?? 0), $status->tone()) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php $bookingsBody = ob_get_clean(); ?>
        <?= partial('ui/genericCard', ['title' => 'Bookings', 'icon' => 'calendar', 'animate' => 'slide-in-right', 'body' => $bookingsBody]) ?>
    </div>

    <div class="split">
        <?= partial('ui/genericCard', [
            'title' => 'Per stadium',
            'icon' => 'stadium',
            'animate' => 'fade-up',
            'body' => partial('ui/genericTable', [
                'columns' => ['stadium' => 'Stadium', 'bookings' => 'Bookings', 'net' => 'Net revenue', 'expenses' => 'Expenses', 'profit' => 'Profit'],
                'rows' => $stadiumRows,
                'alignRight' => ['net', 'expenses', 'profit'],
                'empty' => ['title' => 'No stadiums in scope', 'icon' => 'stadium'],
            ]),
        ]) ?>
        <?php ob_start(); ?>
        <?php if ($report['expensesByCategory'] === []): ?>
            <?= partial('ui/emptyState', ['title' => 'No expenses in this period', 'icon' => 'receipt']) ?>
        <?php else: ?>
            <div class="stack" style="gap:12px">
                <?php foreach ($report['expensesByCategory'] as $category => $amount): ?>
                    <div class="category-row">
                        <span><?= e($categories[$category] ?? $category) ?></span>
                        <div class="meter"><div class="meter__fill" style="--w: <?= e(round($amount / $maxCategory * 100, 1)) ?>%"></div></div>
                        <strong class="nowrap"><?= e(money($amount)) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php $categoryBody = ob_get_clean(); ?>
        <?= partial('ui/genericCard', ['title' => 'Expenses by category', 'icon' => 'receipt', 'animate' => 'slide-in-right', 'body' => $categoryBody]) ?>
    </div>
</div>
