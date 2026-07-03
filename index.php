<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studium Reservation Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/javascripts/Button.js" defer></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
</head>
<body class="bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    
    <?php include 'views/partials/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-10 min-h-screen">
        <?php include 'views/home.php'; ?>
    </main>

    <?php include 'views/partials/footer.php'; ?>

    <script src="assets/javascripts/theme.js" defer></script>
</body>
</html>