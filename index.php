<?php
// index.php
session_start();

require_once __DIR__ . '/configs/bootstrap.php';

$currentUser = $_SESSION['user'] ?? null;

$page = $_GET['page'] ?? 'home';

$contentMap = [
    'home'     => 'views/home.php',
    'register' => 'views/auth/register.php',
    'login'    => 'views/auth/login.php',
    'profile'  => 'views/user/profile.php'
];

$viewFile = $contentMap[$page] ?? 'views/home.php';

ob_start();
include $viewFile;
$content = ob_get_clean();

include 'views/layouts/app.php';
