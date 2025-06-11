<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'list';
$orderId = $_GET['id'] ?? '';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $result = updateOrderStatus($db, $_POST);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get orders for listing
$orders = [];
$statusFilter = $_GET['status'] ?? '';
try {
    $sql = "SELECT * FROM orders WHERE 1=1";
    $params = [];
    
    if ($statusFilter) {
        $sql .= " AND status = ?";
        $params[] = $statusFilter;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Get single order for viewing
$currentOrder = null;
$orderItems = [];
if ($action === 'view' && $orderId) {
    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $currentOrder = $stmt->fetch();
        
        if ($currentOrder) {
            $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $orderItems = $stmt->fetchAll();
        } else {
            $error = "Order not found";
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = "Error loading order: " . $e->getMessage();
        $action = 'list';
    }
}

function updateOrderStatus($db, $data) {
    try {
        $orderId = intval($data['order_id']);
        $status = $data['status'];
        
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        
        return ['success' => true, 'message' => 'Order status updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating order status: ' . $e->getMessage()];
    }
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="status-pending">Pending</span>',
        'confirmed' => '<span class="status-confirmed">Confirmed</span>',
        'processing' => '<span class="status-processing">Processing</span>',
        'shipped' => '<span class="status-shipped">Shipped</span>',
        'delivered' => '<span class="status-delivered">Delivered</span>',
        'cancelled' => '<span class="status-cancelled">Cancelled</span>'
    ];
    
    return $badges[$status] ?? $status;
}

$pageTitle = 'Orders Management';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Admin Luxury Watches</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="notification success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="notification error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($action === 'list'): ?>
                    <!-- Orders List -->
                    <div class="page-header">
                        <h1>Orders Management</h1>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <select onchange="window.location.href='?status=' + this.value" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                                <option value="">All Orders</option>
                                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" class="empty-state">No orders found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                                            <td><strong><?= number_format($order['total'], 0) ?> MAD</strong></td>
                                            <td><?= getStatusBadge($order['status']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <a href="?action=view&id=<?= $order['id'] ?>" class="btn btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php elseif ($action === 'view'): ?>
                    <!-- Order Details -->
                    <div class="page-header">
                        <h1>Order Details - <?= htmlspecialchars($currentOrder['order_number']) ?></h1>
                        <a href="orders.php" class="btn btn-secondary">Back to List</a>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                        <!-- Order Information -->
                        <div>
                            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                                <h2>Customer Information</h2>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                                    <div>
                                        <strong>Name:</strong><br>
                                        <?= htmlspecialchars($currentOrder['customer_name']) ?>
                                    </div>
                                    <div>
                                        <strong>Phone:</strong><br>
                                        <a href="tel:<?= htmlspecialchars($currentOrder['customer_phone']) ?>"><?= htmlspecialchars($currentOrder['customer_phone']) ?></a>
                                    </div>
                                    <div>
                                        <strong>Email:</strong><br>
                                        <?= $currentOrder['customer_email'] ? '<a href="mailto:' . htmlspecialchars($currentOrder['customer_email']) . '">' . htmlspecialchars($currentOrder['customer_email']) . '</a>' : 'Not provided' ?>
                                    </div>
                                    <div>
                                        <strong>City:</strong><br>
                                        <?= htmlspecialchars($currentOrder['customer_city']) ?>
                                    </div>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <strong>Address:</strong><br>
                                    <?= nl2br(htmlspecialchars($currentOrder['customer_address'])) ?>
                                </div>
                                <?php if ($currentOrder['notes']): ?>
                                    <div style="margin-top: 1rem;">
                                        <strong>Notes:</strong><br>
                                        <?= nl2br(htmlspecialchars($currentOrder['notes'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Order Items -->
                            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h2>Order Items</h2>
                                <table style="width: 100%; margin-top: 1rem;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid #eee;">
                                            <th style="text-align: left; padding: 0.5rem;">Product</th>
                                            <th style="text-align: center; padding: 0.5rem;">Quantity</th>
                                            <th style="text-align: right; padding: 0.5rem;">Price</th>
                                            <th style="text-align: right; padding: 0.5rem;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr style="border-bottom: 1px solid #f5f5f5;">
                                                <td style="padding: 0.75rem 0.5rem;"><?= htmlspecialchars($item['product_name']) ?></td>
                                                <td style="text-align: center; padding: 0.75rem 0.5rem;"><?= $item['quantity'] ?></td>
                                                <td style="text-align: right; padding: 0.75rem 0.5rem;"><?= number_format($item['product_price'], 0) ?> MAD</td>
                                                <td style="text-align: right; padding: 0.75rem 0.5rem;"><strong><?= number_format($item['total'], 0) ?> MAD</strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr style="border-top: 2px solid #ddd;">
                                            <td colspan="3" style="text-align: right; padding: 0.75rem 0.5rem;"><strong>Subtotal:</strong></td>
                                            <td style="text-align: right; padding: 0.75rem 0.5rem;"><strong><?= number_format($currentOrder['subtotal'], 0) ?> MAD</strong></td>
                                        </tr>
                                        <?php if ($currentOrder['discount'] > 0): ?>
                                            <tr>
                                                <td colspan="3" style="text-align: right; padding: 0.25rem 0.5rem; color: #10B981;"><strong>Discount:</strong></td>
                                                <td style="text-align: right; padding: 0.25rem 0.5rem; color: #10B981;"><strong>-<?= number_format($currentOrder['discount'], 0) ?> MAD</strong></td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr style="font-size: 1.2rem;">
                                            <td colspan="3" style="text-align: right; padding: 0.75rem 0.5rem;"><strong>Total:</strong></td>
                                            <td style="text-align: right; padding: 0.75rem 0.5rem;"><strong><?= number_format($currentOrder['total'], 0) ?> MAD</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Order Status -->
                        <div>
                            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                                <h2>Order Status</h2>
                                <form method="POST" style="margin-top: 1rem;">
                                    <input type="hidden" name="order_id" value="<?= $currentOrder['id'] ?>">
                                    <div style="margin-bottom: 1rem;">
                                        <label>Current Status:</label><br>
                                        <?= getStatusBadge($currentOrder['status']) ?>
                                    </div>
                                    <div style="margin-bottom: 1rem;">
                                        <label for="status">Update Status:</label>
                                        <select id="status" name="status" style="width: 100%; padding: 8px; margin-top: 0.5rem; border-radius: 4px; border: 1px solid #ddd;">
                                            <option value="pending" <?= $currentOrder['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $currentOrder['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="processing" <?= $currentOrder['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="shipped" <?= $currentOrder['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="delivered" <?= $currentOrder['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $currentOrder['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="update_status" class="btn btn-primary" style="width: 100%;">Update Status</button>
                                </form>
                            </div>
                            
                            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <h2>Order Info</h2>
                                <div style="margin-top: 1rem;">
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>Order Date:</strong><br>
                                        <?= date('d/m/Y H:i', strtotime($currentOrder['created_at'])) ?>
                                    </div>
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>Payment Method:</strong><br>
                                        Cash on Delivery
                                    </div>
                                    <div style="margin-bottom: 0.5rem;">
                                        <strong>Last Updated:</strong><br>
                                        <?= date('d/m/Y H:i', strtotime($currentOrder['updated_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Auto-hide notifications
        setTimeout(() => {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            });
        }, 5000);
    </script>
    
    <style>
        .notification {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            transition: opacity 0.3s ease;
        }
        
        .notification.success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        
        .notification.error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
    </style>
</body>
</html>