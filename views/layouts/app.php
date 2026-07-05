<?php
// Fallback to prevent Undefined variable error in the IDE
$content = $content ?? '<p>Content missing.</p>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' };</script>
    <!-- Ensure the path matches your project structure -->
    <link rel="stylesheet" href="./assets/css/styles/style.css">
    <script src="./assets/javascripts/theme.js" defer></script>
</head>
<body class="bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-10 min-h-screen">
        <?php echo $content; ?>
    </main>
</body>
</html>