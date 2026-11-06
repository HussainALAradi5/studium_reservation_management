<?php
/**
 * @var \App\Enums\Settings\SettingsKey[] $keys
 * @var array<string, int> $values
 * @var \App\Enums\Users\Permission[] $permissions
 * @var \App\Enums\Users\UserRole[] $roles
 * @var array<string, string[]> $matrix
 */
use App\Enums\Users\UserRole;

$settingFields = [];
foreach ($keys as $key) {
    [$min, $max] = $key->range();
    $settingFields[] = $key->isBoolean()
        ? ['name' => $key->fieldName(), 'label' => $key->label(), 'type' => 'switch', 'value' => $values[$key->value] === 1, 'hint' => $key->hint(), 'full' => true]
        : ['name' => $key->fieldName(), 'label' => $key->label(), 'type' => 'number', 'required' => true, 'min' => $min, 'max' => $max, 'value' => $values[$key->value], 'hint' => $key->hint() . " ($min–$max)"];
}
$roleTones = ['admin' => 'danger', 'manager' => 'info', 'customer' => 'success'];
?>
<?= partial('ui/pageHeader', [
    'title' => 'Settings & roles',
    'subtitle' => 'Booking rules for the whole system and what each role is allowed to do.',
]) ?>
<div class="stack-lg">
    <?= partial('ui/genericCard', [
        'title' => 'Booking rules',
        'subtitle' => 'Stored as settings keys; changes apply to new bookings immediately.',
        'icon' => 'settings',
        'animate' => 'fade-up',
        'body' => partial('forms/genericForm', [
            'action' => '/api/settings',
            'method' => 'PUT',
            'onSuccess' => 'none',
            'columns' => 2,
            'fields' => $settingFields,
            'submitText' => 'Save settings',
            'loadingText' => 'Saving…',
        ]),
    ]) ?>

    <section>
        <div class="row-between" style="margin-bottom:14px">
            <div>
                <h2 style="margin:0">Roles & permissions</h2>
                <p class="muted" style="margin:4px 0 0">Endpoints check these permissions; a role without one gets “Access denied”.</p>
            </div>
        </div>
        <div class="matrix" data-stagger>
            <?php foreach ($roles as $role): ?>
                <?php $isAdmin = $role === UserRole::ADMIN; ?>
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title"><?= badge($role->label(), $roleTones[$role->value], ['icon' => 'shield', 'large' => true]) ?></h3>
                        <span class="small muted"><?= count($matrix[$role->value]) ?>/<?= count($permissions) ?></span>
                    </div>
                    <div class="card__body">
                        <form class="stack" data-ajax-form action="<?= e(url('/api/settings/permissions/' . $role->value)) ?>" data-method="PUT" data-on-success="none">
                            <div class="matrix__list">
                                <?php foreach ($permissions as $permission): ?>
                                    <label class="check">
                                        <input type="checkbox" name="permissions[]" value="<?= e($permission->value) ?>"
                                            <?= in_array($permission->value, $matrix[$role->value], true) ? 'checked' : '' ?>
                                            <?= $isAdmin ? 'disabled' : '' ?>>
                                        <span><?= e($permission->label()) ?><br><span class="mono soft"><?= e($permission->value) ?></span></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($isAdmin): ?>
                                <div class="alert tone-info small"><?= icon('info') ?><div>Admins always keep every permission so nobody gets locked out.</div></div>
                            <?php else: ?>
                                <div class="form__actions"><?= button('Save ' . $role->label() . ' permissions', ['type' => 'submit', 'size' => 'sm', 'icon' => 'check', 'loadingText' => 'Saving…']) ?></div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
