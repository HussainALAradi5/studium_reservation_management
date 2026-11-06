<?php
/**
 * @var \App\Models\Users\User|null $user
 * @var array<string, string> $roles
 */
use App\Constants\Common\RegexPatterns;
use App\Enums\Users\Gender;
?>
<?= partial('ui/pageHeader', [
    'eyebrow' => $user ? 'Editing ' . $user->code : 'New user',
    'eyebrowIcon' => 'user',
    'title' => $user ? $user->fullName : 'Add a user',
    'subtitle' => 'Managers can be assigned to stadiums after they are created.',
]) ?>
<div class="card" data-animate="fade-up" style="max-width:820px">
    <div class="card__body">
        <?= partial('forms/genericForm', [
            'action' => $user ? "/api/users/{$user->code}" : '/api/users',
            'method' => $user ? 'PUT' : 'POST',
            'columns' => 2,
            'cancelHref' => url('/users'),
            'submitText' => $user ? 'Save changes' : 'Create user',
            'loadingText' => 'Saving…',
            'fields' => [
                ['section' => 'Account'],
                ['name' => 'fullName', 'label' => 'Full name', 'required' => true, 'value' => $user?->fullName, 'icon' => 'user', 'full' => true],
                ['name' => 'username', 'label' => 'Username', 'required' => true, 'value' => $user?->username, 'pattern' => RegexPatterns::USERNAME],
                ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'required' => true, 'options' => $roles, 'value' => $user?->role->value ?? 'customer', 'icon' => 'shield'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $user?->email],
                ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true, 'value' => $user?->phone, 'pattern' => RegexPatterns::PHONE, 'icon' => 'phone'],
                ['name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'options' => Gender::options(), 'emptyOption' => 'Prefer not to say', 'value' => $user?->gender?->value],
                ['name' => 'birthDate', 'label' => 'Birth date', 'type' => 'date', 'value' => $user?->birthDate, 'max' => date('Y-m-d')],
                ['name' => 'isActive', 'label' => 'Active account', 'type' => 'switch', 'value' => $user?->isActive ?? true, 'full' => true],
                ['section' => $user ? 'Reset password (optional)' : 'Password'],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => $user === null, 'autocomplete' => 'new-password', 'hint' => 'At least 8 characters with a letter and a digit.'],
                ['name' => 'passwordConfirmation', 'label' => 'Confirm password', 'type' => 'password', 'required' => $user === null, 'autocomplete' => 'new-password'],
            ],
        ]) ?>
    </div>
</div>
