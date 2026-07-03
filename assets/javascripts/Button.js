document.addEventListener('DOMContentLoaded', () => {

    const buttons = document.querySelectorAll('button[id^="submit-btn"]');

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            this.disabled = true;
            this.textContent = 'Processing...';
        });
    });
});