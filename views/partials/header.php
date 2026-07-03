<header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 transition-colors duration-300">
    <nav class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <!-- Logo -->
        <div class="text-xl font-bold text-slate-900 dark:text-white">Studium</div>
        
        <div class="hidden md:flex">
            <?php include 'Navbar.php'; ?>
        </div>

        <div class="flex items-center gap-2">
            <?php 
                $btnText = 'Toggle'; 
                $btnId = 'theme-toggle';
                $btnVariant = 'btn-ghost'; 
                $btnSize = 'btn-md';
                include 'customButton.php'; 
            ?>
        </div>
    </nav>
</header>