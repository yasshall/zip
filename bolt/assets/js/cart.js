// Cart page functionality

document.addEventListener('DOMContentLoaded', function() {
    initializeCartPage();
});

function initializeCartPage() {
    displayCartItems();
    updateCartSummary();
    initializeCartActions();
}

function displayCartItems() {
    const cartItemsContainer = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    const cartSummary = document.getElementById('cart-summary');
    
    if (cart.length === 0) {
        if (emptyCart) emptyCart.style.display = 'block';
        if (cartSummary) cartSummary.style.display = 'none';
        if (cartItemsContainer) cartItemsContainer.innerHTML = '';
        return;
    }
    
    if (emptyCart) emptyCart.style.display = 'none';
    if (cartSummary) cartSummary.style.display = 'block';
    
    if (cartItemsContainer) {
        cartItemsContainer.innerHTML = cart.map(item => `
            <div class="cart-item" data-product-id="${item.id}">
                <div class="cart-item-image">
                    <img src="${item.image}" alt="${item.name}" loading="lazy">
                </div>
                <div class="cart-item-details">
                    <h3 class="cart-item-title">${item.name}</h3>
                    <div class="cart-item-price">${formatPrice(item.price)}</div>
                    <div class="cart-item-actions">
                        <div class="cart-item-quantity">
                            <label>Quantité:</label>
                            <div class="quantity-controls">
                                <button type="button" onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})">-</button>
                                <input type="number" value="${item.quantity}" min="1" max="10" 
                                       onchange="updateCartQuantity(${item.id}, this.value)">
                                <button type="button" onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})">+</button>
                            </div>
                        </div>
                        <button class="cart-item-remove" onclick="removeCartItem(${item.id})" title="Supprimer">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3,6 5,6 21,6"/>
                                <path d="m19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6m3,0V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

function updateCartSummary() {
    const subtotal = getCartTotal();
    const discount = getCartDiscount();
    const total = subtotal - discount;
    
    const subtotalElement = document.getElementById('subtotal');
    const discountRow = document.getElementById('discount-row');
    const discountAmount = document.getElementById('discount-amount');
    const totalElement = document.getElementById('total');
    
    if (subtotalElement) {
        subtotalElement.textContent = formatPrice(subtotal);
    }
    
    if (discountRow && discountAmount) {
        if (discount > 0) {
            discountRow.style.display = 'flex';
            discountAmount.textContent = `-${formatPrice(discount)}`;
        } else {
            discountRow.style.display = 'none';
        }
    }
    
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
    }
}

function initializeCartActions() {
    const proceedCheckoutBtn = document.getElementById('proceed-checkout');
    
    if (proceedCheckoutBtn) {
        proceedCheckoutBtn.addEventListener('click', function() {
            if (cart.length === 0) {
                showNotification('Votre panier est vide', 'warning');
                return;
            }
            
            window.location.href = 'checkout.html';
        });
    }
}

function updateCartQuantity(productId, newQuantity) {
    newQuantity = parseInt(newQuantity);
    
    if (newQuantity <= 0) {
        removeCartItem(productId);
        return;
    }
    
    if (newQuantity > 10) {
        newQuantity = 10;
        showNotification('Quantité maximale: 10', 'warning');
    }
    
    updateCartItemQuantity(productId, newQuantity);
    displayCartItems();
    updateCartSummary();
    
    // Update the input value in case it was clamped
    const input = document.querySelector(`[data-product-id="${productId}"] input[type="number"]`);
    if (input) {
        input.value = newQuantity;
    }
}

function removeCartItem(productId) {
    // Add confirmation for item removal
    const item = cart.find(item => item.id == productId);
    if (!item) return;
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer "${item.name}" du panier?`)) {
        removeFromCart(productId);
        displayCartItems();
        updateCartSummary();
        
        // Add animation for removal
        const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
        if (cartItem) {
            cartItem.style.transform = 'translateX(-100%)';
            cartItem.style.opacity = '0';
            setTimeout(() => {
                displayCartItems();
            }, 300);
        }
    }
}

// Auto-save cart changes
function autoSaveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

// Add animation for cart updates
function animateCartUpdate() {
    const cartSummary = document.getElementById('cart-summary');
    if (cartSummary) {
        cartSummary.style.transform = 'scale(1.02)';
        setTimeout(() => {
            cartSummary.style.transform = 'scale(1)';
        }, 200);
    }
}

// Override the global functions for cart page specific behavior
const originalUpdateCartItemQuantity = window.updateCartItemQuantity;
window.updateCartItemQuantity = function(productId, quantity) {
    originalUpdateCartItemQuantity(productId, quantity);
    autoSaveCart();
    animateCartUpdate();
};

const originalRemoveFromCart = window.removeFromCart;
window.removeFromCart = function(productId) {
    originalRemoveFromCart(productId);
    autoSaveCart();
    animateCartUpdate();
};

// Make functions globally available
window.updateCartQuantity = updateCartQuantity;
window.removeCartItem = removeCartItem;

// Add CSS for cart animations
const style = document.createElement('style');
style.textContent = `
    .cart-item {
        transition: all 0.3s ease;
    }
    
    .cart-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .quantity-controls {
        transition: all 0.3s ease;
    }
    
    .cart-summary {
        transition: transform 0.2s ease;
    }
    
    .cart-item-remove:hover {
        color: #DC2626;
        transform: scale(1.1);
    }
    
    @media (max-width: 768px) {
        .cart-item-actions {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .cart-item-quantity {
            justify-content: space-between;
        }
    }
`;
document.head.appendChild(style);