<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
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
            handleGetSubscribers($db);
            break;
        case 'POST':
            handleSubscribe($db);
            break;
        case 'DELETE':
            handleUnsubscribe($db);
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

function handleGetSubscribers($db) {
    $limit = $_GET['limit'] ?? 50;
    $offset = $_GET['offset'] ?? 0;
    $status = $_GET['status'] ?? 'active';
    
    try {
        $stmt = $db->prepare("
            SELECT * FROM newsletter_subscribers 
            WHERE status = ? 
            ORDER BY subscribed_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$status, (int)$limit, (int)$offset]);
        $subscribers = $stmt->fetchAll();
        
        // Get total count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = ?");
        $stmt->execute([$status]);
        $total = $stmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'subscribers' => $subscribers,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching subscribers: ' . $e->getMessage()]);
    }
}

function handleSubscribe($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        return;
    }
    
    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
    }
    
    try {
        // Check if email already exists
        $stmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($existing['status'] === 'active') {
                echo json_encode(['success' => false, 'message' => 'Email already subscribed']);
                return;
            } else {
                // Reactivate subscription
                $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'active', subscribed_at = NOW() WHERE email = ?");
                $stmt->execute([$email]);
                echo json_encode(['success' => true, 'message' => 'Subscription reactivated successfully']);
                return;
            }
        }
        
        // Insert new subscription
        $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        
        echo json_encode(['success' => true, 'message' => 'Subscribed successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error subscribing: ' . $e->getMessage()]);
    }
}

function handleUnsubscribe($db) {
    $email = $_GET['email'] ?? '';
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        return;
    }
    
    try {
        $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed' WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Unsubscribed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error unsubscribing: ' . $e->getMessage()]);
    }
}
?>