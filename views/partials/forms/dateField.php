<?php
/**
 * dateField — wheel picker (iOS or Android style, the user's choice is remembered) for date, time,
 * month, or datetime values. The real value lives in a hidden input so forms submit ISO strings:
 * date "2026-11-07", time "18:30", month "2026-11", datetime "2026-11-07 18:30".
 *
 * @var array $field { name, id, mode, label?, value?, required?, hint?, full?, min?, max?, minuteStep?, placeholder?, attrs? }
 */
$mode = $field['mode'] ?? 'date';
$id = $field['id'];
$required = !empty($field['required']);
$icons = ['date' => 'calendar', 'month' => 'calendar', 'time' => 'clock', 'datetime' => 'calendar'];
$placeholders = ['date' => 'Select a date', 'month' => 'Select a month', 'time' => 'Select a time', 'datetime' => 'Select date & time'];
$classes = 'field' . (!empty($field['full']) ? ' field--full' : '') . (!empty($field['class']) ? ' ' . $field['class'] : '');
?>
<div class="<?= e($classes) ?>" data-field="<?= e($field['name']) ?>">
    <?php if (!empty($field['label'])): ?>
        <label class="field__label" for="<?= e($id) ?>"><?= e($field['label']) ?><?= $required ? '<span class="req" aria-hidden="true">*</span>' : '' ?></label>
    <?php endif; ?>
    <div class="field__control" data-wheel-picker
         data-mode="<?= e($mode) ?>"
         data-title="<?= e($field['label'] ?? $placeholders[$mode]) ?>"
         <?= attrs([
             'data-min' => $field['min'] ?? null,
             'data-max' => $field['max'] ?? null,
             'data-minute-step' => $field['minuteStep'] ?? null,
         ]) ?>>
        <span class="field__icon"><?= icon($icons[$mode]) ?></span>
        <input type="hidden" name="<?= e($field['name']) ?>" value="<?= e($field['value'] ?? '') ?>" data-picker-value<?= attrs($field['attrs'] ?? []) ?>>
        <button type="button" class="picker-trigger" id="<?= e($id) ?>" aria-haspopup="dialog" aria-expanded="false"
                data-placeholder="<?= e($field['placeholder'] ?? $placeholders[$mode]) ?>" aria-describedby="<?= e($id) ?>-error">
            <?= e($field['placeholder'] ?? $placeholders[$mode]) ?>
        </button>
    </div>
    <?php if (!empty($field['hint'])): ?><div class="field__hint"><?= e($field['hint']) ?></div><?php endif; ?>
    <div class="field__error" id="<?= e($id) ?>-error" data-error-for="<?= e($field['name']) ?>" role="alert"></div>
</div>
