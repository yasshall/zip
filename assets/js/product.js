// Product page functionality

let currentProduct = null;
let currentImageIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    initializeProductPage();
});

function initializeProductPage() {
    const productId = getUrlParameter('id');
    const productSlug = getUrlParameter('slug');
    
    if (!productId && !productSlug) {
        showNotification('Produit non trouvé', 'error');
        setTimeout(() => {
            window.location.href = 'products.html';
        }, 2000);
        return;
    }
    
    loadProduct(productId, productSlug);
    initializeQuantityControls();
    initializeProductActions();
}

async function loadProduct(productId, productSlug) {
    try {
        // Wait for products to be loaded in main.js
        if (!window.products || window.products.length === 0) {
            console.log('Products not loaded yet, waiting...');
            // Wait for products to be loaded
            await new Promise(resolve => {
                const checkProducts = () => {
                    if (window.products && window.products.length > 0) {
                        resolve();
                    } else {
                        setTimeout(checkProducts, 100);
                    }
                };
                checkProducts();
            });
        }
        
        if (productId) {
            currentProduct = window.products.find(p => p.id == productId);
        } else if (productSlug) {
            currentProduct = window.products.find(p => p.slug === productSlug);
        }
        
        if (!currentProduct) {
            showNotification('Produit non trouvé', 'error');
            setTimeout(() => {
                window.location.href = 'products.html';
            }, 2000);
            return;
        }
        
        displayProduct();
        loadRelatedProducts();
        
        // Update page title and meta description
        document.title = `${currentProduct.name} - Luxury Watches`;
        
        const metaDescription = document.querySelector('meta[name="description"]');
        if (metaDescription) {
            metaDescription.content = currentProduct.description;
        }
    } catch (error) {
        console.error('Error loading product:', error);
        showNotification('Erreur lors du chargement du produit', 'error');
    }
}

function displayProduct() {
    // Update breadcrumb
    const breadcrumbProduct = document.getElementById('breadcrumb-product');
    if (breadcrumbProduct) {
        breadcrumbProduct.textContent = currentProduct.name;
    }
    
    // Update main image
    const mainImage = document.getElementById('main-product-image');
    if (mainImage) {
        mainImage.src = currentProduct.image;
        mainImage.alt = currentProduct.name;
    }
    
    // Update promotion badge
    const promotionBadge = document.getElementById('promotion-badge');
    if (promotionBadge) {
        promotionBadge.style.display = currentProduct.old_price ? 'block' : 'none';
    }
    
    // Update thumbnails
    displayThumbnails();
    
    // Update product info
    const productTitle = document.getElementById('product-title');
    if (productTitle) {
        productTitle.textContent = currentProduct.name;
    }
    
    // Update pricing
    const oldPrice = document.getElementById('old-price');
    const currentPrice = document.getElementById('current-price');
    const savings = document.getElementById('savings');
    
    if (currentPrice) {
        currentPrice.textContent = formatPrice(currentProduct.price);
    }
    
    if (oldPrice && currentProduct.old_price) {
        oldPrice.textContent = formatPrice(currentProduct.old_price);
        oldPrice.style.display = 'inline';
        
        if (savings) {
            const savingsAmount = currentProduct.old_price - currentProduct.price;
            const savingsPercent = Math.round((savingsAmount / currentProduct.old_price) * 100);
            savings.textContent = `Économisez ${formatPrice(savingsAmount)} (${savingsPercent}%)`;
            savings.style.display = 'block';
        }
    }
    
    // Update description
    const productDescription = document.getElementById('product-description');
    if (productDescription) {
        productDescription.textContent = currentProduct.description;
    }
    
    // Update features
    displayFeatures();
}

function displayThumbnails() {
    const thumbnailsContainer = document.getElementById('thumbnail-images');
    if (!thumbnailsContainer || !currentProduct.images) return;
    
    // Ensure images is an array
    let images = currentProduct.images;
    if (typeof images === 'string') {
        try {
            images = JSON.parse(images);
        } catch (e) {
            images = [currentProduct.image];
        }
    }
    
    if (!Array.isArray(images) || images.length === 0) {
        images = [currentProduct.image];
    }
    
    thumbnailsContainer.innerHTML = images.map((image, index) => `
        <div class="thumbnail ${index === 0 ? 'active' : ''}" onclick="changeMainImage(${index})">
            <img src="${image}" alt="${currentProduct.name} ${index + 1}" loading="lazy">
        </div>
    `).join('');
}

function changeMainImage(index) {
    if (!currentProduct.images) return;
    
    // Ensure images is an array
    let images = currentProduct.images;
    if (typeof images === 'string') {
        try {
            images = JSON.parse(images);
        } catch (e) {
            images = [currentProduct.image];
        }
    }
    
    if (!Array.isArray(images) || images.length === 0) {
        images = [currentProduct.image];
    }
    
    if (!images[index]) return;
    
    currentImageIndex = index;
    
    const mainImage = document.getElementById('main-product-image');
    if (mainImage) {
        mainImage.style.opacity = '0';
        setTimeout(() => {
            mainImage.src = images[index];
            mainImage.style.opacity = '1';
        }, 150);
    }
    
    // Update active thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });
}

function displayFeatures() {
    const featuresContainer = document.getElementById('product-features');
    if (!featuresContainer || !currentProduct.features) return;
    
    // Ensure features is an object
    let features = currentProduct.features;
    if (typeof features === 'string') {
        try {
            features = JSON.parse(features);
        } catch (e) {
            features = {};
        }
    }
    
    if (!features || typeof features !== 'object') {
        features = {};
    }
    
    featuresContainer.innerHTML = Object.entries(features).map(([key, value]) => `
        <li>
            <span>${key}</span>
            <span>${value}</span>
        </li>
    `).join('');
    
    // If no features, add a default message
    if (Object.keys(features).length === 0) {
        featuresContainer.innerHTML = '<li><span>Aucune caractéristique disponible</span></li>';
    }
}

function initializeQuantityControls() {
    const quantityInput = document.getElementById('quantity');
    const quantityMinus = document.getElementById('quantity-minus');
    const quantityPlus = document.getElementById('quantity-plus');
    
    if (quantityMinus) {
        quantityMinus.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
    }
    
    if (quantityPlus) {
        quantityPlus.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue < 10) {
                quantityInput.value = currentValue + 1;
            }
        });
    }
    
    if (quantityInput) {
        quantityInput.addEventListener('change', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) value = 1;
            if (value > 10) value = 10;
            this.value = value;
        });
    }
}

function initializeProductActions() {
    const addToCartBtn = document.getElementById('add-to-cart');
    const buyNowBtn = document.getElementById('buy-now');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            if (!currentProduct) {
                showNotification('Produit non disponible', 'error');
                return;
            }
            
            const quantity = parseInt(document.getElementById('quantity').value);
            addToCart(currentProduct.id, quantity);
        });
    }
    
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            if (!currentProduct) {
                showNotification('Produit non disponible', 'error');
                return;
            }
            
            const quantity = parseInt(document.getElementById('quantity').value);
            addToCart(currentProduct.id, quantity);
            
            // Redirect to checkout
            setTimeout(() => {
                window.location.href = 'checkout.html';
            }, 500);
        });
    }
}

function loadRelatedProducts() {
    const relatedProductsContainer = document.getElementById('related-products');
    if (!relatedProductsContainer || !currentProduct) return;
    
    // Get products from same category, excluding current product
    const relatedProducts = window.products
        .filter(p => p.id !== currentProduct.id && 
                    (p.category === currentProduct.category || 
                     p.category_slug === currentProduct.category_slug))
        .slice(0, 4);
    
    if (relatedProducts.length === 0) {
        // If no products in same category, get random products
        const randomProducts = window.products
            .filter(p => p.id !== currentProduct.id)
            .sort(() => 0.5 - Math.random())
            .slice(0, 4);
        relatedProducts.push(...randomProducts);
    }
    
    relatedProductsContainer.innerHTML = relatedProducts.map(product => `
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
                    <a href="product.html?id=${product.id}" class="btn btn-primary">Voir</a>
                </div>
            </div>
        </div>
    `).join('');
}

// Keyboard navigation for image gallery
document.addEventListener('keydown', function(e) {
    if (!currentProduct || !currentProduct.images) return;
    
    // Ensure images is an array
    let images = currentProduct.images;
    if (typeof images === 'string') {
        try {
            images = JSON.parse(images);
        } catch (e) {
            images = [currentProduct.image];
        }
    }
    
    if (!Array.isArray(images) || images.length === 0) {
        images = [currentProduct.image];
    }
    
    if (e.key === 'ArrowLeft') {
        e.preventDefault();
        const newIndex = currentImageIndex > 0 ? currentImageIndex - 1 : images.length - 1;
        changeMainImage(newIndex);
    } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        const newIndex = currentImageIndex < images.length - 1 ? currentImageIndex + 1 : 0;
        changeMainImage(newIndex);
    }
});

// Make function globally available
window.changeMainImage = changeMainImage;