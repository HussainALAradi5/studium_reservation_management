<?php

use App\Enums\UI\AlertMessageType;

$content = $content ?? '<p>Content missing.</p>';
defined('SITE_ROOT') or define('SITE_ROOT', '/studium_reservation_management');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        };
    </script>
    <link rel="stylesheet" href="<?= SITE_ROOT ?>/assets/css/styles/style.css">
    <script src="<?= SITE_ROOT ?>/assets/javascripts/theme.js" defer></script>
    <script src="<?= SITE_ROOT ?>/assets/javascripts/alert.js" defer></script>
    <script src="<?= SITE_ROOT ?>/assets/javascripts/custom-select.js" defer></script>
</head>

<body class="bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-10 min-h-screen">
        <!-- Global Flash Alert Area -->
        <?php if (isset($_SESSION['flash_alert'])): ?>
            <?php
            $alert = $_SESSION['flash_alert'];
            $alertType = AlertMessageType::from($alert['type']);
            $alertTitle = $alert['title'];
            $alertMessage = $alert['message'];

            include __DIR__ . '/../partials/alert.php';
            unset($_SESSION['flash_alert']);
            ?>
        <?php endif; ?>

        <?php echo $content; ?>
    </main>
</body>

</html>