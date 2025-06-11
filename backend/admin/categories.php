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
$categoryId = $_GET['id'] ?? '';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $result = addCategory($db, $_POST);
        if ($result['success']) {
            $message = $result['message'];
            $action = 'list';
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['update_category'])) {
        $result = updateCategory($db, $_POST);
        if ($result['success']) {
            $message = $result['message'];
            $action = 'list';
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['delete_category'])) {
        $result = deleteCategory($db, $_POST['category_id']);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get categories for listing
$categories = [];
try {
    $stmt = $db->query("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
        GROUP BY c.id 
        ORDER BY c.name
    ");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Get single category for editing
$currentCategory = null;
if ($action === 'edit' && $categoryId) {
    try {
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $currentCategory = $stmt->fetch();
        if (!$currentCategory) {
            $error = "Category not found";
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = "Error loading category: " . $e->getMessage();
        $action = 'list';
    }
}

function addCategory($db, $data) {
    try {
        $name = trim($data['name']);
        $description = trim($data['description']);
        
        // Generate slug
        $slug = generateCategorySlug($name, $db);
        
        $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $description]);
        
        return ['success' => true, 'message' => 'Category added successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding category: ' . $e->getMessage()];
    }
}

function updateCategory($db, $data) {
    try {
        $id = intval($data['category_id']);
        $name = trim($data['name']);
        $description = trim($data['description']);
        
        // Generate new slug if name changed
        $slug = generateCategorySlug($name, $db, $id);
        
        $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $description, $id]);
        
        return ['success' => true, 'message' => 'Category updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()];
    }
}

function deleteCategory($db, $id) {
    try {
        // Check if category has products
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1");
        $stmt->execute([$id]);
        $productCount = $stmt->fetch()['count'];
        
        if ($productCount > 0) {
            return ['success' => false, 'message' => 'Cannot delete category with active products'];
        }
        
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true, 'message' => 'Category deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()];
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

$pageTitle = 'Categories Management';
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
                    <!-- Categories List -->
                    <div class="page-header">
                        <h1>Categories Management</h1>
                        <a href="?action=add" class="btn btn-primary">Add New Category</a>
                    </div>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="5" class="empty-state">No categories found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($category['slug']) ?></code></td>
                                            <td><?= htmlspecialchars($category['description']) ?></td>
                                            <td><?= $category['product_count'] ?> products</td>
                                            <td>
                                                <a href="?action=edit&id=<?= $category['id'] ?>" class="btn btn-sm">Edit</a>
                                                <?php if ($category['product_count'] == 0): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                        <button type="submit" name="delete_category" class="btn btn-sm btn-error">Delete</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 12px;">Cannot delete (has products)</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php elseif ($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Category Form -->
                    <div class="page-header">
                        <h1><?= $action === 'add' ? 'Add New Category' : 'Edit Category' ?></h1>
                        <a href="categories.php" class="btn btn-secondary">Back to List</a>
                    </div>
                    
                    <form method="POST" class="form-grid">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="category_id" value="<?= $currentCategory['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="name">Category Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?= htmlspecialchars($currentCategory['name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($currentCategory['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div style="grid-column: 1 / -1; margin-top: 2rem;">
                            <button type="submit" name="<?= $action === 'add' ? 'add_category' : 'update_category' ?>" 
                                    class="btn btn-primary">
                                <?= $action === 'add' ? 'Add Category' : 'Update Category' ?>
                            </button>
                            <a href="categories.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
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