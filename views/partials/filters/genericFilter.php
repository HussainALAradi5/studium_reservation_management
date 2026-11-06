<?php
/**
 * genericFilter — GET filter bar for any list. Changes submit automatically (search is debounced) via
 * assets/javascripts/ui/filters.js; works without JavaScript through the Apply button.
 *
 * @var string $path     page path the filter submits to
 * @var array  $values   current query values ($request->queryAll())
 * @var array  $fields   [{name, label, type: search|select|date, options?, emptyOption?, placeholder?}]
 */
$hasActive = false;
foreach ($fields as $definition) {
    if (($values[$definition['name']] ?? '') !== '') {
        $hasActive = true;
    }
}
?>
<form class="filters glass anim-fade-up" method="get" action="<?= e(url($path)) ?>" data-filter-form role="search">
    <?php foreach ($fields as $definition): ?>
        <?php
        $type = $definition['type'] ?? 'search';
        $field = [
            ...$definition,
            'value' => $values[$definition['name']] ?? '',
            'type' => $type,
            'icon' => $definition['icon'] ?? ($type === 'search' ? 'search' : null),
            'class' => $type === 'search' ? 'field--search' : null,
            'attrs' => $type === 'search' ? ['data-filter-search' => true] : [],
        ];
        ?>
        <?= partial('forms/genericField', ['field' => $field]) ?>
    <?php endforeach; ?>
    <div class="filters__actions">
        <noscript><?= button('Apply', ['type' => 'submit', 'variant' => 'secondary']) ?></noscript>
        <?php if ($hasActive): ?>
            <?= button('Clear', ['variant' => 'ghost', 'icon' => 'x', 'href' => url($path)]) ?>
        <?php endif; ?>
    </div>
</form>
