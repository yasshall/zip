<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGetProducts($db);
            break;
        case 'POST':
            handleCreateProduct($db);
            break;
        case 'PUT':
            handleUpdateProduct($db);
            break;
        case 'DELETE':
            handleDeleteProduct($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Products API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}

function handleGetProducts($db) {
    $category = $_GET['category'] ?? '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $search = $_GET['search'] ?? '';
    $id = $_GET['id'] ?? '';
    $slug = $_GET['slug'] ?? '';
    
    try {
        if ($id || $slug) {
            // Get single product by ID or slug
            if ($id) {
                $stmt = $db->prepare("
                    SELECT p.*, c.name as category_name, c.slug as category_slug 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.id = ? AND p.is_active = 1
                ");
                $stmt->execute([$id]);
            } else {
                $stmt = $db->prepare("
                    SELECT p.*, c.name as category_name, c.slug as category_slug 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.slug = ? AND p.is_active = 1
                ");
                $stmt->execute([$slug]);
            }
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                // Process product data
                $product['images'] = json_decode($product['images'], true) ?: [];
                $product['features'] = json_decode($product['features'], true) ?: [];
                $product['price'] = (float)$product['price'];
                $product['old_price'] = $product['old_price'] ? (float)$product['old_price'] : null;
                $product['is_new'] = (bool)$product['is_new'];
                $product['category'] = $product['category_slug']; // For compatibility
                
                // Ensure images array includes main image
                if (!empty($product['image']) && !in_array($product['image'], $product['images'])) {
                    array_unshift($product['images'], $product['image']);
                }
                
                echo json_encode(['success' => true, 'product' => $product]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
            }
            return;
        }
        
        // Build query for multiple products
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1
        ";
        $params = [];
        
        // Add category filter
        if ($category) {
            if ($category === 'new') {
                $sql .= " AND p.is_new = 1";
            } else {
                $sql .= " AND c.slug = ?";
                $params[] = $category;
            }
        }
        
        // Add search filter
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process products
        foreach ($products as &$product) {
            $product['images'] = json_decode($product['images'], true) ?: [];
            $product['features'] = json_decode($product['features'], true) ?: [];
            $product['price'] = (float)$product['price'];
            $product['old_price'] = $product['old_price'] ? (float)$product['old_price'] : null;
            $product['is_new'] = (bool)$product['is_new'];
            $product['category'] = $product['category_slug']; // Add category field for compatibility
            
            // Ensure images array includes main image
            if (!empty($product['image']) && !in_array($product['image'], $product['images'])) {
                array_unshift($product['images'], $product['image']);
            }
        }
        
        echo json_encode([
            'success' => true, 
            'products' => $products
        ]);
        
    } catch (Exception $e) {
        error_log("Products API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage()]);
    }
}

function handleCreateProduct($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        return;
    }
    
    $required = ['name', 'description', 'price', 'category_id'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            return;
        }
    }
    
    try {
        $slug = generateSlug($input['name'], $db);
        
        $stmt = $db->prepare("
            INSERT INTO products (
                name, slug, description, price, old_price, image, images, 
                category_id, features, is_new, stock_quantity
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['name'],
            $slug,
            $input['description'],
            $input['price'],
            $input['old_price'] ?? null,
            $input['image'] ?? '',
            json_encode($input['images'] ?? []),
            $input['category_id'],
            json_encode($input['features'] ?? []),
            $input['is_new'] ?? false,
            $input['stock_quantity'] ?? 0
        ]);
        
        $productId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product created successfully',
            'product_id' => $productId
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating product: ' . $e->getMessage()]);
    }
}

function handleUpdateProduct($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    try {
        $updateFields = [];
        $params = [];
        
        $allowedFields = [
            'name', 'description', 'price', 'old_price', 'image', 'images',
            'category_id', 'features', 'is_new', 'stock_quantity', 'is_active'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateFields[] = "$field = ?";
                
                if ($field === 'images' || $field === 'features') {
                    $params[] = json_encode($input[$field]);
                } else {
                    $params[] = $input[$field];
                }
            }
        }
        
        if (empty($updateFields)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        // Update slug if name changed
        if (isset($input['name'])) {
            $newSlug = generateSlug($input['name'], $db, $input['id']);
            $updateFields[] = "slug = ?";
            $params[] = $newSlug;
        }
        
        $params[] = $input['id'];
        
        $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
    }
}

function handleDeleteProduct($db) {
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    try {
        // Soft delete - just mark as inactive
        $stmt = $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
    }
}

function generateSlug($name, $db, $excludeId = null) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $sql = "SELECT id FROM products WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            break;
        }
        
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}
?>