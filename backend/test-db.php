<?php
// Database connection test
header('Content-Type: application/json');

try {
    require_once 'config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    // Test basic query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if (!$result) {
        throw new Exception('Database query failed');
    }
    
    // Test products table
    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    
    // Test categories table
    $stmt = $db->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $stmt->fetch()['count'];
    
    // Get sample products
    $stmt = $db->query("SELECT id, name, price, is_active FROM products LIMIT 3");
    $sampleProducts = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'data' => [
            'products_count' => $productCount,
            'categories_count' => $categoryCount,
            'sample_products' => $sampleProducts,
            'php_version' => phpversion(),
            'server_time' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug_info' => [
            'php_version' => phpversion(),
            'server_time' => date('Y-m-d H:i:s'),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ]
    ]);
}
?>