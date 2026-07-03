document.addEventListener('DOMContentLoaded', () => {
    // Select all buttons meant to be disabled on click
    const buttons = document.querySelectorAll('button[id^="submit-btn"]');

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            this.disabled = true;
            
            this.textContent = 'Processing...';
            
        });
    });
});