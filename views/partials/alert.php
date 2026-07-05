<?php
use App\Enums\UI\AlertMessageType;

// Default values to prevent undefined variable errors
$alertType = $alertType ?? AlertMessageType::INFO;
$alertTitle = $alertTitle ?? null;
$alertMessage = $alertMessage ?? '';
$alertDismissible = $alertDismissible ?? true;
?>

<!-- Alert Container -->
<div class="flex items-start p-4 mb-4 border rounded-lg shadow-sm transition-all duration-300 <?php echo $alertType->getThemeClasses(); ?>" role="alert" data-alert-box>
    
    <!-- Icon -->
    <div class="flex-shrink-0 mt-0.5 <?php echo $alertType->getIconClasses(); ?>">
        <?php echo $alertType->getIconSvg(); ?>
    </div>
    
    <!-- Content -->
    <div class="ml-3 flex-1">
        <?php if ($alertTitle): ?>
            <h3 class="text-sm font-semibold mb-1"><?php echo htmlspecialchars($alertTitle); ?></h3>
        <?php endif; ?>
        <div class="text-sm opacity-90">
            <?php echo htmlspecialchars($alertMessage); ?>
        </div>
    </div>
    
    <!-- Close Button -->
    <?php if ($alertDismissible): ?>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex h-8 w-8 focus:ring-2 focus:ring-current opacity-60 hover:opacity-100 transition-opacity" aria-label="Close" data-dismiss-btn>
            <span class="sr-only">Dismiss</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    <?php endif; ?>
</div>