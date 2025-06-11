<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetCategories($db);
            break;
        case 'POST':
            handleCreateCategory($db);
            break;
        case 'PUT':
            handleUpdateCategory($db);
            break;
        case 'DELETE':
            handleDeleteCategory($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleGetCategories($db) {
    $id = $_GET['id'] ?? '';
    
    try {
        if ($id) {
            // Get single category
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $category = $stmt->fetch();
            
            if ($category) {
                // Get product count
                $stmt = $db->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ? AND is_active = 1");
                $stmt->execute([$id]);
                $category['product_count'] = $stmt->fetch()['product_count'];
                
                echo json_encode(['success' => true, 'category' => $category]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Category not found']);
            }
            return;
        }
        
        // Get all categories
        $stmt = $db->prepare("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
            GROUP BY c.id 
            ORDER BY c.name
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'categories' => $categories]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching categories: ' . $e->getMessage()]);
    }
}

function handleCreateCategory($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        return;
    }
    
    $required = ['name'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            return;
        }
    }
    
    try {
        $slug = generateCategorySlug($input['name'], $db);
        
        $stmt = $db->prepare("
            INSERT INTO categories (name, slug, description) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $input['name'],
            $slug,
            $input['description'] ?? ''
        ]);
        
        $categoryId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Category created successfully',
            'category_id' => $categoryId
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating category: ' . $e->getMessage()]);
    }
}

function handleUpdateCategory($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }
    
    try {
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['name', 'description'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        if (empty($updateFields)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        // Update slug if name changed
        if (isset($input['name'])) {
            $newSlug = generateCategorySlug($input['name'], $db, $input['id']);
            $updateFields[] = "slug = ?";
            $params[] = $newSlug;
        }
        
        $params[] = $input['id'];
        
        $sql = "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()]);
    }
}

function handleDeleteCategory($db) {
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }
    
    try {
        // Check if category has products
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $productCount = $stmt->fetch()['count'];
        
        if ($productCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete category with products']);
            return;
        }
        
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()]);
    }
}

function generateCategorySlug($name, $db, $excludeId = null) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $sql = "SELECT id FROM categories WHERE slug = ?";
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