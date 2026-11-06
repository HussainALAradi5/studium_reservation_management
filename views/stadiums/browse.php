<?php
/**
 * @var \App\Dtos\Common\PageResult $page
 * @var array $filters
 * @var \App\Models\Regional\City[] $cities
 * @var array<string, string> $sports
 * @var string $currentPath
 */
$cityOptions = [];
foreach ($cities as $city) {
    $cityOptions[$city->code] = $city->name;
}
?>
<?= partial('ui/pageHeader', [
    'eyebrow' => $page->totalItems . ' stadiums available',
    'eyebrowIcon' => 'stadium',
    'title' => 'Find a stadium',
    'subtitle' => 'Filter by city and sport, then pick a free slot or a full day.',
]) ?>
<div class="stack-lg">
    <?= partial('filters/genericFilter', [
        'path' => $currentPath,
        'values' => $filters,
        'fields' => [
            ['name' => 'q', 'label' => 'Search', 'placeholder' => 'Name, area, or street'],
            ['name' => 'city', 'label' => 'City', 'type' => 'select', 'options' => $cityOptions, 'emptyOption' => 'All cities'],
            ['name' => 'sport', 'label' => 'Sport', 'type' => 'select', 'options' => $sports, 'emptyOption' => 'All sports'],
            ['name' => 'sort', 'label' => 'Sort', 'type' => 'select', 'options' => ['name' => 'Name', 'price' => 'Price', 'created' => 'Newest']],
        ],
    ]) ?>
    <?php if ($page->isEmpty()): ?>
        <div class="card"><?= partial('ui/emptyState', ['title' => 'No stadiums match', 'message' => 'Try another city or sport.', 'icon' => 'search']) ?></div>
    <?php else: ?>
        <div class="grid grid-3" data-stagger>
            <?php foreach ($page->items as $stadium): ?>
                <?= partial('ui/stadiumCard', ['stadium' => $stadium]) ?>
            <?php endforeach; ?>
        </div>
        <?= partial('ui/pagination', ['page' => $page, 'path' => $currentPath, 'filters' => $filters]) ?>
    <?php endif; ?>
</div>
