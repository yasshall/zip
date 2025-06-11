// Thank you page functionality

document.addEventListener('DOMContentLoaded', function() {
    initializeThankYouPage();
});

function initializeThankYouPage() {
    displayOrderDetails();
    loadRelatedProducts();
    
    // Clear the stored order data after a delay
    setTimeout(() => {
        localStorage.removeItem('lastOrder');
    }, 300000); // 5 minutes
}

function displayOrderDetails() {
    const lastOrder = JSON.parse(localStorage.getItem('lastOrder'));
    
    if (!lastOrder) {
        // If no order data, redirect to home
        showNotification('Aucune commande trouvée', 'warning');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 2000);
        return;
    }
    
    // Update order number
    const orderNumber = document.getElementById('order-number');
    if (orderNumber) {
        orderNumber.textContent = `#${lastOrder.orderNumber}`;
    }
    
    // Update order date
    const orderDate = document.getElementById('order-date');
    if (orderDate) {
        const date = new Date(lastOrder.order_date);
        orderDate.textContent = date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Update order total
    const orderTotalDisplay = document.getElementById('order-total-display');
    if (orderTotalDisplay) {
        orderTotalDisplay.textContent = formatPrice(lastOrder.totals.total);
    }
    
    // Update customer info in the page title
    document.title = `Commande Confirmée #${lastOrder.orderNumber} - Luxury Watches`;
    
    // Send confirmation email (if email provided)
    if (lastOrder.customer.email) {
        sendConfirmationEmail(lastOrder);
    }
}

async function sendConfirmationEmail(orderData) {
    try {
        await fetch('backend/api/send-confirmation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        });
    } catch (error) {
        console.error('Error sending confirmation email:', error);
    }
}

function loadRelatedProducts() {
    const relatedProductsContainer = document.getElementById('related-products');
    if (!relatedProductsContainer) return;
    
    // Get random products for recommendations
    const randomProducts = products
        .sort(() => 0.5 - Math.random())
        .slice(0, 4);
    
    relatedProductsContainer.innerHTML = randomProducts.map(product => `
        <div class="product-card">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}" loading="lazy">
                ${product.old_price ? '<div class="promotion-badge"><span>Promotion</span></div>' : ''}
            </div>
            <div class="product-info">
                <h3 class="product-title">${product.name}</h3>
                <div class="product-pricing">
                    ${product.old_price ? `<span class="old-price">${formatPrice(product.old_price)}</span>` : ''}
                    <span class="current-price">${formatPrice(product.price)}</span>
                </div>
                <div class="product-actions">
                    <button class="btn btn-secondary" onclick="addToCart(${product.id})">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="m16 10-4 4-4-4"/>
                        </svg>
                        Ajouter
                    </button>
                    <a href="product.html?slug=${product.slug}" class="btn btn-primary">Voir</a>
                </div>
            </div>
        </div>
    `).join('');
    
    // Add animation to related products
    addProductAnimations();
}

function addProductAnimations() {
    const productCards = document.querySelectorAll('#related-products .product-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });
    
    productCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
}

// Add celebration animation
function addCelebrationAnimation() {
    const successIcon = document.querySelector('.success-icon');
    if (successIcon) {
        successIcon.style.animation = 'celebrate 0.6s ease-in-out';
    }
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes celebrate {
        0% {
            transform: scale(0.8);
            opacity: 0;
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .thank-you-content {
        animation: fadeInUp 0.8s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .step {
        animation: slideInLeft 0.6s ease-out;
        animation-fill-mode: both;
    }
    
    .step:nth-child(1) { animation-delay: 0.2s; }
    .step:nth-child(2) { animation-delay: 0.4s; }
    .step:nth-child(3) { animation-delay: 0.6s; }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .contact-method {
        transition: all 0.3s ease;
    }
    
    .contact-method:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
`;
document.head.appendChild(style);

// Run celebration animation on load
setTimeout(addCelebrationAnimation, 500);