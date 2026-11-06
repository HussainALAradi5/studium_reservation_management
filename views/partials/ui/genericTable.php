<?php
/**
 * Responsive table: stacks into label/value rows on phones.
 *
 * @var array<string, string> $columns   key => header label (keys match row cells)
 * @var array<int, array<string, string>> $rows  each cell is pre-rendered, already escaped HTML
 * @var array|null $alignRight  column keys aligned right
 * @var array|null $empty       emptyState partial options
 */
$alignRight ??= [];
?>
<?php if ($rows === []): ?>
    <?= partial('ui/emptyState', $empty ?? ['title' => 'Nothing here yet']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--stack">
            <thead>
                <tr>
                    <?php foreach ($columns as $key => $label): ?>
                        <th<?= in_array($key, $alignRight, true) ? ' class="text-right"' : '' ?>><?= e($label) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody data-stagger>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $key => $label): ?>
                            <td data-label="<?= e($label) ?>"<?= in_array($key, $alignRight, true) ? ' class="text-right"' : '' ?>><?= $row[$key] ?? '' ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
