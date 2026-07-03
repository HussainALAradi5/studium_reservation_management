<?php
$btnText = $btnText ?? 'Submit';
$btnId = $btnId ?? 'submit-btn-' . uniqid(); 
$btnVariant = $btnVariant ?? 'solid'; 
$btnSize = $btnSize ?? 'md';

$variants = [
    'solid' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm',
    'outline' => 'border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800',
    'ghost' => 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
];

$padding = ($btnSize === 'md') ? 'px-4 py-2 text-sm' : 'px-3 py-1.5 text-xs';
$baseClasses = "inline-flex items-center justify-center font-semibold rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed";

$classes = "$baseClasses $padding " . ($variants[$btnVariant] ?? $variants['solid']);
?>

<button 
    id="<?php echo htmlspecialchars($btnId); ?>" 
    type="button"
    class="<?php echo htmlspecialchars($classes); ?>">
    <?php echo htmlspecialchars($btnText); ?>
</button>