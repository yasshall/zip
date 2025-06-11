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

// Get statistics
$stats = [];

try {
    // Total products
    $stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
    $stats['products'] = $stmt->fetch()['count'] ?? 0;

    // Total orders
    $stmt = $db->query("SELECT COUNT(*) as count FROM orders");
    $stats['orders'] = $stmt->fetch()['count'] ?? 0;

    // Total revenue
    $stmt = $db->query("SELECT SUM(total) as revenue FROM orders WHERE status != 'cancelled'");
    $stats['revenue'] = $stmt->fetch()['revenue'] ?? 0;

    // Newsletter subscribers
    $stmt = $db->query("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active'");
    $stats['subscribers'] = $stmt->fetch()['count'] ?? 0;

    // Recent orders
    $stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll();
} catch (Exception $e) {
    // If database connection fails, set default values
    $stats = [
        'products' => 0,
        'orders' => 0,
        'revenue' => 0,
        'subscribers' => 0
    ];
    $recentOrders = [];
    $db_error = $e->getMessage();
}

$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Luxury Watches</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <div class="page-header">
                    <h1>Tableau de Bord</h1>
                    <p>Vue d'ensemble de votre boutique</p>
                </div>
                
                <?php if (isset($db_error)): ?>
                    <div style="background: #fee; color: #c33; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #fcc;">
                        <strong>Database Connection Error:</strong> <?= htmlspecialchars($db_error) ?><br>
                        <small>Please check your database configuration in backend/config/database.php</small>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 7h-9"/>
                                <path d="M14 17H5"/>
                                <circle cx="17" cy="17" r="3"/>
                                <circle cx="7" cy="7" r="3"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['products']) ?></h3>
                            <p>Produits</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <path d="m16 10-4 4-4-4"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['orders']) ?></h3>
                            <p>Commandes</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['revenue'], 0, ',', ' ') ?> MAD</h3>
                            <p>Chiffre d'Affaires</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($stats['subscribers']) ?></h3>
                            <p>Abonnés Newsletter</p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Commandes Récentes</h2>
                            <a href="orders.php" class="btn btn-sm">Voir Tout</a>
                        </div>
                        <div class="card-content">
                            <?php if (empty($recentOrders)): ?>
                                <p class="empty-state">Aucune commande récente</p>
                            <?php else: ?>
                                <div class="orders-list">
                                    <?php foreach ($recentOrders as $order): ?>
                                        <div class="order-item">
                                            <div class="order-info">
                                                <strong>#<?= htmlspecialchars($order['order_number']) ?></strong>
                                                <span class="order-customer"><?= htmlspecialchars($order['customer_name']) ?></span>
                                            </div>
                                            <div class="order-meta">
                                                <span class="order-total"><?= number_format($order['total'], 0, ',', ' ') ?> MAD</span>
                                                <span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Actions Rapides</h2>
                        </div>
                        <div class="card-content">
                            <div class="quick-actions">
                                <a href="products.php?action=add" class="quick-action">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Ajouter un Produit
                                </a>
                                <a href="orders.php" class="quick-action">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                        <line x1="3" y1="6" x2="21" y2="6"/>
                                        <path d="m16 10-4 4-4-4"/>
                                    </svg>
                                    Gérer les Commandes
                                </a>
                                <a href="categories.php" class="quick-action">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 7h-9"/>
                                        <path d="M14 17H5"/>
                                        <circle cx="17" cy="17" r="3"/>
                                        <circle cx="7" cy="7" r="3"/>
                                    </svg>
                                    Gérer les Catégories
                                </a>
                                <a href="settings.php" class="quick-action">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                                    </svg>
                                    Paramètres
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <h3>System Information</h3>
                    <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                    <p><strong>Session ID:</strong> <?= session_id() ?></p>
                    <p><strong>Login Status:</strong> <?= $_SESSION['admin_logged_in'] ? 'Logged In' : 'Not Logged In' ?></p>
                    <p><strong>Username:</strong> <?= $_SESSION['admin_username'] ?? 'Not Set' ?></p>
                    <p><strong>Current Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/admin.js"></script>
</body>
</html>