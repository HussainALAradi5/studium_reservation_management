    const toggle = document.getElementById('theme-toggle');
    toggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
    });