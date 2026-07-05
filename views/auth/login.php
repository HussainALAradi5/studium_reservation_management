<?php
// views/auth/login.php
require_once __DIR__ . '/../../configs/bootstrap.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Authenticate using the AuthorizationService
        $user = $authService->login($_POST['userName'], $_POST['password']);
        
        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: ' . SITE_ROOT . '/index.php');
            exit;
        } else {
            throw new \Exception("Invalid username or password.");
        }
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
    $cardTitle = "Login to Your Account";
    ob_start();
    $fields = [
        ['label' => 'Username', 'name' => 'userName', 'type' => 'text'],
        ['label' => 'Password', 'name' => 'password', 'type' => 'password']
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