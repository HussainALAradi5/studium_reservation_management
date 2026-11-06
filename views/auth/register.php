<?php
use App\Constants\Common\RegexPatterns;
use App\Constants\Users\AuthRules;
?>
<h1 style="margin-bottom:4px">Create your account</h1>
<p class="muted">Book stadiums by the hour or for full days.</p>
<?= partial('forms/genericForm', [
    'action' => '/api/auth/register',
    'submitText' => 'Create account',
    'submitIcon' => 'sparkles',
    'loadingText' => 'Creating…',
    'blockSubmit' => true,
    'columns' => 2,
    'fields' => [
        ['name' => 'fullName', 'label' => 'Full name', 'icon' => 'user', 'required' => true, 'full' => true, 'autocomplete' => 'name', 'maxlength' => 120],
        ['name' => 'username', 'label' => 'Username', 'required' => true, 'autocomplete' => 'username', 'pattern' => RegexPatterns::USERNAME, 'hint' => 'Letters, digits, dot, underscore.'],
        ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'icon' => 'phone', 'required' => true, 'autocomplete' => 'tel', 'pattern' => RegexPatterns::PHONE, 'placeholder' => '+97333123456', 'inputmode' => 'tel'],
        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'full' => true, 'autocomplete' => 'email'],
        ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password', 'minlength' => AuthRules::PASSWORD_MIN_LENGTH, 'hint' => 'At least 8 characters with a letter and a digit.'],
        ['name' => 'passwordConfirmation', 'label' => 'Confirm password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password'],
    ],
]) ?>
<p class="auth__switch">Already have an account? <a href="<?= e(url('/login')) ?>">Sign in</a></p>
