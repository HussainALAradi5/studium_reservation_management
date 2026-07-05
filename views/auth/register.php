<?php
require_once __DIR__ . '/../../configs/bootstrap.php'; 

use App\Controllers\user\UserController;
use App\Models\User\User;
use App\Enums\User\Gender;
use App\Enums\User\UserType;

$userController = new UserController($userService, $authService);
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $newUser = User::fromRegistration(
            $_POST['userName'],
            $_POST['password'],
            $_POST['email'],
            $_POST['phoneNumber'],
            Gender::from($_POST['gender']),
            UserType::USER
        );
        $userController->register($newUser);
        
        header('Location: ' . SITE_ROOT . '/index.php?page=login');
        exit;
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

ob_start();
?>

<div class="max-w-md mx-auto mt-10">
    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php
    $cardTitle = "Create an Account";
    ob_start();
    
    $fields = [
        ['label' => 'Username', 'name' => 'userName', 'type' => 'text', 'required' => true],
        ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'required' => true],
        ['label' => 'Phone Number', 'name' => 'phoneNumber', 'type' => 'tel', 'required' => true],
        ['label' => 'Password', 'name' => 'password', 'type' => 'password', 'required' => true],
        [
            'label' => 'Gender', 
            'name' => 'gender', 
            'type' => 'select', 
            'placeholder' => 'Select your gender',
            'required' => true,
            'options' => [
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female']
            ]
        ]
    ];
    
    include __DIR__ . '/../partials/genericForm.php';
    $cardBody = ob_get_clean();
    include __DIR__ . '/../partials/genericCard.php';
    ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>