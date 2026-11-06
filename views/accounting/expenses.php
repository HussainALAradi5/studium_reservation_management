<?php
/**
 * @var \App\Dtos\Common\PageResult $page
 * @var array $filters
 * @var \App\Models\Stadiums\Stadium[] $stadiums
 * @var array<string, string> $categories
 * @var string $currentPath
 */
$stadiumOptions = [];
foreach ($stadiums as $stadium) {
    $stadiumOptions[$stadium->code] = $stadium->name;
}
$pageTotal = 0.0;
$rows = [];
foreach ($page->items as $expense) {
    $pageTotal += $expense->amount;
    $rows[] = [
        'date' => '<span class="cell-title nowrap">' . e(format_date($expense->expenseDate, 'j M Y')) . '</span>',
        'stadium' => e($expense->stadiumName),
        'category' => badge($expense->category->label(), 'warning', ['icon' => 'receipt']),
        'description' => e($expense->description),
        'amount' => '<strong class="nowrap">' . e(money($expense->amount)) . '</strong>',
        'actions' => '<div class="actions">' . button('Delete', ['variant' => 'danger-soft', 'size' => 'sm', 'icon' => 'trash', 'iconOnly' => true, 'attrs' => [
            'data-action' => url('/api/expenses/' . $expense->code),
            'data-method' => 'DELETE',
            'data-confirm' => 'Delete this expense? It will be removed from reports.',
            'data-confirm-title' => 'Delete expense',
            'data-confirm-tone' => 'danger',
            'data-confirm-button' => 'Delete',
        ]]) . '</div>',
    ];
}
?>
<?= partial('ui/pageHeader', [
    'title' => 'Expenses',
    'subtitle' => 'Running costs per stadium — used to calculate profit.',
    'actions' => $stadiumOptions !== [] ? button('New expense', ['icon' => 'plus', 'attrs' => ['data-open-dialog' => '#expense-dialog']]) : null,
]) ?>
<div class="stack">
    <?= partial('filters/genericFilter', [
        'path' => $currentPath,
        'values' => $filters,
        'fields' => [
            ['name' => 'stadium', 'label' => 'Stadium', 'type' => 'select', 'options' => $stadiumOptions, 'emptyOption' => 'All stadiums'],
            ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => $categories, 'emptyOption' => 'All categories'],
            ['name' => 'from', 'label' => 'From', 'type' => 'date'],
            ['name' => 'to', 'label' => 'To', 'type' => 'date'],
        ],
    ]) ?>
    <div class="card" data-animate="fade-up">
        <div class="card__body">
            <?= partial('ui/genericTable', [
                'columns' => ['date' => 'Date', 'stadium' => 'Stadium', 'category' => 'Category', 'description' => 'Description', 'amount' => 'Amount', 'actions' => ''],
                'rows' => $rows,
                'alignRight' => ['amount', 'actions'],
                'empty' => ['title' => 'No expenses recorded', 'icon' => 'receipt'],
            ]) ?>
            <?php if ($rows !== []): ?>
                <div class="row-between" style="padding:12px 16px 0"><span class="muted small">Total on this page</span><strong><?= e(money($pageTotal)) ?></strong></div>
            <?php endif; ?>
            <?= partial('ui/pagination', ['page' => $page, 'path' => $currentPath, 'filters' => $filters]) ?>
        </div>
    </div>
</div>

<?= partial('ui/dialog', [
    'id' => 'expense-dialog',
    'title' => 'New expense',
    'icon' => 'receipt',
    'tone' => 'warning',
    'body' => partial('forms/genericForm', [
        'action' => '/api/expenses',
        'onSuccess' => 'reload',
        'cancelDialog' => true,
        'columns' => 2,
        'fields' => [
            ['name' => 'stadiumCode', 'label' => 'Stadium', 'type' => 'select', 'required' => true, 'options' => $stadiumOptions, 'full' => true],
            ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'required' => true, 'options' => $categories],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true, 'step' => '0.001', 'min' => '0.001', 'icon' => 'cash'],
            ['name' => 'expenseDate', 'label' => 'Date', 'type' => 'date', 'required' => true, 'value' => date('Y-m-d'), 'max' => date('Y-m-d')],
            ['name' => 'description', 'label' => 'Description', 'required' => true, 'maxlength' => 255],
        ],
    ]),
]) ?>
