<?php
/**
 * Sidebar navigation; each link appears only when the user's role holds its permission.
 *
 * @var string $currentPath
 */
use App\Enums\Users\Permission;

$user = current_user();
$link = static fn (string $path, string $icon, string $label) =>
    '<a class="nav-link' . nav_active($currentPath, $path) . '" href="' . e(url($path)) . '">' . icon($icon) . '<span>' . e($label) . '</span></a>';
$canManageStadiums = can(Permission::STADIUMS_MANAGE);
$canExpenses = can(Permission::EXPENSES_MANAGE);
$canReports = can(Permission::REPORTS_VIEW);
$canCities = can(Permission::CITIES_MANAGE);
$canUsers = can(Permission::USERS_MANAGE);
$canSettings = can(Permission::SETTINGS_MANAGE);
?>
<aside class="sidebar" id="sidebar" aria-label="Main navigation">
    <a class="brand" href="<?= e(url('/dashboard')) ?>">
        <span class="brand__mark"><?= icon('ball') ?></span> Studium
    </a>

    <nav class="stack" style="gap:4px">
        <?= $link('/dashboard', 'home', 'Dashboard') ?>
        <?= $link('/stadiums', 'stadium', 'Find a stadium') ?>
        <?= $link('/reservations', 'calendar', $user->isCustomer() ? 'My bookings' : 'Reservations') ?>
        <?= $link('/notifications', 'bell', 'Notifications') ?>

        <?php if ($canManageStadiums || $canExpenses || $canReports): ?>
            <div class="nav-section">Manage</div>
            <?php if ($canManageStadiums): ?><?= $link('/manage/stadiums', 'map', 'My stadiums') ?><?php endif; ?>
            <?php if ($canExpenses): ?><?= $link('/accounting/expenses', 'receipt', 'Expenses') ?><?php endif; ?>
            <?php if ($canReports): ?><?= $link('/accounting/reports', 'chart', 'Accounting') ?><?php endif; ?>
        <?php endif; ?>

        <?php if ($canCities || $canUsers || $canSettings): ?>
            <div class="nav-section">Administration</div>
            <?php if ($canCities): ?><?= $link('/cities', 'city', 'Cities') ?><?php endif; ?>
            <?php if ($canUsers): ?><?= $link('/users', 'users', 'Users') ?><?php endif; ?>
            <?php if ($canSettings): ?><?= $link('/settings', 'settings', 'Settings & roles') ?><?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar__footer stack" style="gap:4px">
        <?= $link('/profile', 'user', 'My profile') ?>
        <button type="button" class="nav-link" style="border:0;background:none;font:inherit;cursor:pointer;width:100%"
                data-action="<?= e(url('/api/auth/logout')) ?>" data-method="POST">
            <?= icon('logout') ?><span>Sign out</span>
        </button>
    </div>
</aside>
<div class="sidebar-overlay" data-nav-close></div>
