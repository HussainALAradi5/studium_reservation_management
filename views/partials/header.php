<header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 transition-colors duration-300">
    <nav class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="text-xl font-bold text-slate-900 dark:text-white">Studium</div>
        
        <div class="flex items-center gap-2">
            <?php 
                $btnText = 'Toggle Mode'; 
                $btnId = 'theme-toggle';
                $btnVariant = 'btn-solid'; 
                $btnSize = 'btn-md';
                include 'customButton.php'; 
            ?>
        </div>
    </nav>
</header>