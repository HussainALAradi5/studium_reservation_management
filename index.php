<?php
session_start();
$currentUser = $_SESSION['user'] ?? null;

// Get the requested page from the URL (e.g., index.php?page=register)
$page = $_GET['page'] ?? 'home'; 

// Logic to determine which content to load
$contentMap = [
    'home'     => 'views/home.php',
    'register' => 'views/auth/register.php',
    'login'    => 'views/auth/login.php',
    'profile'  => 'views/user/profile.php'
];
define('SITE_ROOT', '/studium_reservation_management');
$viewFile = $contentMap[$page] ?? 'views/home.php';

ob_start();
include $viewFile;
$content = ob_get_clean();
include 'views/layouts/app.php';
?>