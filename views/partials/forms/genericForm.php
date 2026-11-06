<?php
/**
 * genericForm — AJAX form built from field definitions (see genericField.php).
 *
 * @var string      $action     API path, e.g. "/api/cities"
 * @var string|null $method     POST | PUT | DELETE
 * @var array       $fields     field definitions, or ['section' => 'Title'] rows to start a section
 * @var string|null $id
 * @var int|null    $columns    1, 2 or 3
 * @var string|null $submitText
 * @var string|null $submitIcon
 * @var string|null $loadingText
 * @var string|null $onSuccess  redirect | reload | reset | close | none (assets/javascripts/forms/ajax-form.js)
 * @var string|null $cancelHref
 * @var bool|null   $cancelDialog  render a Cancel button that closes the surrounding dialog
 * @var string|null $before     pre-rendered HTML above the fields
 * @var string|null $after      pre-rendered HTML below the fields
 * @var bool|null   $blockSubmit
 */
$columns ??= 1;
$sections = [];
$current = ['title' => null, 'fields' => []];
foreach ($fields as $definition) {
    if (isset($definition['section'])) {
        if ($current['fields'] !== []) {
            $sections[] = $current;
        }
        $current = ['title' => $definition['section'], 'fields' => []];
        continue;
    }
    $current['fields'][] = $definition;
}
$sections[] = $current;
?>
<form class="form" novalidate data-ajax-form
      action="<?= e(url($action)) ?>"
      data-method="<?= e(strtoupper($method ?? 'POST')) ?>"
      data-on-success="<?= e($onSuccess ?? 'redirect') ?>"
      <?= !empty($id) ? 'id="' . e($id) . '"' : '' ?>>
    <?= $before ?? '' ?>
    <?php foreach ($sections as $section): ?>
        <div class="form__section">
            <?php if ($section['title']): ?><h3 class="form__section-title"><?= e($section['title']) ?></h3><?php endif; ?>
            <div class="form__grid form__grid--<?= (int) $columns ?>">
                <?php foreach ($section['fields'] as $field): ?>
                    <?= partial('forms/genericField', ['field' => $field]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?= $after ?? '' ?>
    <div class="form__actions">
        <?php if (!empty($cancelHref)): ?>
            <?= button('Cancel', ['variant' => 'ghost', 'href' => $cancelHref]) ?>
        <?php elseif (!empty($cancelDialog)): ?>
            <?= button('Cancel', ['variant' => 'ghost', 'attrs' => ['data-close-dialog' => true]]) ?>
        <?php endif; ?>
        <?= button($submitText ?? 'Save', [
            'type' => 'submit',
            'icon' => $submitIcon ?? 'check',
            'loadingText' => $loadingText ?? null,
            'block' => !empty($blockSubmit),
            'size' => !empty($blockSubmit) ? 'lg' : 'md',
        ]) ?>
    </div>
</form>
