<?php

$fields = $fields ?? [];
$formAction = $formAction ?? ''; 
?>

<form action="<?php echo htmlspecialchars($formAction); ?>" method="POST" class="space-y-6 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm transition-colors duration-300">
    <?php foreach ($fields as $field): 
        $type = $field['type'] ?? 'text';
        $isToggle = in_array($type, ['checkbox', 'switch']);
    ?>
        <div>
            <?php if (!$isToggle): ?>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <?php echo htmlspecialchars($field['label']); ?>
                </label>
            <?php endif; ?>

            <?php if ($type === 'select'): ?>
                <div class="relative" data-custom-select>
                    
                    <input type="text" 
                           name="<?php echo htmlspecialchars($field['name']); ?>" 
                           value="" 
                           data-select-input 
                           class="absolute bottom-0 left-1/2 opacity-0 w-0 h-0 pointer-events-none z-[-1]" 
                           tabindex="-1" 
                           <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                    
                    <button type="button" data-select-toggle 
                            class="w-full px-4 py-2 text-left rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all flex justify-between items-center outline-none">
                        <span data-select-label class="text-slate-400">
                            <?php echo htmlspecialchars($field['placeholder'] ?? 'Select an option'); ?>
                        </span>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" data-select-icon fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <ul data-select-dropdown class="hidden absolute z-10 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md shadow-lg max-h-60 overflow-y-auto transform opacity-0 scale-95 transition-all duration-200 origin-top">
                        <?php foreach (($field['options'] ?? []) as $option): ?>
                            <li data-value="<?php echo htmlspecialchars($option['value']); ?>" 
                                class="px-4 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                                <?php echo htmlspecialchars($option['label']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php elseif ($type === 'checkbox'): ?>
                <label class="flex items-center cursor-pointer group w-max" data-custom-toggle tabindex="0">
                    <div class="relative flex items-center justify-center w-5 h-5 mr-3 border-2 rounded-md border-slate-300 dark:border-slate-600 group-hover:border-blue-500 transition-colors">
                        <input type="checkbox" 
                               name="<?php echo htmlspecialchars($field['name']); ?>" 
                               value="<?php echo htmlspecialchars($field['value'] ?? '1'); ?>" 
                               class="peer sr-only"
                               <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                        
                        <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity z-10 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        
                        <div class="absolute inset-0 bg-blue-600 rounded-sm opacity-0 peer-checked:opacity-100 transition-opacity z-0"></div>
                    </div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300 select-none">
                        <?php echo htmlspecialchars($field['label']); ?>
                    </span>
                </label>

            <?php elseif ($type === 'switch'): ?>
                <label class="flex items-center cursor-pointer group w-max" data-custom-toggle tabindex="0">
                    <div class="relative inline-flex items-center">
                        <input type="checkbox" 
                               name="<?php echo htmlspecialchars($field['name']); ?>" 
                               value="<?php echo htmlspecialchars($field['value'] ?? '1'); ?>" 
                               class="peer sr-only"
                               <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                        
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600 transition-colors duration-300"></div>
                    </div>
                    <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300 select-none">
                        <?php echo htmlspecialchars($field['label']); ?>
                    </span>
                </label>

            <?php else: ?>
                <input type="<?php echo htmlspecialchars($type); ?>" 
                       name="<?php echo htmlspecialchars($field['name']); ?>" 
                       class="w-full px-4 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                       placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>" 
                       <?php echo !empty($field['required']) ? 'required' : ''; ?>>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="flex justify-end mt-8">
        <?php
        $btnText = 'Submit';
        $btnId = 'submit-btn-form';
        $btnVariant = 'solid';
        $btnType = 'submit';
        include 'customButton.php';
        ?>
    </div>
</form>