<?php

declare(strict_types=1);

/**
 * Production install (run after bin/migrate.php). Safe to run again.
 * Grants default role permissions, adds missing settings, and creates the first admin.
 *
 * Usage: php bin/install.php
 * The admin is read from INSTALL_ADMIN_NAME, INSTALL_ADMIN_USERNAME, INSTALL_ADMIN_EMAIL,
 * INSTALL_ADMIN_PHONE, INSTALL_ADMIN_PASSWORD (environment), or asked for interactively.
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require __DIR__ . '/../config/App/bootstrap.php';

use App\Dtos\Users\UserFormDto;
use App\Exceptions\Common\AppException;
use App\Services\Setup\InstallService;

/** Reads a value from the environment or asks for it (hidden for passwords on Unix terminals). */
function ask(string $label, string $envKey, bool $isSecret = false): string
{
    $fromEnv = getenv($envKey);
    if (is_string($fromEnv) && $fromEnv !== '') {
        return $fromEnv;
    }
    echo $label . ': ';
    $canHide = $isSecret && DIRECTORY_SEPARATOR === '/' && stream_isatty(STDIN);
    if ($canHide) {
        shell_exec('stty -echo');
    }
    $value = trim((string) fgets(STDIN));
    if ($canHide) {
        shell_exec('stty echo');
        echo "\n";
    }
    return $value;
}

$install = new InstallService();
$roles = $install->grantDefaultPermissions();
echo $roles === [] ? "Permissions: already set.\n" : 'Permissions: defaults granted to ' . implode(', ', $roles) . ".\n";
$settings = $install->addMissingSettings();
echo $settings === [] ? "Settings: already set.\n" : 'Settings added: ' . count($settings) . ".\n";

if ($install->hasAdmin()) {
    echo "Admin: an admin already exists, nothing to create.\n";
    exit(0);
}

echo "Create the first admin account.\n";
$password = ask('Password (8+ characters, a letter and a digit)', 'INSTALL_ADMIN_PASSWORD', true);
try {
    $admin = $install->createFirstAdmin(UserFormDto::fromInput([
        'fullName' => ask('Full name', 'INSTALL_ADMIN_NAME'),
        'username' => ask('Username', 'INSTALL_ADMIN_USERNAME'),
        'email' => ask('Email', 'INSTALL_ADMIN_EMAIL'),
        'phone' => ask('Phone (e.g. +97333000000)', 'INSTALL_ADMIN_PHONE'),
        'role' => 'admin',
        'isActive' => '1',
        'password' => $password,
        'passwordConfirmation' => $password,
    ], true));
} catch (AppException $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    foreach ($error->details['fields'] ?? [] as $field => $message) {
        fwrite(STDERR, "  - $field: $message\n");
    }
    exit(1);
}
echo "Admin created: {$admin->username}. Sign in and add cities, managers, and stadiums.\n";
