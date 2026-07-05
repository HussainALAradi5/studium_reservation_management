<?php
// Ensure SITE_ROOT is available
if (!defined('SITE_ROOT')) define('SITE_ROOT', '/studium_reservation_management');

$isLoggedIn = isset($currentUser);

$navLinks = [];

if ($isLoggedIn) {
    $navLinks = [
        ['name' => 'Dashboard', 'url' => SITE_ROOT . '/index.php'],
        ['name' => 'Profile', 'url' => SITE_ROOT . '/views/user/profile.php'], 
        ['name' => 'Logout', 'url' => SITE_ROOT . '/logout.php'],
    ];
} else {
    $navLinks = [
        ['name' => 'Login', 'url' => SITE_ROOT . '/views/auth/login.php'],     
        ['name' => 'Register', 'url' => SITE_ROOT . '/views/auth/register.php'], 
    ];
}
?>

<ul class="flex items-center gap-6">
    <?php foreach ($navLinks as $link): ?>
        <li>
            <a href="<?php echo htmlspecialchars($link['url']); ?>" 
               class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors">
                <?php echo htmlspecialchars($link['name']); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>