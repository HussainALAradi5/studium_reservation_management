<?php
/**
 * @var \App\Dtos\Common\PageResult $page
 * @var string $path     current page path
 * @var array  $filters  current query (kept on every link)
 */
$total = $page->totalPages();
$link = static fn (int $number) => url($path, [...$filters, 'page' => $number]);
$from = max(1, $page->page - 2);
$to = min($total, $page->page + 2);
$first = $page->totalItems === 0 ? 0 : ($page->page - 1) * $page->pageSize + 1;
$last = min($page->totalItems, $page->page * $page->pageSize);
?>
<?php if ($page->totalItems > 0): ?>
<nav class="pagination" aria-label="Pagination">
    <span class="muted small">Showing <?= $first ?>–<?= $last ?> of <?= $page->totalItems ?></span>
    <?php if ($total > 1): ?>
        <div class="pagination__pages">
            <a class="pagination__link<?= $page->page <= 1 ? ' is-disabled' : '' ?>" href="<?= e($link($page->page - 1)) ?>" aria-label="Previous"><?= icon('chevron-left') ?></a>
            <?php for ($number = $from; $number <= $to; $number++): ?>
                <a class="pagination__link<?= $number === $page->page ? ' is-active' : '' ?>" href="<?= e($link($number)) ?>"<?= $number === $page->page ? ' aria-current="page"' : '' ?>><?= $number ?></a>
            <?php endfor; ?>
            <a class="pagination__link<?= $page->page >= $total ? ' is-disabled' : '' ?>" href="<?= e($link($page->page + 1)) ?>" aria-label="Next"><?= icon('chevron-right') ?></a>
        </div>
    <?php endif; ?>
</nav>
<?php endif; ?>
