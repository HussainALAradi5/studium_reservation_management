<?php
/**
 * @var \App\Dtos\Common\PageResult $page
 * @var array $filters
 * @var \App\Models\Regional\City[] $cities
 * @var array<string, string> $sports
 * @var string $currentPath
 */
use App\Enums\Users\Permission;

$cityOptions = [];
foreach ($cities as $city) {
    $cityOptions[$city->code] = $city->name;
}
$rows = [];
foreach ($page->items as $stadium) {
    $rows[] = [
        'name' => '<a class="cell-title" href="' . e(url('/stadiums/' . $stadium->code)) . '">' . e($stadium->name) . '</a><div class="cell-sub mono">' . e($stadium->code) . '</div>',
        'location' => '<div>' . e($stadium->address->cityName) . '</div><div class="cell-sub">' . e($stadium->address->area ?? $stadium->address->street) . '</div>',
        'sport' => badge($stadium->sportType->label(), 'info', ['icon' => 'ball']),
        'price' => '<div class="cell-title nowrap">' . e(money($stadium->pricePerHour)) . ' /hr</div>' . ($stadium->allowsDailyBooking() ? '<div class="cell-sub">' . e(money($stadium->pricePerDay)) . ' /day</div>' : ''),
        'manager' => e($stadium->managerName ?? '—'),
        'status' => $stadium->isActive ? badge('Open', 'success', ['live' => true]) : badge('Inactive', 'neutral'),
        'actions' => '<div class="actions">'
            . button('Edit', ['variant' => 'ghost', 'size' => 'sm', 'icon' => 'edit', 'iconOnly' => true, 'href' => url("/manage/stadiums/{$stadium->code}/edit")])
            . button($stadium->isActive ? 'Deactivate' : 'Activate', ['variant' => $stadium->isActive ? 'danger-soft' : 'secondary', 'size' => 'sm', 'icon' => 'power', 'iconOnly' => true, 'attrs' => [
                'data-action' => url("/api/stadiums/{$stadium->code}/toggle"),
                'data-confirm' => $stadium->isActive ? 'Deactivate this stadium? Customers will no longer see it.' : 'Open this stadium for bookings?',
                'data-confirm-title' => $stadium->isActive ? 'Deactivate stadium' : 'Activate stadium',
                'data-confirm-tone' => $stadium->isActive ? 'danger' : 'success',
            ]])
            . '</div>',
    ];
}
?>
<?= partial('ui/pageHeader', [
    'title' => current_user()->isAdmin() ? 'All stadiums' : 'My stadiums',
    'subtitle' => 'Prices, hours, location, and availability of the stadiums you run.',
    'actions' => can(Permission::STADIUMS_CREATE) ? button('New stadium', ['icon' => 'plus', 'href' => url('/manage/stadiums/create')]) : null,
]) ?>
<div class="stack">
    <?= partial('filters/genericFilter', [
        'path' => $currentPath,
        'values' => $filters,
        'fields' => [
            ['name' => 'q', 'label' => 'Search', 'placeholder' => 'Name, area, or street'],
            ['name' => 'city', 'label' => 'City', 'type' => 'select', 'options' => $cityOptions, 'emptyOption' => 'All cities'],
            ['name' => 'sport', 'label' => 'Sport', 'type' => 'select', 'options' => $sports, 'emptyOption' => 'All sports'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Open', 'inactive' => 'Inactive'], 'emptyOption' => 'Any status'],
        ],
    ]) ?>
    <div class="card" data-animate="fade-up">
        <div class="card__body">
            <?= partial('ui/genericTable', [
                'columns' => ['name' => 'Stadium', 'location' => 'Location', 'sport' => 'Sport', 'price' => 'Price', 'manager' => 'Manager', 'status' => 'Status', 'actions' => ''],
                'rows' => $rows,
                'alignRight' => ['actions'],
                'empty' => ['title' => 'No stadiums', 'message' => 'Stadiums assigned to you will appear here.', 'icon' => 'stadium'],
            ]) ?>
            <?= partial('ui/pagination', ['page' => $page, 'path' => $currentPath, 'filters' => $filters]) ?>
        </div>
    </div>
</div>
