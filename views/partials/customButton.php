<?php
$btnText = $btnText ?? 'Submit';
$btnId = $btnId ?? 'submit-btn-' . uniqid(); 
?>

<button 
    id="<?php echo htmlspecialchars($btnId); ?>" 
    type="button"
    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 dark:disabled:bg-slate-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg disabled:cursor-not-allowed">
    <?php echo htmlspecialchars($btnText); ?>
</button>