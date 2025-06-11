// Checkout page functionality

let orderData = {};

document.addEventListener('DOMContentLoaded', function() {
    initializeCheckoutPage();
});

function initializeCheckoutPage() {
    // Redirect if cart is empty
    if (cart.length === 0) {
        showNotification('Votre panier est vide', 'warning');
        setTimeout(() => {
            window.location.href = 'cart.html';
        }, 2000);
        return;
    }
    
    displayOrderSummary();
    initializeCheckoutForm();
    initializeFormValidation();
}

function displayOrderSummary() {
    const orderItemsContainer = document.getElementById('order-items');
    const orderSubtotal = document.getElementById('order-subtotal');
    const orderDiscountRow = document.getElementById('order-discount-row');
    const orderDiscount = document.getElementById('order-discount');
    const orderTotal = document.getElementById('order-total');
    
    // Display order items
    if (orderItemsContainer) {
        orderItemsContainer.innerHTML = cart.map(item => `
            <div class="order-item">
                <div class="order-item-image">
                    <img src="${item.image}" alt="${item.name}" loading="lazy">
                </div>
                <div class="order-item-details">
                    <div class="order-item-title">${item.name}</div>
                    <div class="order-item-quantity">Quantité: ${item.quantity}</div>
                </div>
                <div class="order-item-price">${formatPrice(item.price * item.quantity)}</div>
            </div>
        `).join('');
    }
    
    // Calculate totals
    const subtotal = getCartTotal();
    const discount = getCartDiscount();
    const total = subtotal - discount;
    
    // Update summary
    if (orderSubtotal) {
        orderSubtotal.textContent = formatPrice(subtotal);
    }
    
    if (orderDiscountRow && orderDiscount) {
        if (discount > 0) {
            orderDiscountRow.style.display = 'flex';
            orderDiscount.textContent = `-${formatPrice(discount)}`;
        } else {
            orderDiscountRow.style.display = 'none';
        }
    }
    
    if (orderTotal) {
        orderTotal.textContent = formatPrice(total);
    }
}

function initializeCheckoutForm() {
    const checkoutForm = document.getElementById('checkout-form');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Format Moroccan phone number
            if (value.startsWith('212')) {
                value = value.substring(3);
            }
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            
            // Add formatting
            if (value.length >= 9) {
                value = value.substring(0, 9);
                value = `+212 ${value.substring(0, 1)} ${value.substring(1, 3)} ${value.substring(3, 6)} ${value.substring(6)}`;
            } else if (value.length >= 6) {
                value = `+212 ${value.substring(0, 1)} ${value.substring(1, 3)} ${value.substring(3)}`;
            } else if (value.length >= 3) {
                value = `+212 ${value.substring(0, 1)} ${value.substring(1)}`;
            } else if (value.length >= 1) {
                value = `+212 ${value}`;
            }
            
            e.target.value = value;
        });
    }
}

function initializeFormValidation() {
    const form = document.getElementById('checkout-form');
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
    });
}

function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    
    clearFieldError(e);
    
    if (!value) {
        showFieldError(field, 'Ce champ est obligatoire');
        return false;
    }
    
    // Specific validations
    switch(field.type) {
        case 'email':
            if (value && !isValidEmail(value)) {
                showFieldError(field, 'Adresse email invalide');
                return false;
            }
            break;
        case 'tel':
            if (!isValidPhone(value)) {
                showFieldError(field, 'Numéro de téléphone invalide');
                return false;
            }
            break;
    }
    
    return true;
}

function clearFieldError(e) {
    const field = e.target;
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
    field.classList.remove('error');
}

function showFieldError(field, message) {
    field.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.cssText = `
        color: var(--error-color);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    `;
    
    field.parentNode.appendChild(errorElement);
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    // Remove all non-digits
    const digits = phone.replace(/\D/g, '');
    
    // Check if it's a valid Moroccan number
    return digits.length >= 12 && digits.startsWith('212');
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    // Validate all fields
    const requiredFields = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField({ target: field })) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
        return;
    }
    
    // Prepare order data
    orderData = {
        customer: {
            firstName: formData.get('firstName'),
            lastName: formData.get('lastName'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            address: formData.get('address'),
            city: formData.get('city'),
            postalCode: formData.get('postalCode'),
            notes: formData.get('notes')
        },
        items: cart,
        totals: {
            subtotal: getCartTotal(),
            discount: getCartDiscount(),
            total: getCartTotal() - getCartDiscount()
        },
        payment_method: 'cod',
        order_date: new Date().toISOString()
    };
    
    // Update button state
    submitButton.textContent = 'Traitement...';
    submitButton.disabled = true;
    
    try {
        const response = await fetch('backend/api/orders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Store order data for thank you page
            localStorage.setItem('lastOrder', JSON.stringify({
                orderNumber: data.order_number,
                ...orderData
            }));
            
            // Clear cart
            clearCart();
            
            // Facebook Pixel tracking
            if (typeof fbq !== 'undefined') {
                fbq('track', 'Purchase', {
                    value: orderData.totals.total,
                    currency: 'MAD',
                    content_ids: cart.map(item => item.id),
                    content_type: 'product'
                });
            }
            
            // Google Analytics tracking
            if (typeof gtag !== 'undefined') {
                gtag('event', 'purchase', {
                    transaction_id: data.order_number,
                    value: orderData.totals.total,
                    currency: 'MAD',
                    items: cart.map(item => ({
                        item_id: item.id,
                        item_name: item.name,
                        category: 'watches',
                        quantity: item.quantity,
                        price: item.price
                    }))
                });
            }
            
            // Redirect to thank you page
            window.location.href = 'thank-you.html';
            
        } else {
            throw new Error(data.message || 'Erreur lors de la commande');
        }
        
    } catch (error) {
        console.error('Order error:', error);
        showNotification('Erreur lors de la commande. Veuillez réessayer.', 'error');
        
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    }
}

// Add CSS for form validation
const style = document.createElement('style');
style.textContent = `
    .form-group input.error,
    .form-group textarea.error,
    .form-group select.error {
        border-color: var(--error-color);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    .field-error {
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .checkout-form {
        position: relative;
    }
    
    .checkout-form::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 10;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        font-weight: 600;
    }
    
    .checkout-form.processing::before {
        display: flex;
        content: 'Traitement de votre commande...';
    }
`;
document.head.appendChild(style);