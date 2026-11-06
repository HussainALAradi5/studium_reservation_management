<?php
/**
 * Top navigation for visitors who are not signed in (public pages and the sign-in / register pages).
 *
 * @var string $currentPath
 */
?>
<nav class="guest-nav glass" aria-label="Main navigation">
    <a class="brand" href="<?= e(url('/')) ?>"><span class="brand__mark"><?= icon('ball') ?></span> Studium</a>
    <div class="guest-nav__links">
        <a class="nav-link<?= nav_active($currentPath, '/stadiums') ?>" href="<?= e(url('/stadiums')) ?>"><?= icon('stadium') ?><span>Stadiums</span></a>
    </div>
    <?= partial('layout/themeToggle') ?>
    <?php if ($currentPath !== '/login'): ?>
        <?= button('Sign in', ['variant' => 'ghost', 'href' => url('/login'), 'icon' => 'login']) ?>
    <?php endif; ?>
    <?php if ($currentPath !== '/register'): ?>
        <?= button('Get started', ['href' => url('/register'), 'pill' => true, 'attrs' => ['class' => 'btn__label-optional']]) ?>
    <?php endif; ?>
</nav>
