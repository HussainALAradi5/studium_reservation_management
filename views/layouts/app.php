<?php
/**
 * Main layout: glass sidebar + topbar for signed-in users, glass top navigation for guests.
 *
 * @var string $content
 * @var string $title
 * @var string $currentPath
 */
use App\Utilities\Security\Session;

$user = current_user();
$flash = Session::pullFlash();
?>
<!doctype html>
<html lang="en">
<head>
    <?= partial('layout/head', ['title' => $title ?? '', 'needsMap' => $needsMap ?? false]) ?>
</head>
<body<?= $flash ? ' data-flash="' . e(json_encode($flash)) . '"' : '' ?>>
<?= partial('ui/icons') ?>
<?php if ($user): ?>
    <div class="shell" data-shell>
        <?= partial('layout/sidebar', ['currentPath' => $currentPath ?? '/']) ?>
        <div class="main">
            <header class="topbar">
                <?= button('Open menu', ['variant' => 'ghost', 'iconOnly' => true, 'icon' => 'menu', 'attrs' => ['class' => 'topbar__menu', 'data-nav-toggle' => true, 'aria-controls' => 'sidebar']]) ?>
                <div class="topbar__title"><?= e($title ?? '') ?></div>
                <?= partial('layout/themeToggle') ?>
                <a class="user-chip" href="<?= e(url('/profile')) ?>">
                    <span class="avatar"><?= e($user->initials()) ?></span>
                    <span class="user-chip__text">
                        <span class="user-chip__name"><?= e($user->fullName) ?></span><br>
                        <span class="user-chip__role"><?= e($user->role->label()) ?></span>
                    </span>
                </a>
            </header>
            <main class="content" id="main"><?= $content ?></main>
        </div>
    </div>
<?php else: ?>
    <div class="shell shell--guest">
        <nav class="guest-nav glass" aria-label="Main navigation">
            <a class="brand" href="<?= e(url('/')) ?>"><span class="brand__mark"><?= icon('ball') ?></span> Studium</a>
            <div class="guest-nav__links">
                <a class="nav-link<?= nav_active($currentPath ?? '/', '/stadiums') ?>" href="<?= e(url('/stadiums')) ?>"><?= icon('stadium') ?><span>Stadiums</span></a>
            </div>
            <?= partial('layout/themeToggle') ?>
            <?= button('Sign in', ['variant' => 'ghost', 'href' => url('/login'), 'icon' => 'login']) ?>
            <?= button('Get started', ['href' => url('/register'), 'pill' => true, 'attrs' => ['class' => 'btn__label-optional']]) ?>
        </nav>
        <main class="guest-content" id="main"><?= $content ?></main>
        <footer class="footer">&copy; <?= date('Y') ?> Studium · Stadium rentals made simple</footer>
    </div>
<?php endif; ?>
<div class="toasts" data-toasts aria-live="polite"></div>
</body>
</html>
