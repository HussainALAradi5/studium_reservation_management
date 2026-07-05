// assets/javascripts/custom-select.js

document.addEventListener('DOMContentLoaded', () => {
    const customSelects = document.querySelectorAll('[data-custom-select]');
    console.log('🔌 Custom Select Initialized. Found instances:', customSelects.length);

    // 1. Document click listener for outside clicks
    document.addEventListener('click', (e) => {
        customSelects.forEach((select, index) => {
            if (!select.contains(e.target)) {
                // Only log if the dropdown isn't already hidden to prevent log spam
                const dropdown = select.querySelector('[data-select-dropdown]');
                if (!dropdown.classList.contains('hidden')) {
                    console.log(`🖱️ Clicked outside select #${index}. Closing it.`);
                    closeDropdown(select);
                }
            }
        });
    });

    customSelects.forEach((select, index) => {
        const toggleBtn = select.querySelector('[data-select-toggle]');
        const dropdown = select.querySelector('[data-select-dropdown]');
        const input = select.querySelector('[data-select-input]');
        const label = select.querySelector('[data-select-label]');
        const icon = select.querySelector('[data-select-icon]');
        const options = select.querySelectorAll('li[data-value]');

        console.log(`🔍 Select #${index} setup complete. Options found:`, options.length);

        // 2. Toggle button listener
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isHidden = dropdown.classList.contains('hidden');
            console.log(`🔘 Toggle clicked on select #${index}. Currently hidden:`, isHidden);
            
            customSelects.forEach((s, sIndex) => { 
                if (s !== select) {
                    const otherDropdown = s.querySelector('[data-select-dropdown]');
                    if (!otherDropdown.classList.contains('hidden')) {
                        console.log(`🧹 Closing other open select #${sIndex}`);
                        closeDropdown(s); 
                    }
                }
            });

            if (isHidden) {
                openDropdown(dropdown, icon, index);
            } else {
                closeDropdown(select, index);
            }
        });

        // 3. Option selection listener
        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation(); 
                
                const selectedValue = option.getAttribute('data-value');
                const selectedText = option.textContent.trim();
                
                console.log(`✅ Option selected on select #${index} -> Value: "${selectedValue}", Label: "${selectedText}"`);
                
                input.value = selectedValue;
                label.textContent = selectedText;
                
                label.classList.remove('text-slate-400');
                label.classList.add('text-slate-900', 'dark:text-white'); 
                
                closeDropdown(select, index);
            });
        });
    });

    // 4. Open/Close functions
    function openDropdown(dropdown, icon, index = 'unknown') {
        console.log(`📂 Executing openDropdown() for select #${index}`);
        clearTimeout(dropdown.dataset.timeoutId); 
        
        dropdown.classList.remove('hidden');
        icon.classList.add('rotate-180'); 
        
        setTimeout(() => {
            dropdown.classList.remove('opacity-0', 'scale-95');
            dropdown.classList.add('opacity-100', 'scale-100');
            console.log(`🎬 Open animation classes applied for select #${index}`);
        }, 10);
    }

    function closeDropdown(selectContainer, index = 'unknown') {
        const dropdown = selectContainer.querySelector('[data-select-dropdown]');
        const icon = selectContainer.querySelector('[data-select-icon]');
        
        console.log(`📁 Executing closeDropdown() for select #${index}`);
        clearTimeout(dropdown.dataset.timeoutId);
        
        icon.classList.remove('rotate-180');
        dropdown.classList.remove('opacity-100', 'scale-100');
        dropdown.classList.add('opacity-0', 'scale-95');
        
        dropdown.dataset.timeoutId = setTimeout(() => {
            dropdown.classList.add('hidden');
            console.log(`🛑 Close animation finished, 'hidden' class added for select #${index}`);
        }, 200); 
    }
});