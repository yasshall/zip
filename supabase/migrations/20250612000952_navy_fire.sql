-- Fix Database Structure for Luxury Watches Store
-- This SQL file addresses issues with products and categories not displaying

-- Make sure we're using the correct database
USE hazubjvk_watches;

-- Fix categories table if needed
ALTER TABLE categories MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE categories MODIFY COLUMN name varchar(100) NOT NULL;
ALTER TABLE categories MODIFY COLUMN slug varchar(100) NOT NULL;

-- Fix products table if needed
ALTER TABLE products MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE products MODIFY COLUMN name varchar(255) NOT NULL;
ALTER TABLE products MODIFY COLUMN slug varchar(255) NOT NULL;
ALTER TABLE products MODIFY COLUMN price decimal(10,2) NOT NULL;

-- Fix any existing products with NULL images or features
UPDATE products SET 
    images = '[]' 
WHERE images IS NULL OR images = '';

UPDATE products SET 
    features = '{}' 
WHERE features IS NULL OR features = '';

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
1, 5),

(2, 'Montre Sport Acier', 'montre-sport-acier', 'Montre sportive en acier inoxydable avec chronographe intégré. Parfaite pour les activités sportives.', 1800.00, 2200.00, 'https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400', 
'["https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400"]', 
1, '{"Mouvement": "Quartz", "Matériau": "Acier inoxydable", "Résistance à l\'eau": "100m", "Garantie": "3 ans"}', 
0, 8),

(3, 'Montre Femme Diamant', 'montre-femme-diamant', 'Montre élégante pour femme ornée de diamants véritables. Design raffiné et mouvement de précision.', 3200.00, 4000.00, 'https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400', 
'["https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400"]', 
2, '{"Mouvement": "Automatique", "Matériau": "Or blanc 18K", "Diamants": "0.5 carat", "Garantie": "5 ans"}', 
1, 3),

(4, 'Montre Vintage Cuir', 'montre-vintage-cuir', 'Montre vintage avec bracelet en cuir artisanal. Style intemporel et finitions soignées.', 1500.00, 1900.00, 'https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400', 
'["https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400", "https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400"]', 
2, '{"Mouvement": "Mécanique", "Matériau": "Acier brossé", "Bracelet": "Cuir véritable", "Garantie": "2 ans"}', 
0, 6);

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_is_new ON products(is_new);
CREATE INDEX IF NOT EXISTS idx_products_slug ON products(slug);
CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug);

-- Fix any issues with JSON columns
-- Make sure MySQL/MariaDB can handle JSON properly
ALTER TABLE products MODIFY COLUMN images LONGTEXT;
ALTER TABLE products MODIFY COLUMN features LONGTEXT;

-- Add a timestamp to track when this fix was applied
INSERT INTO settings (setting_key, setting_value) 
VALUES ('database_fix_applied', NOW())
ON DUPLICATE KEY UPDATE setting_value = NOW();