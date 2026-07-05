// assets/javascripts/dialog.js

const Dialog = {
    /**
     * Opens a modal dialog using the native HTML5 showModal method.
     * @param {string} id - The DOM element ID of the <dialog>
     */
    open: (id) => {
        const dialog = document.getElementById(id);
        if (dialog) {
            dialog.showModal();
        } else {
            console.error(`[Dialog Error] Element with id "${id}" not found.`);
        }
    },

    /**
     * Closes a modal dialog.
     * @param {string} id - The DOM element ID of the <dialog>
     */
    close: (id) => {
        const dialog = document.getElementById(id);
        if (dialog) {
            dialog.close();
        }
    },
    
    /**
     * Configures and opens a confirmation/alert dialog.
     * Clones the confirmation button to cleanly wipe any previous click event listeners.
     * @param {string} id - The DOM element ID of the <dialog>
     * @param {Function} onConfirm - Callback function executed when the user confirms
     */
    confirm: (id, onConfirm) => {
        const dialog = document.getElementById(id);
        if (!dialog) {
            console.error(`[Dialog Error] Confirmation dialog with id "${id}" not found.`);
            return;
        }

        const btn = dialog.querySelector('#dialog-confirm-btn');
        if (btn) {
            // Clone the node to clear out prior event listeners attached to this instance
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            // Register the single runtime confirmation event handler
            newBtn.addEventListener('click', () => {
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
                dialog.close();
            });
        } else {
            console.warn(`[Dialog Warning] Action button '#dialog-confirm-btn' missing inside dialog "${id}".`);
        }
        
        dialog.showModal();
    }
};

/**
 * Global Auto-Initialization Listener
 * Ensures that if a flash dialog HTML element was rendered by the backend,
 * it displays instantly without triggering async layout or scripting race conditions.
 */
document.addEventListener('DOMContentLoaded', () => {
    const flashDialog = document.getElementById('flash-dialog');
    if (flashDialog) {
        Dialog.open('flash-dialog');
    }
});