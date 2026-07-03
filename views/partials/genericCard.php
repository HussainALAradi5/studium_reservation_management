<?php
$cardTitle = $cardTitle ?? null;
$cardBody = $cardBody ?? '';
$cardFooter = $cardFooter ?? null;
?>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm transition-colors duration-300 overflow-hidden">
    <?php if ($cardTitle): ?>
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-semibold text-slate-900 dark:text-white">
            <?php echo htmlspecialchars($cardTitle); ?>
        </div>
    <?php endif; ?>
    
    <div class="p-6 text-slate-600 dark:text-slate-300">
        <?php echo $cardBody; ?>
    </div>

    <?php if ($cardFooter): ?>
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800">
            <?php echo $cardFooter; ?>
        </div>
    <?php endif; ?>
</div>