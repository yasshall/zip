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
    if (isset($_POST['update_settings'])) {
        $result = updateSettings($db, $_POST);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get current settings
$settings = [];
try {
    $stmt = $db->query("SELECT * FROM settings");
    $settingsData = $stmt->fetchAll();
    
    foreach ($settingsData as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

function updateSettings($db, $data) {
    try {
        $settingsToUpdate = [
            'site_name',
            'contact_email',
            'contact_phone',
            'whatsapp_number',
            'facebook_pixel_id',
            'google_analytics_id'
        ];
        
        foreach ($settingsToUpdate as $key) {
            if (isset($data[$key])) {
                $stmt = $db->prepare("
                    INSERT INTO settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([$key, $data[$key]]);
            }
        }
        
        return ['success' => true, 'message' => 'Settings updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating settings: ' . $e->getMessage()];
    }
}

$pageTitle = 'Settings';
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
                    <h1>Settings</h1>
                    <p>Configure your store settings</p>
                </div>
                
                <form method="POST" class="form-grid">
                    <div class="form-section" style="grid-column: 1 / -1;">
                        <h2>General Settings</h2>
                        
                        <div class="form-group">
                            <label for="site_name">Site Name</label>
                            <input type="text" id="site_name" name="site_name" 
                                   value="<?= htmlspecialchars($settings['site_name'] ?? 'Luxury Watches') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" id="contact_email" name="contact_email" 
                                   value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" 
                                   value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>"
                                   placeholder="+212 6XX XXX XXX">
                        </div>
                        
                        <div class="form-group">
                            <label for="whatsapp_number">WhatsApp Number</label>
                            <input type="text" id="whatsapp_number" name="whatsapp_number" 
                                   value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>"
                                   placeholder="+212XXXXXXXXX">
                        </div>
                    </div>
                    
                    <div class="form-section" style="grid-column: 1 / -1;">
                        <h2>Analytics & Tracking</h2>
                        
                        <div class="form-group">
                            <label for="facebook_pixel_id">Facebook Pixel ID</label>
                            <input type="text" id="facebook_pixel_id" name="facebook_pixel_id" 
                                   value="<?= htmlspecialchars($settings['facebook_pixel_id'] ?? '') ?>"
                                   placeholder="YOUR_PIXEL_ID">
                            <small style="color: #666; font-size: 0.875rem;">Used for Facebook advertising tracking</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="google_analytics_id">Google Analytics ID</label>
                            <input type="text" id="google_analytics_id" name="google_analytics_id" 
                                   value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>"
                                   placeholder="GA_MEASUREMENT_ID">
                            <small style="color: #666; font-size: 0.875rem;">Used for Google Analytics tracking</small>
                        </div>
                    </div>
                    
                    <div style="grid-column: 1 / -1; margin-top: 2rem;">
                        <button type="submit" name="update_settings" class="btn btn-primary">Update Settings</button>
                    </div>
                </form>
                
                <!-- System Information -->
                <div style="margin-top: 3rem; padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                    <h2>System Information</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                        <div>
                            <strong>PHP Version:</strong><br>
                            <?= phpversion() ?>
                        </div>
                        <div>
                            <strong>Server Software:</strong><br>
                            <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?>
                        </div>
                        <div>
                            <strong>Database:</strong><br>
                            MySQL <?= $db->getAttribute(PDO::ATTR_SERVER_VERSION) ?>
                        </div>
                        <div>
                            <strong>Current Time:</strong><br>
                            <?= date('Y-m-d H:i:s') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Admin Credentials -->
                <div style="margin-top: 2rem; padding: 2rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 12px;">
                    <h2 style="color: #856404;">Security Notice</h2>
                    <p style="color: #856404; margin-bottom: 1rem;">
                        <strong>Important:</strong> Change your admin credentials in <code>backend/admin/index.php</code>
                    </p>
                    <p style="color: #856404; margin: 0;">
                        Current default credentials: <code>admin</code> / <code>luxury2024</code>
                    </p>
                </div>
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
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .form-section h2 {
            margin-bottom: 1.5rem;
            color: var(--secondary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }
    </style>
</body>
</html>