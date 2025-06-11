-- Fix Database Structure for Luxury Watches Store
-- This SQL file addresses issues with products and categories not displaying

-- Make sure we're using the correct database
USE luxury_watches;

-- Fix categories table if it doesn't exist
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fix products table if it doesn't exist
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2) NULL,
    image VARCHAR(500),
    images JSON,
    category_id INT,
    features JSON,
    is_new BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories if they don't exist
INSERT IGNORE INTO categories (id, name, slug, description) VALUES
(1, 'Montres Homme', 'men', 'Collection de montres de luxe pour homme'),
(2, 'Montres Femme', 'women', 'Collection de montres élégantes pour femme'),
(3, 'Nouveautés', 'new', 'Nos dernières acquisitions');

-- Insert sample products if they don't exist
INSERT IGNORE INTO products (
    id, name, slug, description, price, old_price, image, images, category_id, features, is_new, stock_quantity
) VALUES 
(1, 'Montre Classique Or', 'montre-classique-or', 'Élégante montre classique en or avec bracelet en cuir véritable. Mouvement automatique de haute précision.', 2500.00, 3200.00, 'https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400', 
'["https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400"]', 
1, '{"Mouvement": "Automatique", "Matériau": "Or 18K", "Résistance à l\'eau": "50m", "Garantie": "2 ans"}', 
true, 5),

(2, 'Montre Sport Acier', 'montre-sport-acier', 'Montre sportive en acier inoxydable avec chronographe intégré. Parfaite pour les activités sportives.', 1800.00, 2200.00, 'https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400', 
'["https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400"]', 
1, '{"Mouvement": "Quartz", "Matériau": "Acier inoxydable", "Résistance à l\'eau": "100m", "Garantie": "3 ans"}', 
false, 8),

(3, 'Montre Femme Diamant', 'montre-femme-diamant', 'Montre élégante pour femme ornée de diamants véritables. Design raffiné et mouvement de précision.', 3200.00, 4000.00, 'https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400', 
'["https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400"]', 
2, '{"Mouvement": "Automatique", "Matériau": "Or blanc 18K", "Diamants": "0.5 carat", "Garantie": "5 ans"}', 
true, 3),

(4, 'Montre Vintage Cuir', 'montre-vintage-cuir', 'Montre vintage avec bracelet en cuir artisanal. Style intemporel et finitions soignées.', 1500.00, 1900.00, 'https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400', 
'["https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400"]', 
2, '{"Mouvement": "Mécanique", "Matériau": "Acier brossé", "Bracelet": "Cuir véritable", "Garantie": "2 ans"}', 
false, 6);

-- Fix any existing products with NULL images or features
UPDATE products SET 
    images = '[]' 
WHERE images IS NULL OR images = '';

UPDATE products SET 
    features = '{}' 
WHERE features IS NULL OR features = '';

-- Add category_slug field to products for easier filtering
-- This is a view that will help with product filtering
CREATE OR REPLACE VIEW product_view AS
SELECT 
    p.*,
    c.name AS category_name,
    c.slug AS category_slug
FROM 
    products p
LEFT JOIN 
    categories c ON p.category_id = c.id
WHERE 
    p.is_active = 1;

-- Fix any issues with order tables
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_email VARCHAR(255),
    customer_address TEXT NOT NULL,
    customer_city VARCHAR(100) NOT NULL,
    customer_postal_code VARCHAR(20),
    notes TEXT,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cod',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_is_new ON products(is_new);
CREATE INDEX IF NOT EXISTS idx_products_slug ON products(slug);
CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug);

-- Fix settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings if they don't exist
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'Luxury Watches'),
('contact_email', 'info@luxurywatches.ma'),
('contact_phone', '+212 6XX XXX XXX'),
('whatsapp_number', '+212XXXXXXXXX'),
('facebook_pixel_id', 'YOUR_PIXEL_ID'),
('google_analytics_id', 'GA_MEASUREMENT_ID');

-- Fix newsletter subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;