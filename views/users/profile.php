<?php
/** @var \App\Models\Users\User $user */
use App\Constants\Common\RegexPatterns;
use App\Enums\Users\Gender;
?>
<?= partial('ui/pageHeader', [
    'title' => 'My profile',
    'subtitle' => 'Keep your contact details up to date so stadiums can reach you.',
]) ?>
<div class="grid grid-2">
    <?= partial('ui/genericCard', [
        'title' => 'Personal details',
        'icon' => 'user',
        'animate' => 'fade-up',
        'actions' => badge($user->role->label(), 'primary', ['icon' => 'shield']),
        'body' => partial('forms/genericForm', [
            'action' => '/api/profile',
            'method' => 'PUT',
            'onSuccess' => 'reload',
            'columns' => 2,
            'submitText' => 'Save profile',
            'loadingText' => 'Saving…',
            'fields' => [
                ['name' => 'fullName', 'label' => 'Full name', 'required' => true, 'value' => $user->fullName, 'full' => true, 'icon' => 'user'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $user->email],
                ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true, 'value' => $user->phone, 'pattern' => RegexPatterns::PHONE],
                ['name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'options' => Gender::options(), 'emptyOption' => 'Prefer not to say', 'value' => $user->gender?->value],
                ['name' => 'birthDate', 'label' => 'Birth date', 'type' => 'date', 'value' => $user->birthDate, 'max' => date('Y-m-d')],
            ],
        ]),
    ]) ?>
    <?= partial('ui/genericCard', [
        'title' => 'Change password',
        'icon' => 'key',
        'animate' => 'fade-up',
        'body' => partial('forms/genericForm', [
            'action' => '/api/profile/password',
            'method' => 'PUT',
            'onSuccess' => 'reset',
            'submitText' => 'Update password',
            'submitIcon' => 'shield',
            'loadingText' => 'Updating…',
            'fields' => [
                ['name' => 'currentPassword', 'label' => 'Current password', 'type' => 'password', 'required' => true, 'autocomplete' => 'current-password'],
                ['name' => 'newPassword', 'label' => 'New password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password', 'hint' => 'At least 8 characters with a letter and a digit.'],
                ['name' => 'newPasswordConfirmation', 'label' => 'Confirm new password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password'],
            ],
        ]),
    ]) ?>
</div>
