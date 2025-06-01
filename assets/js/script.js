// Main JavaScript functionality

// Product Modal Functions
function openProductModal(productId) {
    const modal = document.getElementById('productModal');
    const modalContent = document.getElementById('modalContent');
    
    // Show loading
    modalContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.style.display = 'block';
    
    // Fetch product details
    fetch(`/isabelle-prints/api/product-details.php?id=${productId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            modalContent.innerHTML = html;
            
            // Initialize any form elements or event listeners in the modal
            initializeModalElements();
        })
        .catch(error => {
            console.error('Error loading product details:', error);
            modalContent.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Sorry, we couldn't load the product details. Please try again later.</p>
                    <button class="btn btn-primary" onclick="closeModal()">Close</button>
                </div>
            `;
        });
}

function initializeModalElements() {
    // Initialize size options
    const sizeOptions = document.querySelectorAll('.size-option');
    if (sizeOptions.length > 0) {
        sizeOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                sizeOptions.forEach(opt => opt.classList.remove('active'));
                // Add active class to clicked option
                this.classList.add('active');
                // Update hidden input value
                const sizeInput = document.querySelector('input[name="size"]');
                if (sizeInput) {
                    sizeInput.value = this.dataset.size;
                }
            });
        });
    }
    
    // Initialize color options
    const colorOptions = document.querySelectorAll('.color-option');
    if (colorOptions.length > 0) {
        colorOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                colorOptions.forEach(opt => opt.classList.remove('active'));
                // Add active class to clicked option
                this.classList.add('active');
                // Update hidden input value
                const colorInput = document.querySelector('input[name="color"]');
                if (colorInput) {
                    colorInput.value = this.dataset.color;
                }
            });
        });
    }
}

function closeModal() {
    const modal = document.getElementById('productModal');
    modal.style.display = 'none';
}

function addToCartModal() {
    const form = document.getElementById('addToCartForm');
    const formData = new FormData(form);
    formData.append('action', 'add_to_cart');
    
    // Get selected options
    const options = {};
    if (formData.has('size')) options.size = formData.get('size');
    if (formData.has('color')) options.color = formData.get('color');
    if (formData.has('finish')) options.finish = formData.get('finish');
    
    // Add to cart
    addToCart(
        formData.get('product_id'),
        options,
        formData.get('notes')
    );
    
    // Close modal
    closeModal();
}

// Close modal when clicking the X or outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('productModal');
    const closeBtn = document.querySelector('.close');
    
    if (closeBtn) {
        closeBtn.onclick = function() {
            closeModal();
        }
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
});

// Cart quantity functions
function changeQuantity(button, change) {
    const input = button.parentNode.querySelector('.qty-input');
    const currentValue = parseInt(input.value);
    const newValue = Math.max(1, currentValue + change);
    
    input.value = newValue;
}

// Login/Register form toggle
function showRegisterForm() {
    document.querySelector('.login-form').style.display = 'none';
    document.querySelector('.register-form').style.display = 'block';
}

function showLoginForm() {
    document.querySelector('.register-form').style.display = 'none';
    document.querySelector('.login-form').style.display = 'block';
}

// Add to cart function
function addToCart(productId, options = {}, notes = '') {
    const quantity = document.querySelector('.qty-input') ? 
                    parseInt(document.querySelector('.qty-input').value) : 1;
    
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('options', JSON.stringify(options));
    formData.append('notes', notes);
    
    fetch('/isabelle-prints/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count in header
            updateCartCount();
            // Show success message
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification('Error adding product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding product to cart', 'error');
    });
}

// Update cart count in header
function updateCartCount() {
    fetch('/isabelle-prints/api/cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.querySelector('.cart-count');
            if (data.count > 0) {
                if (cartCountElement) {
                    cartCountElement.textContent = data.count;
                } else {
                    // Create cart count element if it doesn't exist
                    const cartIcon = document.querySelector('.cart-icon');
                    const countSpan = document.createElement('span');
                    countSpan.className = 'cart-count';
                    countSpan.textContent = data.count;
                    cartIcon.appendChild(countSpan);
                }
            } else {
                if (cartCountElement) {
                    cartCountElement.remove();
                }
            }
        });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 4px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else {
        notification.style.backgroundColor = '#007bff';
    }
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    updateCartCount();
    
    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});