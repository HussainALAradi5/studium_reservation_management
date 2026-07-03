<?php

$columns = $columns ?? [];
$data = $data ?? [];
?>


<div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm transition-colors duration-300">
    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
        <thead class="bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 uppercase font-semibold">
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th class="px-6 py-4"><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-950">
            <?php foreach ($data as $row): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">
                    <?php foreach ($row as $cell): ?>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($cell); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>