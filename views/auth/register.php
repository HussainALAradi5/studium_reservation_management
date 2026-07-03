<?php
// Start output buffering to capture the form content
ob_start();
?>

<div class="max-w-md mx-auto mt-10">
    <?php
    $cardTitle = "Create an Account";
    
    // Capture the form for the card body[cite: 14]
    ob_start();
    $fields = [
        ['label' => 'Username', 'name' => 'userName', 'type' => 'text'],
        ['label' => 'Email', 'name' => 'email', 'type' => 'email'],
        ['label' => 'Phone Number', 'name' => 'phoneNumber', 'type' => 'tel'],
        ['label' => 'Password', 'name' => 'password', 'type' => 'password'],
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