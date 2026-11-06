<?php

declare(strict_types=1);

/**
 * Development seed: default permissions and settings, one user per role, cities, and sample stadiums.
 * Refuses to run in production. Usage: C:\xampp\php\php.exe bin/seed.php
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require __DIR__ . '/../config/App/bootstrap.php';

use App\Config\App\AppConfig;
use App\Config\App\Env;
use App\Seeds\Regional\CitiesSeed;
use App\Seeds\Settings\SettingsSeed;
use App\Seeds\Stadiums\StadiumsSeed;
use App\Seeds\Users\RolePermissionsSeed;
use App\Seeds\Users\UsersSeed;

if (AppConfig::isProduction()) {
    fwrite(STDERR, "Refusing to seed: APP_ENV is production.\n");
    exit(1);
}

$password = Env::string('SEED_PASSWORD');
if ($password === '') {
    fwrite(STDERR, "Set SEED_PASSWORD in .env (at least 8 characters with a letter and a digit).\n");
    exit(1);
}

(new RolePermissionsSeed())->run();
(new SettingsSeed())->run();
$users = (new UsersSeed())->run($password);
$cities = (new CitiesSeed())->run();
(new StadiumsSeed())->run($cities, $users['manager']);

echo "Seed complete. Users: admin, manager, customer (password from SEED_PASSWORD).\n";
