<?php
$dialogId = $dialogId ?? 'modal-dialog';
$isAlertDialog = $isAlertDialog ?? false;
?>

<dialog id="<?php echo $dialogId; ?>" 
    class="p-0 rounded-xl shadow-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm transition-all duration-300 w-full max-w-lg">
    
    <div class="p-6">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4" id="dialog-title">
            <?php echo $title ?? 'Notification'; ?>
        </h2>
        
        <div class="text-slate-600 dark:text-slate-400 mb-6">
            <?php echo $content ?? ''; ?>
        </div>

        <div class="flex justify-end gap-3">
            <?php if ($isAlertDialog): ?>
                <button onclick="document.getElementById('<?php echo $dialogId; ?>').close()" 
                    class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                    Cancel
                </button>
                <button id="dialog-confirm-btn" 
                    class="px-4 py-2 text-sm font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Confirm Action
                </button>
            <?php else: ?>
                <button onclick="document.getElementById('<?php echo $dialogId; ?>').close()" 
                    class="px-4 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Close
                </button>
            <?php endif; ?>
        </div>
    </div>
</dialog>