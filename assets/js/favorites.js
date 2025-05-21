/**
 * Favorites functionality for Rydr car rental
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all favorite buttons
    initFavoriteButtons();
});

/**
 * Initialize favorite buttons
 */
function initFavoriteButtons() {
    // Initialize favorite buttons
    document.querySelectorAll('.favorite-button, .favorite-icon').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isLoggedIn = document.body.classList.contains('logged-in');
            
            if (!isLoggedIn) {
                // Show login prompt
                showLoginPrompt();
                return;
            }
            
            const carId = this.getAttribute('data-car-id');
            toggleFavorite(carId, this);
        });
    });
}

/**
 * Toggle favorite status for a car
 * @param {number} carId - The car ID
 * @param {HTMLElement} button - The button element
 */
function toggleFavorite(carId, button) {
    // Send AJAX request to toggle favorite
    const formData = new FormData();
    formData.append('car_id', carId);
    
    fetch('/toggle-favorite', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_favorite) {
                // Add to favorites
                button.classList.add('active');
                if (button.classList.contains('favorite-button')) {
                    button.classList.add('active');
                } else if (button.classList.contains('favorite-icon')) {
                    button.classList.add('active');
                    button.querySelector('i').classList.remove('fa-heart-o');
                    button.querySelector('i').classList.add('fa-heart');
                }
                showNotification('Auto toegevoegd aan favorieten', 'success');
            } else {
                // Remove from favorites
                if (button.classList.contains('favorite-button')) {
                    button.classList.remove('active');
                } else if (button.classList.contains('favorite-icon')) {
                    button.classList.remove('active');
                    button.querySelector('i').classList.remove('fa-heart');
                    button.querySelector('i').classList.add('fa-heart-o');
                }
                showNotification('Auto verwijderd uit favorieten', 'info');
            }
        } else {
            showNotification(data.message || 'Er is een fout opgetreden', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Er is een fout opgetreden', 'error');
    });
}

/**
 * Show notification
 * @param {string} message - The notification message
 * @param {string} type - Notification type: success, error, info
 */
function showNotification(message, type) {
    // Check if notification container exists, if not create it
    let notificationContainer = document.getElementById('notification-container');
    
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.position = 'fixed';
        notificationContainer.style.bottom = '20px';
        notificationContainer.style.right = '20px';
        notificationContainer.style.zIndex = '1000';
        document.body.appendChild(notificationContainer);
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.innerHTML = message;
    
    // Style notification based on type
    notification.style.backgroundColor = type === 'success' ? 'rgba(25, 135, 84, 0.9)' : 
                                         type === 'error' ? 'rgba(220, 53, 69, 0.9)' : 
                                         'rgba(13, 110, 253, 0.9)';
    notification.style.color = 'white';
    notification.style.padding = '12px 20px';
    notification.style.borderRadius = '8px';
    notification.style.marginTop = '10px';
    notification.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
    notification.style.transition = 'all 0.3s ease';
    notification.style.opacity = '0';
    notification.style.transform = 'translateY(20px)';
    
    // Add to container
    notificationContainer.appendChild(notification);
    
    // Show with animation
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Hide and remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';
        
        // Remove from DOM after animation completes
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

/**
 * Show login prompt
 */
function showLoginPrompt() {
    // Check if login prompt already exists
    if (document.getElementById('login-prompt')) {
        return;
    }
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.id = 'login-overlay';
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0,0,0,0.7)';
    overlay.style.zIndex = '1000';
    overlay.style.display = 'flex';
    overlay.style.justifyContent = 'center';
    overlay.style.alignItems = 'center';
    
    // Create prompt
    const prompt = document.createElement('div');
    prompt.id = 'login-prompt';
    prompt.style.backgroundColor = 'white';
    prompt.style.borderRadius = '12px';
    prompt.style.padding = '30px';
    prompt.style.maxWidth = '400px';
    prompt.style.width = '90%';
    prompt.style.textAlign = 'center';
    prompt.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
    
    // Add content
    prompt.innerHTML = `
        <div style="font-size: 50px; color: #ff3b58; margin-bottom: 15px;">
            <i class="fas fa-heart"></i>
        </div>
        <h3 style="margin-bottom: 15px; font-weight: 600; color: #333;">Log in om favorieten toe te voegen</h3>
        <p style="margin-bottom: 25px; color: #666;">Om auto's aan uw favorieten toe te voegen, moet u eerst inloggen of een account aanmaken.</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="/login-form" style="background-color: #3366cc; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 500;">Inloggen</a>
            <a href="/register-form" style="background-color: #6c757d; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 500;">Registreren</a>
        </div>
    `;
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.style.position = 'absolute';
    closeBtn.style.top = '15px';
    closeBtn.style.right = '15px';
    closeBtn.style.background = 'none';
    closeBtn.style.border = 'none';
    closeBtn.style.fontSize = '24px';
    closeBtn.style.cursor = 'pointer';
    closeBtn.style.color = '#666';
    
    closeBtn.addEventListener('click', function() {
        overlay.remove();
    });
    
    prompt.appendChild(closeBtn);
    overlay.appendChild(prompt);
    document.body.appendChild(overlay);
    
    // Close on click outside
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
} 