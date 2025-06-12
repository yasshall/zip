// Main JavaScript file for Luxury Watch Store

// Global variables
let cart = JSON.parse(localStorage.getItem('cart')) || [];
let products = [];

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// App initialization
async function initializeApp() {
    await loadProducts();
    updateCartCount();
    initializeNavigation();
    initializeWhatsApp();
    initializeNewsletterForm();
    
    // Load page-specific functionality
    const currentPage = getCurrentPage();
    
    switch(currentPage) {
        case 'index':
            initializeHomePage();
            break;
        case 'products':
            initializeProductsPage();
            break;
        case 'product':
            initializeProductPage();
            break;
        case 'cart':
            initializeCartPage();
            break;
        case 'checkout':
            initializeCheckoutPage();
            break;
        case 'thank-you':
            initializeThankYouPage();
            break;
    }
}

// Get current page from URL
function getCurrentPage() {
    const path = window.location.pathname;
    const page = path.split('/').pop().split('.')[0];
    return page || 'index';
}

// Load products from API
async function loadProducts() {
    try {
        console.log('Loading products from API...');
        const response = await fetch('backend/api/products.php');
        const data = await response.json();
        
        console.log('API Response:', data);
        
        if (data.success && data.products && data.products.length > 0) {
            products = data.products;
            console.log('Products loaded successfully:', products.length);
        } else {
            console.warn('API returned success: false or no products. Using demo data.');
            products = getDemoProducts();
        }
    } catch (error) {
        console.error('Error loading products:', error);
        products = getDemoProducts();
    }
}

// Demo products data
function getDemoProducts() {
    console.log('Using demo products data');
    return [
        {
            id: 1,
            name: "Montre Classique Or",
            slug: "montre-classique-or",
            price: 2500,
            old_price: 3200,
            description: "Élégante montre classique en or avec bracelet en cuir véritable. Mouvement automatique de haute précision.",
            image: "https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400",
            images: [
                "https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400",
                "https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400"
            ],
            category: "men",
            category_slug: "men",
            category_id: 1,
            is_new: true,
            features: {
                "Mouvement": "Automatique",
                "Matériau": "Or 18K",
                "Résistance à l'eau": "50m",
                "Garantie": "2 ans"
            }
        },
        {
            id: 2,
            name: "Montre Sport Acier",
            slug: "montre-sport-acier",
            price: 1800,
            old_price: 2200,
            description: "Montre sportive en acier inoxydable avec chronographe intégré. Parfaite pour les activités sportives.",
            image: "https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400",
            images: [
                "https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400",
                "https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400"
            ],
            category: "men",
            category_slug: "men",
            category_id: 1,
            is_new: false,
            features: {
                "Mouvement": "Quartz",
                "Matériau": "Acier inoxydable",
                "Résistance à l'eau": "100m",
                "Garantie": "3 ans"
            }
        },
        {
            id: 3,
            name: "Montre Femme Diamant",
            slug: "montre-femme-diamant",
            price: 3200,
            old_price: 4000,
            description: "Montre élégante pour femme ornée de diamants véritables. Design raffiné et mouvement de précision.",
            image: "https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400",
            images: [
                "https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400",
                "https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400"
            ],
            category: "women",
            category_slug: "women",
            category_id: 2,
            is_new: true,
            features: {
                "Mouvement":  "Automatique",
                "Matériau": "Or blanc 18K",
                "Diamants": "0.5 carat",
                "Garantie": "5 ans"
            }
        },
        {
            id: 4,
            name: "Montre Vintage Cuir",
            slug: "montre-vintage-cuir",
            price: 1500,
            old_price: 1900,
            description: "Montre vintage avec bracelet en cuir artisanal. Style intemporel et finitions soignées.",
            image: "https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400",
            images: [
                "https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400",
                "https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400"
            ],
            category: "women",
            category_slug: "women",
            category_id: 2,
            is_new: false,
            features: {
                "Mouvement": "Mécanique",
                "Matériau": "Acier brossé",
                "Bracelet": "Cuir véritable",
                "Garantie": "2 ans"
            }
        }
    ];
}

// Navigation functionality
function initializeNavigation() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (navMenu && navToggle && !navToggle.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('active');
        }
    });
}

// WhatsApp functionality
function initializeWhatsApp() {
    const whatsappBtn = document.getElementById('whatsapp-btn');
    if (whatsappBtn) {
        // Add animation on scroll
        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > lastScrollTop) {
                whatsappBtn.style.transform = 'translateY(100px)';
            } else {
                whatsappBtn.style.transform = 'translateY(0)';
            }
            lastScrollTop = scrollTop;
        });
    }
}

// Newsletter form
function initializeNewsletterForm() {
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[type="email"]').value;
            const button = this.querySelector('button');
            const originalText = button.textContent;
            
            button.textContent = 'Inscription...';
            button.disabled = true;
            
            try {
                const response = await fetch('backend/api/newsletter.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Inscription réussie! Merci de vous être inscrit.', 'success');
                    this.reset();
                } else {
                    showNotification(data.message || 'Erreur lors de l\'inscription', 'error');
                }
            } catch (error) {
                console.error('Newsletter error:', error);
                showNotification('Erreur lors de l\'inscription', 'error');
            }
            
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}

// Cart functionality
function updateCartCount() {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
        
        // Add animation
        cartCount.style.transform = 'scale(1.2)';
        setTimeout(() => {
            cartCount.style.transform = 'scale(1)';
        }, 200);
    }
}

function addToCart(productId, quantity = 1) {
    const product = products.find(p => p.id == productId);
    if (!product) {
        console.error('Product not found:', productId);
        return;
    }
    
    const existingItem = cart.find(item => item.id == productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: quantity
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showNotification('Produit ajouté au panier!', 'success');
    
    // Facebook Pixel tracking
    if (typeof fbq !== 'undefined') {
        fbq('track', 'AddToCart', {
            content_ids: [productId],
            content_type: 'product',
            value: product.price,
            currency: 'MAD'
        });
    }
    
    // Google Analytics tracking
    if (typeof gtag !== 'undefined') {
        gtag('event', 'add_to_cart', {
            currency: 'MAD',
            value: product.price,
            items: [{
                item_id: productId,
                item_name: product.name,
                category: product.category,
                quantity: quantity,
                price: product.price
            }]
        });
    }
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id != productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showNotification('Produit retiré du panier', 'info');
}

function updateCartItemQuantity(productId, quantity) {
    const item = cart.find(item => item.id == productId);
    if (item) {
        if (quantity <= 0) {
            removeFromCart(productId);
        } else {
            item.quantity = quantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
        }
    }
}

function getCartTotal() {
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

function getCartDiscount() {
    // 20% discount if more than one item
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    if (totalItems >= 2) {
        return getCartTotal() * 0.2;
    }
    return 0;
}

function clearCart() {
    cart = [];
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

// Utility functions
function formatPrice(price) {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD',
        minimumFractionDigits: 0
    }).format(price);
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span>${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
    `;
    
    notification.querySelector('.notification-content').style.cssText = `
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    `;
    
    notification.querySelector('.notification-close').style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
    
    // Close button functionality
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Page-specific initializations
function initializeHomePage() {
    loadNewArrivals();
}

function loadNewArrivals() {
    const newArrivalsGrid = document.getElementById('new-arrivals-grid');
    if (!newArrivalsGrid) return;
    
    console.log('Loading new arrivals...');
    const newProducts = products.filter(product => product.is_new).slice(0, 4);
    console.log('New products found:', newProducts.length);
    
    if (newProducts.length === 0) {
        newArrivalsGrid.innerHTML = '<p>Aucun nouveau produit disponible pour le moment.</p>';
        return;
    }
    
    newArrivalsGrid.innerHTML = newProducts.map(product => `
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
                <p class="product-description">${product.description}</p>
                <div class="product-actions">
                    <button class="btn btn-secondary" onclick="addToCart(${product.id})">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="m16 10-4 4-4-4"/>
                        </svg>
                        Ajouter
                    </button>
                    <a href="product.html?id=${product.id}" class="btn btn-primary">Voir</a>
                </div>
            </div>
        </div>
    `).join('');
}

// Export functions for global access
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateCartItemQuantity = updateCartItemQuantity;
window.formatPrice = formatPrice;
window.showNotification = showNotification;
window.getDemoProducts = getDemoProducts;