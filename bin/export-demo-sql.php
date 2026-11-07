<?php

declare(strict_types=1);

/**
 * Builds deploy/studium-demo-data.sql: the demo cities and stadiums (prices, hours, map pins) for a
 * live database, imported with phpMyAdmin. Safe to import again: existing cities and stadiums
 * (same name) are skipped. No demo user accounts are exported; with --with-manager it asks for a
 * real manager account (strong password required) and assigns the demo stadiums to it.
 *
 * Usage: php bin/export-demo-sql.php [--with-manager]
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require __DIR__ . '/../config/App/bootstrap.php';

use App\Constants\Common\CodePrefixes;
use App\Constants\Stadiums\StadiumRules;
use App\Dtos\Users\UserFormDto;
use App\Enums\Users\UserRole;
use App\Exceptions\Common\AppException;
use App\Seeds\Regional\CitiesSeed;
use App\Seeds\Stadiums\StadiumsSeed;
use App\Utilities\Codes\CodeGenerator;
use App\Utilities\Security\PasswordHasher;

const OUTPUT_FILE = BASE_DIR . '/deploy/studium-demo-data.sql';

function sqlValue(mixed $value): string
{
    return match (true) {
        $value === null => 'NULL',
        is_int($value), is_float($value) => (string) $value,
        default => "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $value) . "'",
    };
}

function ask(string $label): string
{
    echo $label . ': ';
    return trim((string) fgets(STDIN));
}

$country = StadiumRules::DEFAULT_COUNTRY_CODE;
$sql = ['-- Studium demo data generated ' . date('Y-m-d H:i:s'), 'SET NAMES utf8mb4;'];
$managerLookup = 'NULL';

if (in_array('--with-manager', $argv, true)) {
    echo "Demo manager account (a real login on the live site: use a strong, unique password).\n";
    $password = ask('Password (8+ characters, a letter and a digit)');
    try {
        $manager = UserFormDto::fromInput([
            'fullName' => ask('Full name'),
            'username' => ask('Username'),
            'email' => ask('Email'),
            'phone' => ask('Phone (e.g. +97333000000)'),
            'role' => UserRole::MANAGER->value,
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
    $sql[] = "\n-- Manager (skipped if the username, email, or phone already exists)";
    $sql[] = sprintf(
        "INSERT INTO users (code, role, full_name, username, email, phone, password_hash, is_active, created_at, updated_at)\n"
        . "SELECT %s, %s, %s, %s, %s, %s, %s, 1, NOW(), NOW() FROM DUAL\n"
        . "WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = %s OR email = %s OR phone = %s);",
        sqlValue(CodeGenerator::generate(CodePrefixes::USER)),
        sqlValue(UserRole::MANAGER->value),
        sqlValue($manager->fullName),
        sqlValue($manager->username),
        sqlValue($manager->email),
        sqlValue($manager->phone),
        sqlValue(PasswordHasher::hash((string) $manager->password)),
        sqlValue($manager->username),
        sqlValue($manager->email),
        sqlValue($manager->phone)
    );
    $managerLookup = sprintf("(SELECT id FROM users WHERE username = %s AND role = 'manager' LIMIT 1)", sqlValue($manager->username));
}

$sql[] = "\n-- Cities";
foreach (CitiesSeed::CITIES as $city) {
    $sql[] = sprintf(
        "INSERT INTO cities (code, name, country_code, is_active, created_at, updated_at)\n"
        . "SELECT %s, %s, %s, 1, NOW(), NOW() FROM DUAL\n"
        . "WHERE NOT EXISTS (SELECT 1 FROM cities WHERE name = %s AND country_code = %s AND deleted_at IS NULL);",
        sqlValue(CodeGenerator::generate(CodePrefixes::CITY)),
        sqlValue($city),
        sqlValue($country),
        sqlValue($city),
        sqlValue($country)
    );
}

$sql[] = "\n-- Stadiums with their addresses (skipped when a stadium with the same name exists)";
foreach (StadiumsSeed::STADIUMS as [$city, $name, $sport, $surface, $capacity, $hourly, $daily, $open, $close, $slot, $street, $block, $area, $lat, $lng]) {
    $addressCode = CodeGenerator::generate(CodePrefixes::ADDRESS);
    $notExists = sprintf('NOT EXISTS (SELECT 1 FROM stadiums WHERE name = %s AND deleted_at IS NULL)', sqlValue($name));
    $sql[] = sprintf(
        "INSERT INTO addresses (code, city_id, street, block, area, latitude, longitude, created_at, updated_at)\n"
        . "SELECT %s, c.id, %s, %s, %s, %s, %s, NOW(), NOW() FROM cities c\n"
        . "WHERE c.name = %s AND c.country_code = %s AND c.deleted_at IS NULL AND %s LIMIT 1;",
        sqlValue($addressCode),
        sqlValue($street),
        sqlValue($block),
        sqlValue($area),
        sqlValue($lat),
        sqlValue($lng),
        sqlValue($city),
        sqlValue($country),
        $notExists
    );
    $sql[] = sprintf(
        "INSERT INTO stadiums (code, manager_id, address_id, name, description, sport_type, surface_type, capacity, price_per_hour, price_per_day, opening_time, closing_time, slot_minutes, is_active, created_at, updated_at)\n"
        . "SELECT %s, %s, a.id, %s, %s, %s, %s, %d, %s, %s, %s, %s, %d, 1, NOW(), NOW() FROM addresses a\n"
        . "WHERE a.code = %s AND %s;",
        sqlValue(CodeGenerator::generate(CodePrefixes::STADIUM)),
        $managerLookup,
        sqlValue($name),
        sqlValue("Well-kept $sport venue with lighting, changing rooms, and parking."),
        sqlValue($sport),
        sqlValue($surface),
        $capacity,
        sqlValue($hourly),
        sqlValue($daily),
        sqlValue($open),
        sqlValue($close),
        $slot,
        sqlValue($addressCode),
        $notExists
    );
}

if (!is_dir(dirname(OUTPUT_FILE))) {
    mkdir(dirname(OUTPUT_FILE), 0775, true);
}
file_put_contents(OUTPUT_FILE, implode("\n", $sql) . "\n");
printf("Written: deploy/studium-demo-data.sql (%d cities, %d stadiums%s).\n", count(CitiesSeed::CITIES), count(StadiumsSeed::STADIUMS), $managerLookup === 'NULL' ? '' : ', 1 manager');
echo "Import it into the live database with phpMyAdmin (Import tab). Safe to import again.\n";
