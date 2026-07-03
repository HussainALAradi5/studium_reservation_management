<?php
$fields = $fields ?? [];

?>

<form class="space-y-6 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm transition-colors duration-300">
    <?php foreach ($fields as $field): ?>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                <?php echo htmlspecialchars($field['label']); ?>
            </label>
            <input 
                type="<?php echo $field['type'] ?? 'text'; ?>" 
                name="<?php echo $field['name']; ?>"
                class="w-full px-4 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>">
        </div>
    <?php endforeach; ?>
    
    <div class="flex justify-end">
        <?php 
            $btnText = 'Save Changes';
            $btnId = 'submit-btn-form';
            $btnVariant = 'btn-solid';
            include 'customButton.php'; 
        ?>
    </div>
</form>