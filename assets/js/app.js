// Frontend JavaScript functionality for SFStore Ecommerce

document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart functionality
    initializeCart();
    
    // Initialize product functionality
    initializeProducts();
    
    // Initialize forms
    initializeForms();
    
    // Initialize alerts auto-hide
    initializeAlerts();
});

// Cart functionality
function initializeCart() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const quantity = this.dataset.quantity || 1;
            
            addToCart(productId, quantity);
        });
    });
    
    // Update cart quantity buttons
    const updateQuantityButtons = document.querySelectorAll('.btn-update-quantity');
    updateQuantityButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const quantity = this.dataset.quantity;
            
            updateCartQuantity(productId, quantity);
        });
    });
    
    // Remove from cart buttons
    const removeButtons = document.querySelectorAll('.btn-remove-from-cart');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            removeFromCart(productId);
        });
    });
    
    // Update cart count on page load
    updateCartCount();
}

// Add product to cart
async function addToCart(productId, quantity = 1) {
    try {
        showSpinner();
        
        const response = await fetch(`/cart/add/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `quantity=${quantity}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            updateCartBadge(data.cartCount);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        showAlert('error', 'Error al agregar producto al carrito');
    } finally {
        hideSpinner();
    }
}

// Update cart item quantity
async function updateCartQuantity(productId, quantity) {
    try {
        const response = await fetch(`/cart/update/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `quantity=${quantity}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCartBadge(data.cartCount);
            // Reload page to update cart display
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        showAlert('error', 'Error al actualizar cantidad');
    }
}

// Remove product from cart
async function removeFromCart(productId) {
    try {
        const response = await fetch(`/cart/remove/${productId}`, {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            updateCartBadge(data.cartCount);
            // Reload page to update cart display
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        showAlert('error', 'Error al eliminar producto del carrito');
    }
}

// Update cart count in navigation
async function updateCartCount() {
    try {
        const response = await fetch('/cart/count');
        const data = await response.json();
        updateCartBadge(data.count);
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Update cart badge display
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

// Product functionality
function initializeProducts() {
    // Quantity selectors
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const min = parseInt(this.min) || 1;
            const max = parseInt(this.max) || 999;
            let value = parseInt(this.value) || min;
            
            if (value < min) value = min;
            if (value > max) value = max;
            
            this.value = value;
        });
    });
}

// Form functionality
function initializeForms() {
    // Add Bootstrap validation classes
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Alert functionality
function initializeAlerts() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
}

// Show alert message
function showAlert(type, message) {
    const alertContainer = document.querySelector('.alert-container') || document.body;
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alertDiv.style.transition = 'opacity 0.5s ease';
        alertDiv.style.opacity = '0';
        setTimeout(() => {
            alertDiv.remove();
        }, 500);
    }, 5000);
}

// Spinner functionality
function showSpinner() {
    const spinner = document.querySelector('.spinner-overlay') || createSpinner();
    spinner.style.display = 'flex';
}

function hideSpinner() {
    const spinner = document.querySelector('.spinner-overlay');
    if (spinner) {
        spinner.style.display = 'none';
    }
}

function createSpinner() {
    const spinner = document.createElement('div');
    spinner.className = 'spinner-overlay';
    spinner.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    `;
    document.body.appendChild(spinner);
    return spinner;
}