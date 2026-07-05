
const updateButtonText = (isDark) => {
    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
        toggle.innerText = isDark ? 'Light Mode' : 'Dark Mode';
    }
};

const isDarkMode = localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

if (isDarkMode) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('theme-toggle');
    
    
    if (toggle) {
        toggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.theme = isDark ? 'dark' : 'light';
            
            updateButtonText(isDark);
        });
    }
});