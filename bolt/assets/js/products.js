// Products page functionality

let currentCategory = '';
let currentSort = 'name';
let filteredProducts = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeProductsPage();
});

function initializeProductsPage() {
    // Get category from URL
    currentCategory = getUrlParameter('category') || '';
    
    // Update page title based on category
    updatePageTitle();
    
    // Initialize filters
    initializeFilters();
    
    // Load and display products
    loadAndDisplayProducts();
}

function updatePageTitle() {
    const pageTitle = document.getElementById('page-title');
    const categoryFilter = document.getElementById('category-filter');
    
    if (pageTitle) {
        let title = 'Toutes les Montres';
        
        switch(currentCategory) {
            case 'men':
                title = 'Montres Homme';
                break;
            case 'women':
                title = 'Montres Femme';
                break;
            case 'new':
                title = 'Nouveautés';
                break;
        }
        
        pageTitle.textContent = title;
    }
    
    if (categoryFilter) {
        categoryFilter.value = currentCategory;
    }
}

function initializeFilters() {
    const categoryFilter = document.getElementById('category-filter');
    const sortFilter = document.getElementById('sort-filter');
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            currentCategory = this.value;
            updateURL();
            loadAndDisplayProducts();
        });
    }
    
    if (sortFilter) {
        sortFilter.addEventListener('change', function() {
            currentSort = this.value;
            loadAndDisplayProducts();
        });
    }
}

function updateURL() {
    const url = new URL(window.location);
    if (currentCategory) {
        url.searchParams.set('category', currentCategory);
    } else {
        url.searchParams.delete('category');
    }
    window.history.replaceState({}, '', url);
    updatePageTitle();
}

async function loadAndDisplayProducts() {
    showLoading(true);
    
    try {
        // Build API URL with category parameter
        let apiUrl = 'backend/api/products.php?limit=50';
        if (currentCategory) {
            apiUrl += `&category=${encodeURIComponent(currentCategory)}`;
        }
        
        console.log('Loading products from:', apiUrl); // Debug log
        
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        console.log('API Response:', data); // Debug log
        
        if (data.success && data.products) {
            filteredProducts = data.products;
            
            // Sort products
            sortProducts();
            
            // Display products
            displayProducts();
        } else {
            console.error('API Error:', data.message || 'Unknown error');
            
            // Fallback to demo data if API fails
            console.log('Falling back to demo data...');
            loadFallbackProducts();
        }
    } catch (error) {
        console.error('Error loading products:', error);
        
        // Fallback to demo data
        console.log('Falling back to demo data due to error...');
        loadFallbackProducts();
    }
    
    showLoading(false);
}

function loadFallbackProducts() {
    // Use demo products from main.js if available
    if (typeof getDemoProducts === 'function') {
        const allProducts = getDemoProducts();
        
        // Filter by category if specified
        if (currentCategory) {
            if (currentCategory === 'new') {
                filteredProducts = allProducts.filter(p => p.is_new);
            } else {
                filteredProducts = allProducts.filter(p => p.category === currentCategory);
            }
        } else {
            filteredProducts = allProducts;
        }
        
        // Sort products
        sortProducts();
        
        // Display products
        displayProducts();
    } else {
        // If no demo products available, show empty state
        filteredProducts = [];
        displayProducts();
    }
}

function sortProducts() {
    filteredProducts.sort((a, b) => {
        switch(currentSort) {
            case 'price-low':
                return a.price - b.price;
            case 'price-high':
                return b.price - a.price;
            case 'name':
            default:
                return a.name.localeCompare(b.name);
        }
    });
}

function displayProducts() {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return;
    
    console.log('Displaying products:', filteredProducts.length); // Debug log
    
    if (filteredProducts.length === 0) {
        productsGrid.innerHTML = `
            <div class="no-products">
                <div class="no-products-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="m9 9 6 6"/>
                        <path d="m15 9-6 6"/>
                    </svg>
                </div>
                <h3>Aucun produit trouvé</h3>
                <p>Essayez de modifier vos filtres ou explorez d'autres catégories</p>
                <a href="products.html" class="btn btn-primary">Voir Tous les Produits</a>
            </div>
        `;
        return;
    }
    
    productsGrid.innerHTML = filteredProducts.map(product => `
        <div class="product-card" data-product-id="${product.id}">
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
                    <a href="product/${product.slug || 'product-' + product.id}" class="btn btn-primary">Voir Détails</a>
                </div>
            </div>
        </div>
    `).join('');
    
    // Add intersection observer for animations
    addProductAnimations();
}

function addProductAnimations() {
    const productCards = document.querySelectorAll('.product-card');
    
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
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    productCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
}

function showLoading(show) {
    const loading = document.getElementById('loading');
    const productsGrid = document.getElementById('products-grid');
    
    if (loading) {
        loading.style.display = show ? 'block' : 'none';
    }
    
    if (productsGrid) {
        productsGrid.style.display = show ? 'none' : 'grid';
    }
}

// Add CSS for no products state
const style = document.createElement('style');
style.textContent = `
    .no-products {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        color: var(--neutral-600);
    }
    
    .no-products-icon {
        color: var(--neutral-400);
        margin-bottom: 2rem;
    }
    
    .no-products h3 {
        color: var(--neutral-700);
        margin-bottom: 1rem;
    }
    
    .no-products p {
        margin-bottom: 2rem;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
`;
document.head.appendChild(style);