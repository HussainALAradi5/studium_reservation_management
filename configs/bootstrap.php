<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../'; 
    
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    
    $relative_class = substr($class, strlen($prefix));
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

use App\Services\User\UserService;
use App\Services\AuthorizationService;

$userService = new UserService($pdo);
$authService = new AuthorizationService($pdo);