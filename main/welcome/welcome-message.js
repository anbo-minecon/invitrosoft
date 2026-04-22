/**
 * Welcome Message Component (Modal Version)
 * 
 * Usage:
 * 1. Include this script and the CSS file in your HTML
 * 2. Call showWelcomeModal() with your configuration
 * 
 * Example:
 * showWelcomeModal({
 *     title: '¡Bienvenido al Sistema!',
 *     message: 'Gracias por iniciar sesión. Estamos encantados de tenerte aquí.',
 *     imageUrl: '/path/to/image.jpg',
 *     buttons: [
 *         { text: 'Comenzar', type: 'primary', onClick: () => console.log('Comenzar') },
 *         { text: 'Más tarde', type: 'secondary', onClick: () => console.log('Más tarde') }
 *     ]
 * });
 */

// Default configuration
const defaultConfig = {
    title: '¡Bienvenido!',
    message: 'Bienvenido al sistema',
    imageUrl: '/img/avatar/welcome.png',
    showClose: true,
    closeOnOverlayClick: true,
    buttons: [],
    onClose: null
};

// Create modal container
function createModalContainer() {
    let container = document.getElementById('welcome-modal-container');
    
    if (!container) {
        container = document.createElement('div');
        container.id = 'welcome-modal-container';
        container.className = 'welcome-overlay';
        container.innerHTML = `
            <div class="welcome-message">
                <div class="welcome-image-container">
                    <img src="" alt="Welcome" class="welcome-image" id="welcome-modal-image">
                </div>
                <div class="welcome-content">
                    <button class="welcome-close" aria-label="Cerrar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h1 class="welcome-title"></h1>
                    <p class="welcome-subtitle"></p>
                    <div class="welcome-actions"></div>
                </div>
            </div>
        `;
        document.body.appendChild(container);
    }
    
    return container;
}

// Show welcome modal
function showWelcomeModal(config = {}) {
    // Merge default config with provided config
    const mergedConfig = { ...defaultConfig, ...config };
    const { title, message, imageUrl, showClose, closeOnOverlayClick, buttons, onClose } = mergedConfig;
    
    // Create or get modal container
    const container = createModalContainer();
    const modal = container.querySelector('.welcome-message');
    const titleElement = container.querySelector('.welcome-title');
    const messageElement = container.querySelector('.welcome-subtitle');
    const imageElement = container.querySelector('#welcome-modal-image');
    const actionsContainer = container.querySelector('.welcome-actions');
    const closeButton = container.querySelector('.welcome-close');
    
    // Set content
    titleElement.textContent = title;
    messageElement.innerHTML = message;
    
    // Set image if provided
    if (imageUrl) {
        imageElement.src = imageUrl;
        imageElement.style.display = 'block';
    } else {
        imageElement.style.display = 'none';
        container.querySelector('.welcome-image-container').style.display = 'none';
    }
    
    // Clear and add buttons
    actionsContainer.innerHTML = '';
    if (buttons && buttons.length > 0) {
        buttons.forEach(button => {
            const btn = document.createElement('button');
            btn.className = `welcome-button ${button.type || 'primary'}`;
            btn.textContent = button.text;
            if (button.onClick) {
                btn.addEventListener('click', (e) => {
                    button.onClick(e);
                    if (button.closeOnClick !== false) {
                        hideWelcomeModal();
                    }
                });
            } else {
                btn.addEventListener('click', () => hideWelcomeModal());
            }
            actionsContainer.appendChild(btn);
        });
    } else if (showClose) {
        // Add default close button if no buttons are provided
        const closeBtn = document.createElement('button');
        closeBtn.className = 'welcome-button primary';
        closeBtn.textContent = 'Entendido';
        closeBtn.addEventListener('click', () => hideWelcomeModal());
        actionsContainer.appendChild(closeBtn);
    }
    
    // Close button handler
    if (showClose && closeButton) {
        closeButton.style.display = 'flex';
        closeButton.addEventListener('click', (e) => {
            e.stopPropagation();
            hideWelcomeModal();
            if (typeof onClose === 'function') {
                onClose();
            }
        });
    } else if (closeButton) {
        closeButton.style.display = 'none';
    }
    
    // Close on overlay click
    if (closeOnOverlayClick) {
        container.addEventListener('click', (e) => {
            if (e.target === container) {
                hideWelcomeModal();
                if (typeof onClose === 'function') {
                    onClose();
                }
            }
        });
    }
    
    // Show modal
    setTimeout(() => {
        container.classList.add('show');
        modal.classList.add('show');
    }, 50);
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
    
    // Return close function
    return hideWelcomeModal;
}

// Hide welcome modal
function hideWelcomeModal() {
    const container = document.getElementById('welcome-modal-container');
    if (!container) return;
    
    const modal = container.querySelector('.welcome-message');
    if (modal) {
        modal.classList.remove('show');
    }
    
    container.classList.remove('show');
    
    // Re-enable body scroll
    document.body.style.overflow = '';
    
    // Remove from DOM after animation
    setTimeout(() => {
        if (container && container.parentNode) {
            container.parentNode.removeChild(container);
        }
    }, 300);
}

// Export to global scope
window.showWelcomeModal = showWelcomeModal;
window.hideWelcomeModal = hideWelcomeModal;
