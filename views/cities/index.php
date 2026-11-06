<?php
/**
 * @var \App\Dtos\Common\PageResult $page
 * @var array $filters
 * @var string $currentPath
 */
use App\Constants\Common\RegexPatterns;
use App\Constants\Stadiums\StadiumRules;

$rows = [];
foreach ($page->items as $city) {
    $rows[] = [
        'name' => '<div class="cell-title">' . e($city->name) . '</div><div class="cell-sub mono">' . e($city->code) . '</div>',
        'country' => badge($city->countryCode, 'neutral', ['dot' => false]),
        'stadiums' => badge($city->stadiumCount . ' stadiums', $city->stadiumCount > 0 ? 'info' : 'neutral', ['icon' => 'stadium']),
        'status' => $city->isActive ? badge('Active', 'success') : badge('Inactive', 'neutral'),
        'actions' => '<div class="actions">'
            . button('Edit', ['variant' => 'ghost', 'size' => 'sm', 'icon' => 'edit', 'iconOnly' => true, 'attrs' => [
                'data-open-dialog' => '#city-dialog',
                'data-dialog-title' => 'Edit city',
                'data-form-action' => url('/api/cities/' . $city->code),
                'data-form-method' => 'PUT',
                'data-fill' => json_encode(['name' => $city->name, 'countryCode' => $city->countryCode, 'isActive' => $city->isActive]),
            ]])
            . button('Delete', ['variant' => 'danger-soft', 'size' => 'sm', 'icon' => 'trash', 'iconOnly' => true, 'attrs' => [
                'data-action' => url('/api/cities/' . $city->code),
                'data-method' => 'DELETE',
                'data-confirm' => "Delete {$city->name}? Cities that have stadiums cannot be deleted — deactivate them instead.",
                'data-confirm-title' => 'Delete city',
                'data-confirm-tone' => 'danger',
                'data-confirm-button' => 'Delete',
            ]])
            . '</div>',
    ];
}
?>
<?= partial('ui/pageHeader', [
    'title' => 'Cities',
    'subtitle' => 'Where stadiums can be located. Inactive cities are hidden from search and new addresses.',
    'actions' => button('New city', ['icon' => 'plus', 'attrs' => [
        'data-open-dialog' => '#city-dialog',
        'data-dialog-title' => 'New city',
        'data-form-action' => url('/api/cities'),
        'data-form-method' => 'POST',
        'data-fill' => json_encode(['name' => '', 'countryCode' => StadiumRules::DEFAULT_COUNTRY_CODE, 'isActive' => true]),
    ]]),
]) ?>
<div class="stack">
    <?= partial('filters/genericFilter', ['path' => $currentPath, 'values' => $filters, 'fields' => [['name' => 'q', 'label' => 'Search', 'placeholder' => 'City name']]]) ?>
    <div class="card" data-animate="fade-up">
        <div class="card__body">
            <?= partial('ui/genericTable', [
                'columns' => ['name' => 'City', 'country' => 'Country', 'stadiums' => 'Stadiums', 'status' => 'Status', 'actions' => ''],
                'rows' => $rows,
                'alignRight' => ['actions'],
                'empty' => ['title' => 'No cities yet', 'icon' => 'city'],
            ]) ?>
            <?= partial('ui/pagination', ['page' => $page, 'path' => $currentPath, 'filters' => $filters]) ?>
        </div>
    </div>
</div>

<?= partial('ui/dialog', [
    'id' => 'city-dialog',
    'title' => 'City',
    'size' => 'sm',
    'body' => partial('forms/genericForm', [
        'action' => '/api/cities',
        'onSuccess' => 'reload',
        'cancelDialog' => true,
        'fields' => [
            ['name' => 'name', 'label' => 'City name', 'required' => true, 'icon' => 'city', 'maxlength' => 80],
            ['name' => 'countryCode', 'label' => 'Country code (ISO 3166)', 'required' => true, 'maxlength' => 2, 'pattern' => RegexPatterns::COUNTRY_CODE, 'value' => StadiumRules::DEFAULT_COUNTRY_CODE],
            ['name' => 'isActive', 'label' => 'Active', 'type' => 'switch', 'value' => true],
        ],
    ]),
]) ?>
