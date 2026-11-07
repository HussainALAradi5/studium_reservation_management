<?php

declare(strict_types=1);

/**
 * Builds deploy/studium-install.sql for hosts without a command line (e.g. InfinityFree):
 * every migration in order, the schema_migrations records, default role permissions, and default
 * settings. With --with-admin it also asks for the first admin and adds it with a bcrypt hash.
 * Import the file into an empty database with phpMyAdmin. The deploy/ folder is git-ignored.
 *
 * Usage: php bin/export-sql.php [--with-admin]
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require __DIR__ . '/../config/App/bootstrap.php';

use App\Constants\Common\CodePrefixes;
use App\Dtos\Users\UserFormDto;
use App\Enums\Settings\SettingsKey;
use App\Enums\Users\Permission;
use App\Enums\Users\UserRole;
use App\Exceptions\Common\AppException;
use App\Utilities\Codes\CodeGenerator;
use App\Utilities\Security\PasswordHasher;

const OUTPUT_DIR = BASE_DIR . '/deploy';
const OUTPUT_FILE = OUTPUT_DIR . '/studium-install.sql';

function quote(?string $value): string
{
    return $value === null ? 'NULL' : "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
}

function prompt(string $label): string
{
    echo $label . ': ';
    return trim((string) fgets(STDIN));
}

$now = date('Y-m-d H:i:s');
$sql = ["-- Studium install script generated " . $now, 'SET NAMES utf8mb4;', 'SET FOREIGN_KEY_CHECKS = 1;', ''];

$sql[] = 'CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(190) PRIMARY KEY, applied_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
$files = glob(BASE_DIR . '/migrations/*.sql');
sort($files);
foreach ($files as $file) {
    $version = basename($file, '.sql');
    $sql[] = "\n-- Migration $version";
    $sql[] = rtrim((string) file_get_contents($file));
    $sql[] = 'INSERT INTO schema_migrations (version, applied_at) VALUES (' . quote($version) . ', ' . quote($now) . ');';
}

$sql[] = "\n-- Default role permissions (admins always hold every permission)";
foreach ([UserRole::MANAGER, UserRole::CUSTOMER] as $role) {
    foreach (Permission::defaultsFor($role) as $permission) {
        $sql[] = sprintf(
            'INSERT IGNORE INTO role_permissions (code, role, permission, created_at, updated_at) VALUES (%s, %s, %s, %s, %s);',
            quote(CodeGenerator::generate(CodePrefixes::ROLE_PERMISSION)),
            quote($role->value),
            quote($permission->value),
            quote($now),
            quote($now)
        );
    }
}

$sql[] = "\n-- Default settings";
foreach (SettingsKey::cases() as $key) {
    $sql[] = sprintf(
        'INSERT IGNORE INTO settings (code, setting_key, setting_value, created_at, updated_at) VALUES (%s, %s, %s, %s, %s);',
        quote(CodeGenerator::generate(CodePrefixes::SETTING)),
        quote($key->value),
        quote((string) $key->defaultValue()),
        quote($now),
        quote($now)
    );
}

if (in_array('--with-admin', $argv, true)) {
    echo "First admin account (validated with the same rules as the app).\n";
    $password = prompt('Password (8+ characters, a letter and a digit)');
    try {
        $admin = UserFormDto::fromInput([
            'fullName' => prompt('Full name'),
            'username' => prompt('Username'),
            'email' => prompt('Email'),
            'phone' => prompt('Phone (e.g. +97333000000)'),
            'role' => UserRole::ADMIN->value,
            'isActive' => '1',
            'password' => $password,
            'passwordConfirmation' => $password,
        ], true);
    } catch (AppException $error) {
        fwrite(STDERR, $error->getMessage() . "\n");
        foreach ($error->details['fields'] ?? [] as $field => $message) {
            fwrite(STDERR, "  - $field: $message\n");
        }
        exit(1);
    }
    $sql[] = "\n-- First admin";
    $sql[] = sprintf(
        'INSERT INTO users (code, role, full_name, username, email, phone, password_hash, is_active, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s, %s, 1, %s, %s);',
        quote(CodeGenerator::generate(CodePrefixes::USER)),
        quote(UserRole::ADMIN->value),
        quote($admin->fullName),
        quote($admin->username),
        quote($admin->email),
        quote($admin->phone),
        quote(PasswordHasher::hash((string) $admin->password)),
        quote($now),
        quote($now)
    );
}

if (!is_dir(OUTPUT_DIR)) {
    mkdir(OUTPUT_DIR, 0775, true);
}
file_put_contents(OUTPUT_FILE, implode("\n", $sql) . "\n");
echo 'Written: deploy/studium-install.sql (' . count($files) . " migrations). Import it into an empty database with phpMyAdmin.\n";
echo "The file is git-ignored; delete it after importing if it contains the admin.\n";
