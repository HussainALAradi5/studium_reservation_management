<?php
/**
 * @var \App\Dtos\Common\PageResult $page
 * @var array $filters
 * @var array<string, string> $roles
 * @var string $currentPath
 */
$roleTones = ['admin' => 'danger', 'manager' => 'info', 'customer' => 'success'];
$me = current_user();
$rows = [];
foreach ($page->items as $user) {
    $rows[] = [
        'user' => '<div class="row" style="flex-wrap:nowrap"><span class="avatar">' . e($user->initials()) . '</span><div><div class="cell-title">' . e($user->fullName) . '</div><div class="cell-sub">@' . e($user->username) . '</div></div></div>',
        'contact' => '<div>' . e($user->email) . '</div><div class="cell-sub">' . e($user->phone) . '</div>',
        'role' => badge($user->role->label(), $roleTones[$user->role->value], ['icon' => 'shield']),
        'status' => $user->isActive ? badge('Active', 'success') : badge('Disabled', 'neutral'),
        'lastLogin' => '<span class="small muted">' . e($user->lastLoginAt ? format_date($user->lastLoginAt, 'j M Y, H:i') : 'Never') . '</span>',
        'actions' => '<div class="actions">'
            . button('Edit', ['variant' => 'ghost', 'size' => 'sm', 'icon' => 'edit', 'iconOnly' => true, 'href' => url("/users/{$user->code}/edit")])
            . ($user->id !== $me->id ? button($user->isActive ? 'Disable' : 'Enable', ['variant' => $user->isActive ? 'danger-soft' : 'secondary', 'size' => 'sm', 'icon' => 'power', 'iconOnly' => true, 'attrs' => [
                'data-action' => url("/api/users/{$user->code}/toggle"),
                'data-confirm' => $user->isActive ? "Disable {$user->fullName}? They will be signed out and cannot sign in." : "Enable {$user->fullName}?",
                'data-confirm-title' => $user->isActive ? 'Disable user' : 'Enable user',
                'data-confirm-tone' => $user->isActive ? 'danger' : 'success',
            ]]) : '')
            . '</div>',
    ];
}
?>
<?= partial('ui/pageHeader', [
    'title' => 'Users',
    'subtitle' => 'Admins, stadium managers, and customers.',
    'actions' => button('New user', ['icon' => 'plus', 'href' => url('/users/create')]),
]) ?>
<div class="stack">
    <?= partial('filters/genericFilter', [
        'path' => $currentPath,
        'values' => $filters,
        'fields' => [
            ['name' => 'q', 'label' => 'Search', 'placeholder' => 'Name, username, email, phone'],
            ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'options' => $roles, 'emptyOption' => 'All roles'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Disabled'], 'emptyOption' => 'Any status'],
        ],
    ]) ?>
    <div class="card" data-animate="fade-up">
        <div class="card__body">
            <?= partial('ui/genericTable', [
                'columns' => ['user' => 'User', 'contact' => 'Contact', 'role' => 'Role', 'status' => 'Status', 'lastLogin' => 'Last sign-in', 'actions' => ''],
                'rows' => $rows,
                'alignRight' => ['actions'],
                'empty' => ['title' => 'No users match', 'icon' => 'users'],
            ]) ?>
            <?= partial('ui/pagination', ['page' => $page, 'path' => $currentPath, 'filters' => $filters]) ?>
        </div>
    </div>
</div>
