<?php

declare(strict_types=1);

define('BASE_DIR', dirname(__DIR__, 2));

// Layer-first autoloader: App\Services\Users\UserService => services/Users/UserService.php
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $parts = explode('\\', substr($class, 4));
    $parts[0] = strtolower($parts[0]);
    $file = BASE_DIR . '/' . implode('/', $parts) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

App\Config\App\Env::load(BASE_DIR . '/.env');
date_default_timezone_set(App\Config\App\AppConfig::timezone());

require BASE_DIR . '/utilities/Views/helpers.php';
