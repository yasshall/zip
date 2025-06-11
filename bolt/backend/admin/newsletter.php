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

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_subscriber'])) {
        $result = deleteSubscriber($db, $_POST['subscriber_id']);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get subscribers
$subscribers = [];
$statusFilter = $_GET['status'] ?? 'active';
try {
    $stmt = $db->prepare("
        SELECT * FROM newsletter_subscribers 
        WHERE status = ? 
        ORDER BY subscribed_at DESC
    ");
    $stmt->execute([$statusFilter]);
    $subscribers = $stmt->fetchAll();
    
    // Get statistics
    $stmt = $db->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'");
    $activeCount = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'unsubscribed'");
    $unsubscribedCount = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

function deleteSubscriber($db, $id) {
    try {
        $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true, 'message' => 'Subscriber deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error deleting subscriber: ' . $e->getMessage()];
    }
}

$pageTitle = 'Newsletter Management';
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
                
                <div class="page-header">
                    <h1>Newsletter Management</h1>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <select onchange="window.location.href='?status=' + this.value" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Subscribers</option>
                            <option value="unsubscribed" <?= $statusFilter === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                        </select>
                        <button onclick="exportSubscribers()" class="btn btn-secondary">Export CSV</button>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="stats-grid" style="margin-bottom: 2rem;">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #10B981;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($activeCount ?? 0) ?></h3>
                            <p>Active Subscribers</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #EF4444;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                                <line x1="22" y1="4" x2="2" y2="20"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($unsubscribedCount ?? 0) ?></h3>
                            <p>Unsubscribed</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #3B82F6;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <h3><?= $activeCount > 0 ? number_format(($activeCount / ($activeCount + $unsubscribedCount)) * 100, 1) : 0 ?>%</h3>
                            <p>Retention Rate</p>
                        </div>
                    </div>
                </div>
                
                <!-- Subscribers List -->
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Subscribed Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subscribers)): ?>
                                <tr>
                                    <td colspan="4" class="empty-state">No subscribers found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subscribers as $subscriber): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($subscriber['email']) ?></td>
                                        <td>
                                            <?php if ($subscriber['status'] === 'active'): ?>
                                                <span style="color: #10B981; font-weight: 600;">Active</span>
                                            <?php else: ?>
                                                <span style="color: #EF4444; font-weight: 600;">Unsubscribed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($subscriber['subscribed_at'])) ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this subscriber?')">
                                                <input type="hidden" name="subscriber_id" value="<?= $subscriber['id'] ?>">
                                                <button type="submit" name="delete_subscriber" class="btn btn-sm btn-error">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function exportSubscribers() {
            // Create CSV content
            let csv = 'Email,Status,Subscribed Date\n';
            
            <?php foreach ($subscribers as $subscriber): ?>
                csv += '<?= addslashes($subscriber['email']) ?>,<?= $subscriber['status'] ?>,<?= date('Y-m-d H:i:s', strtotime($subscriber['subscribed_at'])) ?>\n';
            <?php endforeach; ?>
            
            // Download CSV
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'newsletter-subscribers-<?= date('Y-m-d') ?>.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
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