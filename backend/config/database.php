<?php
// Database configuration
class Database {
    private $host = 'localhost';
    private $db_name = 'hazubjvk_watches';
    private $username = 'hazubjvk_watches';
    private $password = '0dr_;8oDX+Pr';
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            return $this->conn;
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed: " . $exception->getMessage());
        }
    }
}

// Create database and tables if they don't exist
function initializeDatabase() {
    try {
        // Connect without database name first
        $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS luxury_watches CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE luxury_watches");
        
        // Create tables
        createTables($pdo);
        
        return true;
    } catch(PDOException $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    }
}

function createTables($pdo) {
    // Categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Products table
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Orders table
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Order items table
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Newsletter subscribers table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            status ENUM('active', 'unsubscribed') DEFAULT 'active',
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Insert default categories
    $pdo->exec("
        INSERT IGNORE INTO categories (id, name, slug, description) VALUES
        (1, 'Montres Homme', 'men', 'Collection de montres de luxe pour homme'),
        (2, 'Montres Femme', 'women', 'Collection de montres élégantes pour femme'),
        (3, 'Nouveautés', 'new', 'Nos dernières acquisitions')
    ");
    
    // Insert sample products
    insertSampleProducts($pdo);
    
    // Insert default settings
    $pdo->exec("
        INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
        ('site_name', 'Luxury Watches'),
        ('contact_email', 'info@luxurywatches.ma'),
        ('contact_phone', '+212 6XX XXX XXX'),
        ('whatsapp_number', '+212XXXXXXXXX'),
        ('facebook_pixel_id', 'YOUR_PIXEL_ID'),
        ('google_analytics_id', 'GA_MEASUREMENT_ID')
    ");
}

function insertSampleProducts($pdo) {
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO products (
            id, name, slug, description, price, old_price, image, images, category_id, features, is_new, stock_quantity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $products = [
        [
            1,
            'Montre Classique Or',
            'montre-classique-or',
            'Élégante montre classique en or avec bracelet en cuir véritable. Mouvement automatique de haute précision.',
            2500.00,
            3200.00,
            'https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400',
            json_encode([
                'https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400',
                'https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400'
            ]),
            1, // men category
            json_encode([
                'Mouvement' => 'Automatique',
                'Matériau' => 'Or 18K',
                'Résistance à l\'eau' => '50m',
                'Garantie' => '2 ans'
            ]),
            true,
            5
        ],
        [
            2,
            'Montre Sport Acier',
            'montre-sport-acier',
            'Montre sportive en acier inoxydable avec chronographe intégré. Parfaite pour les activités sportives.',
            1800.00,
            2200.00,
            'https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400',
            json_encode([
                'https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400',
                'https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=400'
            ]),
            1, // men category
            json_encode([
                'Mouvement' => 'Quartz',
                'Matériau' => 'Acier inoxydable',
                'Résistance à l\'eau' => '100m',
                'Garantie' => '3 ans'
            ]),
            false,
            8
        ],
        [
            3,
            'Montre Femme Diamant',
            'montre-femme-diamant',
            'Montre élégante pour femme ornée de diamants véritables. Design raffiné et mouvement de précision.',
            3200.00,
            4000.00,
            'https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400',
            json_encode([
                'https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400',
                'https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400'
            ]),
            2, // women category
            json_encode([
                'Mouvement' => 'Automatique',
                'Matériau' => 'Or blanc 18K',
                'Diamants' => '0.5 carat',
                'Garantie' => '5 ans'
            ]),
            true,
            3
        ],
        [
            4,
            'Montre Vintage Cuir',
            'montre-vintage-cuir',
            'Montre vintage avec bracelet en cuir artisanal. Style intemporel et finitions soignées.',
            1500.00,
            1900.00,
            'https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400',
            json_encode([
                'https://images.pexels.com/photos/1616804/pexels-photo-1616804.jpeg?auto=compress&cs=tinysrgb&w=400',
                'https://images.pexels.com/photos/1697214/pexels-photo-1697214.jpeg?auto=compress&cs=tinysrgb&w=400'
            ]),
            2, // women category
            json_encode([
                'Mouvement' => 'Mécanique',
                'Matériau' => 'Acier brossé',
                'Bracelet' => 'Cuir véritable',
                'Garantie' => '2 ans'
            ]),
            false,
            6
        ]
    ];
    
    foreach ($products as $product) {
        $stmt->execute($product);
    }
}

// Initialize database on first run
if (!file_exists(__DIR__ . '/.db_initialized')) {
    if (initializeDatabase()) {
        file_put_contents(__DIR__ . '/.db_initialized', date('Y-m-d H:i:s'));
    }
}
?>