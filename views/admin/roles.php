<?php
// views/admin/roles.php
require_once __DIR__ . '/../../configs/bootstrap.php';

// 1. Guard Clause: Ensure user is logged in
if (!isset($currentUser)) {
    header('Location: ' . SITE_ROOT . '/views/auth/login.php');
    exit;
}

// 2. Initialize variables
$roles = []; 
$error = null;

// 3. Fetch data through the Controller
try {
    $roles = $roleController->indexRoles($currentUser);
} catch (\Exception $e) {
    $error = $e->getMessage();
}

ob_start();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <!-- Page Header & Actions -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Role Management</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Manage authorization groups and their descriptions.</p>
        </div>
        <div>
            <?php
            $btnText = '+ Create New Role';
            $btnId = 'btn-create-role';
            $btnVariant = 'solid';
            $btnType = 'button';
            include __DIR__ . '/../partials/customButton.php';
            ?>
        </div>
    </div>

    <!-- Error Handling -->
    <?php if ($error): ?>
        <div class="mb-6">
            <?php 
                $alertType = \App\Enums\UI\AlertMessageType::ERROR;
                $alertTitle = 'Access Denied';
                $alertMessage = $error;
                include __DIR__ . '/../partials/alert.php';
            ?>
        </div>
    <?php else: ?>

        <!-- Roles Data Table inside a Card -->
        <?php
        $cardTitle = "Active Roles";
        
        $columns = ['ID', 'Role Name', 'Description', 'Actions'];
        $data = [];
        
        foreach ($roles as $role) {
            $actionButtons = '
                <div class="flex gap-3">
                    <button class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm transition-colors">Edit</button>
                    <button class="text-red-600 dark:text-red-400 hover:underline font-medium text-sm transition-colors">Delete</button>
                </div>
            ';

            $data[] = [
                'id'          => $role['id'],
                'name'        => $role['name'],
                'description' => $role['description'],
                'actions'     => $actionButtons 
            ];
        }

        ob_start();
        include __DIR__ . '/../partials/genericTable.php';
        $cardBody = ob_get_clean();

        include __DIR__ . '/../partials/genericCard.php';
        ?>

    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; 
?>