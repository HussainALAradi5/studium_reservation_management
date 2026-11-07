<?php
/**
 * @var \App\Dtos\Common\PageResult $page
 * @var array $filters
 * @var int $cancelCutoffHours
 * @var array<string, string> $statuses
 * @var \App\Models\Stadiums\Stadium[] $stadiums
 * @var string $currentPath
 */
$user = current_user();
$filterFields = [
    ['name' => 'q', 'label' => 'Search', 'placeholder' => $user->isCustomer() ? 'Code or stadium' : 'Code, customer, phone, stadium'],
    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $statuses, 'emptyOption' => 'All statuses'],
];
if (!$user->isCustomer()) {
    $stadiumOptions = [];
    foreach ($stadiums as $stadium) {
        $stadiumOptions[$stadium->code] = $stadium->name;
    }
    $filterFields[] = ['name' => 'stadium', 'label' => 'Stadium', 'type' => 'select', 'options' => $stadiumOptions, 'emptyOption' => 'All stadiums'];
}
$filterFields[] = ['name' => 'from', 'label' => 'From', 'type' => 'date'];
$filterFields[] = ['name' => 'to', 'label' => 'To', 'type' => 'date'];
?>
<?= partial('ui/pageHeader', [
    'title' => $user->isCustomer() ? 'My bookings' : 'Reservations',
    'subtitle' => $user->isCustomer() ? 'Every stadium you have rented, past and upcoming.' : 'Approve requests, track payments, and keep the schedule clean.',
    'actions' => $user->isCustomer() ? button('New booking', ['href' => url('/stadiums'), 'icon' => 'plus']) : null,
]) ?>
<div class="stack">
    <?= partial('filters/genericFilter', ['path' => $currentPath, 'values' => $filters, 'fields' => $filterFields]) ?>
    <div class="card" data-animate="fade-up">
        <div class="card__body">
            <?= partial('reservations/table', [
                'reservations' => $page->items,
                'cancelCutoffHours' => $cancelCutoffHours,
                'showCustomer' => !$user->isCustomer(),
                'empty' => ['title' => 'No bookings match', 'message' => 'Try clearing the filters.', 'icon' => 'calendar'],
            ]) ?>
            <?= partial('ui/pagination', ['page' => $page, 'path' => $currentPath, 'filters' => $filters]) ?>
        </div>
    </div>
</div>
