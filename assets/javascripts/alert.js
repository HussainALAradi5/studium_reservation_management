document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', (e) => {
        const dismissBtn = e.target.closest('[data-dismiss-btn]');
        if (dismissBtn) {
            const alertBox = dismissBtn.closest('[data-alert-box]');
            if (alertBox) {
                alertBox.style.opacity = '0';
                alertBox.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    alertBox.remove();
                }, 700);
            }
        }
    });
});