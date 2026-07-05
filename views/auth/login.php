<?php
// Start output buffering
ob_start();
?>

<div class="max-w-md mx-auto mt-10">
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