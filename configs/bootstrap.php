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

// In bootstrap.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

use App\Services\User\UserService;
use App\Services\Auth\RoleService; 
use App\Services\AuthorizationService;
use App\Controllers\Auth\RoleController;

$userService = new UserService($pdo);
$authService = new AuthorizationService($pdo);
$roleService = new RoleService($pdo);

$roleController = new RoleController($roleService, $authService);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Retrieve current user from session
$currentUser = $_SESSION['user'] ?? null;