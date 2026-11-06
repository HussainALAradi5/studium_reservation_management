<?php

declare(strict_types=1);

/**
 * Runs pending SQL migrations in filename order and records them in schema_migrations.
 * Usage: C:\xampp\php\php.exe bin/migrate.php
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require __DIR__ . '/../config/App/bootstrap.php';

use App\Config\Database\Database;

$server = Database::connect(false);
$name = Database::name();
$server->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$pdo = Database::connection();
$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(190) PRIMARY KEY,
    applied_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$applied = array_flip($pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN));
$files = glob(BASE_DIR . '/migrations/*.sql');
sort($files);

$count = 0;
foreach ($files as $file) {
    $version = basename($file, '.sql');
    if (isset($applied[$version])) {
        continue;
    }
    $statements = array_filter(array_map('trim', explode(';', (string) file_get_contents($file))));
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
    $pdo->prepare('INSERT INTO schema_migrations (version, applied_at) VALUES (?, NOW())')->execute([$version]);
    echo "Migrated: $version\n";
    $count++;
}

echo $count === 0 ? "Nothing to migrate.\n" : "Done: $count migration(s).\n";
