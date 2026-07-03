<?php
// Assuming $currentUser is set by your application session
$isLoggedIn = isset($currentUser);

$navLinks = [];

if ($isLoggedIn) {
    $navLinks = [
        ['name' => 'Dashboard', 'url' => 'index.php'],
        ['name' => 'Profile', 'url' => 'views/user/profile.php'], 
        ['name' => 'Logout', 'url' => 'logout.php'],
    ];
} else {
    $navLinks = [
        ['name' => 'Login', 'url' => 'views/auth/login.php'],     
        ['name' => 'Register', 'url' => 'views/auth/register.php'], 
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