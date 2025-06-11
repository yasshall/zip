-- Luxury Watches Database Setup
-- Run this SQL if automatic database creation fails

CREATE DATABASE IF NOT EXISTS luxury_watches CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE luxury_watches;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
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
);

-- Orders table
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order items table
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
);

-- Newsletter subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT IGNORE INTO categories (name, slug, description) VALUES
('Montres Homme', 'men', 'Collection de montres de luxe pour homme'),
('Montres Femme', 'women', 'Collection de montres élégantes pour femme'),
('Nouveautés', 'new', 'Nos dernières acquisitions');

-- Insert sample products
INSERT IGNORE INTO products (
    id, name, slug, description, price, old_price, image, images, category_id, features, is_new
) VALUES 
(1, 'Montre Classique Or', 'montre-classique-or', 'Élégante montre classique en or avec bracelet en cuir véritable. Mouvement automatique de haute précision.', 2500.00, 3200.00, 'https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400', '["https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400"]', 1, '{"Mouvement": "Automatique", "Matériau": "Or 18K", "Résistance à l\'eau": "50m", "Garantie": "2 ans"}', true),
(2, 'Montre Sport Acier', 'montre-sport-acier', 'Montre sportive en acier inoxydable avec chronographe intégré. Parfaite pour les activités sportives.', 1800.00, 2200.00, 'https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400', '["https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400"]', 1, '{"Mouvement": "Quartz", "Matériau": "Acier inoxydable", "Résistance à l\'eau": "100m", "Garantie": "3 ans"}', false),
(3, 'Montre Femme Diamant', 'montre-femme-diamant', 'Montre élégante pour femme ornée de diamants véritables. Design raffiné et mouvement de précision.', 3200.00, 4000.00, 'https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400', '["https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400"]', 2, '{"Mouvement": "Automatique", "Matériau": "Or blanc 18K", "Diamants": "0.5 carat", "Garantie": "5 ans"}', true),
(4, 'Montre Vintage Cuir', 'montre-vintage-cuir', 'Montre vintage avec bracelet en cuir artisanal. Style intemporel et finitions soignées.', 1500.00, 1900.00, 'https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400', '["https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400"]', 2, '{"Mouvement": "Mécanique", "Matériau": "Acier brossé", "Bracelet": "Cuir véritable", "Garantie": "2 ans"}', false);

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'Luxury Watches'),
('contact_email', 'info@luxurywatches.ma'),
('contact_phone', '+212 6XX XXX XXX'),
('whatsapp_number', '+212XXXXXXXXX'),
('facebook_pixel_id', 'YOUR_PIXEL_ID'),
('google_analytics_id', 'GA_MEASUREMENT_ID');