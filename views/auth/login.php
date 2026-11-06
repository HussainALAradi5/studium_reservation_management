<?php
/** @var string $next */
?>
<h1 style="margin-bottom:4px">Welcome back</h1>
<p class="muted">Sign in to book and manage stadiums.</p>
<?= partial('forms/genericForm', [
    'action' => '/api/auth/login',
    'submitText' => 'Sign in',
    'submitIcon' => 'login',
    'loadingText' => 'Signing in…',
    'blockSubmit' => true,
    'fields' => [
        ['name' => 'identifier', 'label' => 'Username or email', 'icon' => 'user', 'required' => true, 'autocomplete' => 'username', 'attrs' => ['autofocus' => true]],
        ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'icon' => 'key', 'required' => true, 'autocomplete' => 'current-password'],
        ['name' => 'next', 'type' => 'hidden', 'value' => $next],
    ],
]) ?>
<p class="auth__switch">New here? <a href="<?= e(url('/register')) ?>">Create an account</a></p>
