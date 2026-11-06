<?php
/**
 * genericField — renders one form field from a definition array.
 *
 * @var array $field {
 *   name: string, label?: string, type?: text|email|tel|password|number|select|textarea|switch|hidden|date|time|month|datetime,
 *   value?: mixed, required?: bool, placeholder?: string, hint?: string, icon?: string, full?: bool,
 *   options?: array<string,string>, emptyOption?: string, pattern?: string (PHP regex from RegexPatterns),
 *   minlength?: int, maxlength?: int, min?: mixed, max?: mixed, step?: mixed, autocomplete?: string,
 *   inputmode?: string, readonly?: bool, disabled?: bool, attrs?: array
 * }
 * Date/time types render the wheel picker (dateField.php). Errors are filled in by ajax-form.js
 * into [data-error-for="name"].
 */
use App\Constants\Common\RegexPatterns;

$type = $field['type'] ?? 'text';
$name = $field['name'];
$id = $field['id'] ?? 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', $name);
$value = $field['value'] ?? '';
$required = !empty($field['required']);
$classes = 'field' . (!empty($field['full']) ? ' field--full' : '') . (!empty($field['class']) ? ' ' . $field['class'] : '');

if ($type === 'hidden') {
    echo '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '"' . attrs($field['attrs'] ?? []) . '>';
    return;
}
if (in_array($type, ['date', 'time', 'month', 'datetime'], true)) {
    echo partial('forms/dateField', ['field' => [...$field, 'id' => $id, 'mode' => $type]]);
    return;
}

$common = [
    'id' => $id,
    'name' => $name,
    'required' => $required,
    'readonly' => !empty($field['readonly']),
    'disabled' => !empty($field['disabled']),
    'placeholder' => $field['placeholder'] ?? null,
    'autocomplete' => $field['autocomplete'] ?? null,
    'aria-describedby' => $id . '-error',
    ...($field['attrs'] ?? []),
];
?>
<div class="<?= e($classes) ?>" data-field="<?= e($name) ?>">
    <?php if ($type === 'switch'): ?>
        <input type="hidden" name="<?= e($name) ?>" value="0">
        <label class="switch">
            <input type="checkbox" value="1"<?= attrs([...$common, 'placeholder' => null, 'aria-describedby' => null]) ?><?= !empty($value) ? ' checked' : '' ?>>
            <span class="switch__track" aria-hidden="true"></span>
            <span><?= e($field['label'] ?? '') ?></span>
        </label>
    <?php else: ?>
        <?php if (!empty($field['label'])): ?>
            <label class="field__label" for="<?= e($id) ?>"><?= e($field['label']) ?><?= $required ? '<span class="req" aria-hidden="true">*</span>' : '' ?></label>
        <?php endif; ?>
        <div class="field__control">
            <?php if (!empty($field['icon'])): ?><span class="field__icon"><?= icon($field['icon']) ?></span><?php endif; ?>
            <?php if ($type === 'select'): ?>
                <select class="select"<?= attrs([...$common, 'placeholder' => null]) ?>>
                    <?php if (array_key_exists('emptyOption', $field)): ?>
                        <option value=""><?= e($field['emptyOption']) ?></option>
                    <?php endif; ?>
                    <?php foreach ($field['options'] ?? [] as $optionValue => $optionLabel): ?>
                        <option value="<?= e($optionValue) ?>"<?= (string) $optionValue === (string) $value ? ' selected' : '' ?>><?= e($optionLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($type === 'textarea'): ?>
                <textarea class="textarea"<?= attrs([...$common, 'maxlength' => $field['maxlength'] ?? null]) ?>><?= e($value) ?></textarea>
            <?php else: ?>
                <input class="input" type="<?= e($type) ?>" value="<?= e($value) ?>"<?= attrs([
                    ...$common,
                    'pattern' => !empty($field['pattern']) ? RegexPatterns::html($field['pattern']) : null,
                    'minlength' => $field['minlength'] ?? null,
                    'maxlength' => $field['maxlength'] ?? null,
                    'min' => $field['min'] ?? null,
                    'max' => $field['max'] ?? null,
                    'step' => $field['step'] ?? null,
                    'inputmode' => $field['inputmode'] ?? null,
                ]) ?>>
                <?php if ($type === 'password'): ?>
                    <button type="button" class="input-addon" data-toggle-password aria-label="Show password"><?= icon('eye') ?></button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($field['hint'])): ?><div class="field__hint"><?= e($field['hint']) ?></div><?php endif; ?>
    <div class="field__error" id="<?= e($id) ?>-error" data-error-for="<?= e($name) ?>" role="alert"></div>
</div>
