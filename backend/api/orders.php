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
            handleGetOrders($db);
            break;
        case 'POST':
            handleCreateOrder($db);
            break;
        case 'PUT':
            handleUpdateOrder($db);
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

function handleGetOrders($db) {
    $limit = $_GET['limit'] ?? 50;
    $offset = $_GET['offset'] ?? 0;
    $status = $_GET['status'] ?? '';
    $id = $_GET['id'] ?? '';
    
    try {
        if ($id) {
            // Get single order with items
            $stmt = $db->prepare("
                SELECT * FROM orders WHERE id = ?
            ");
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            
            if ($order) {
                // Get order items
                $stmt = $db->prepare("
                    SELECT * FROM order_items WHERE order_id = ?
                ");
                $stmt->execute([$id]);
                $order['items'] = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'order' => $order]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
            }
            return;
        }
        
        // Get multiple orders
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        // Get items for each order
        foreach ($orders as &$order) {
            $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll();
        }
        
        echo json_encode(['success' => true, 'orders' => $orders]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching orders: ' . $e->getMessage()]);
    }
}

function handleCreateOrder($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        return;
    }
    
    // Validate required fields
    $required = ['customer', 'items', 'totals'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            return;
        }
    }
    
    $customer = $input['customer'];
    $items = $input['items'];
    $totals = $input['totals'];
    
    // Validate customer data
    $customerRequired = ['firstName', 'lastName', 'phone', 'address', 'city'];
    foreach ($customerRequired as $field) {
        if (!isset($customer[$field]) || empty($customer[$field])) {
            echo json_encode(['success' => false, 'message' => "Customer $field is required"]);
            return;
        }
    }
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Order must have at least one item']);
        return;
    }
    
    try {
        $db->beginTransaction();
        
        // Generate order number
        $orderNumber = generateOrderNumber($db);
        
        // Insert order
        $stmt = $db->prepare("
            INSERT INTO orders (
                order_number, customer_name, customer_phone, customer_email,
                customer_address, customer_city, customer_postal_code, notes,
                subtotal, discount, total, payment_method
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $orderNumber,
            $customer['firstName'] . ' ' . $customer['lastName'],
            $customer['phone'],
            $customer['email'] ?? null,
            $customer['address'],
            $customer['city'],
            $customer['postalCode'] ?? null,
            $customer['notes'] ?? null,
            $totals['subtotal'],
            $totals['discount'] ?? 0,
            $totals['total'],
            $input['payment_method'] ?? 'cod'
        ]);
        
        $orderId = $db->lastInsertId();
        
        // Insert order items
        $stmt = $db->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_name, product_price, quantity, total
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['price'] * $item['quantity']
            ]);
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error creating order: ' . $e->getMessage()]);
    }
}

function handleUpdateOrder($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    
    try {
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['status', 'notes'];
        
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
        
        $params[] = $input['id'];
        
        $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating order: ' . $e->getMessage()]);
    }
}

function generateOrderNumber($db) {
    $prefix = 'LW' . date('Y');
    
    // Get the last order number for this year
    $stmt = $db->prepare("
        SELECT order_number FROM orders 
        WHERE order_number LIKE ? 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$prefix . '%']);
    $lastOrder = $stmt->fetch();
    
    if ($lastOrder) {
        $lastNumber = (int)substr($lastOrder['order_number'], strlen($prefix));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
}
?>