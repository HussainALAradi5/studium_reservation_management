
document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('[data-custom-toggle]');
    console.log('🔌 Custom Toggles Initialized. Found instances:', toggles.length);

    toggles.forEach((toggle, index) => {
        const input = toggle.querySelector('input[type="checkbox"]');
        if (!input) return;

        toggle.addEventListener('keydown', (e) => {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault(); 
                input.click(); 
            }
        });

        input.addEventListener('change', () => {
            console.log(`🔘 Toggle #${index} (${input.name}) changed state to:`, input.checked);
            toggle.dataset.state = input.checked ? 'checked' : 'unchecked';
        });
        
        toggle.dataset.state = input.checked ? 'checked' : 'unchecked';
    });
});