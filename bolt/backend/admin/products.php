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
$productId = $_GET['id'] ?? '';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $result = addProduct($db, $_POST);
        if ($result['success']) {
            $message = $result['message'];
            $action = 'list';
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['update_product'])) {
        $result = updateProduct($db, $_POST);
        if ($result['success']) {
            $message = $result['message'];
            $action = 'list';
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['delete_product'])) {
        $result = deleteProduct($db, $_POST['product_id']);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get products for listing
$products = [];
$categories = [];
try {
    // Get all products
    $stmt = $db->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();
    
    // Get categories for dropdown
    $stmt = $db->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Get single product for editing
$currentProduct = null;
if ($action === 'edit' && $productId) {
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $currentProduct = $stmt->fetch();
        if (!$currentProduct) {
            $error = "Product not found";
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = "Error loading product: " . $e->getMessage();
        $action = 'list';
    }
}

function addProduct($db, $data) {
    try {
        $name = trim($data['name']);
        $description = trim($data['description']);
        $price = floatval($data['price']);
        $old_price = !empty($data['old_price']) ? floatval($data['old_price']) : null;
        $category_id = intval($data['category_id']);
        $image = trim($data['image']);
        $is_new = isset($data['is_new']) ? 1 : 0;
        $stock_quantity = intval($data['stock_quantity']);
        
        // Generate slug
        $slug = generateSlug($name, $db);
        
        // Prepare features JSON
        $features = [];
        if (!empty($data['feature_keys'])) {
            foreach ($data['feature_keys'] as $index => $key) {
                if (!empty($key) && !empty($data['feature_values'][$index])) {
                    $features[trim($key)] = trim($data['feature_values'][$index]);
                }
            }
        }
        
        // Prepare images array
        $images = [];
        if (!empty($data['additional_images'])) {
            $imageUrls = explode("\n", $data['additional_images']);
            foreach ($imageUrls as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $images[] = $url;
                }
            }
        }
        if (!empty($image)) {
            array_unshift($images, $image); // Add main image as first
        }
        
        $stmt = $db->prepare("
            INSERT INTO products (name, slug, description, price, old_price, image, images, category_id, features, is_new, stock_quantity)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name,
            $slug,
            $description,
            $price,
            $old_price,
            $image,
            json_encode($images),
            $category_id,
            json_encode($features),
            $is_new,
            $stock_quantity
        ]);
        
        return ['success' => true, 'message' => 'Product added successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding product: ' . $e->getMessage()];
    }
}

function updateProduct($db, $data) {
    try {
        $id = intval($data['product_id']);
        $name = trim($data['name']);
        $description = trim($data['description']);
        $price = floatval($data['price']);
        $old_price = !empty($data['old_price']) ? floatval($data['old_price']) : null;
        $category_id = intval($data['category_id']);
        $image = trim($data['image']);
        $is_new = isset($data['is_new']) ? 1 : 0;
        $stock_quantity = intval($data['stock_quantity']);
        
        // Generate new slug if name changed
        $slug = generateSlug($name, $db, $id);
        
        // Prepare features JSON
        $features = [];
        if (!empty($data['feature_keys'])) {
            foreach ($data['feature_keys'] as $index => $key) {
                if (!empty($key) && !empty($data['feature_values'][$index])) {
                    $features[trim($key)] = trim($data['feature_values'][$index]);
                }
            }
        }
        
        // Prepare images array
        $images = [];
        if (!empty($data['additional_images'])) {
            $imageUrls = explode("\n", $data['additional_images']);
            foreach ($imageUrls as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $images[] = $url;
                }
            }
        }
        if (!empty($image)) {
            array_unshift($images, $image); // Add main image as first
        }
        
        $stmt = $db->prepare("
            UPDATE products 
            SET name = ?, slug = ?, description = ?, price = ?, old_price = ?, image = ?, images = ?, 
                category_id = ?, features = ?, is_new = ?, stock_quantity = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $name,
            $slug,
            $description,
            $price,
            $old_price,
            $image,
            json_encode($images),
            $category_id,
            json_encode($features),
            $is_new,
            $stock_quantity,
            $id
        ]);
        
        return ['success' => true, 'message' => 'Product updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()];
    }
}

function deleteProduct($db, $id) {
    try {
        $stmt = $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true, 'message' => 'Product deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()];
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

$pageTitle = 'Products Management';
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
                    <!-- Products List -->
                    <div class="page-header">
                        <h1>Products Management</h1>
                        <a href="?action=add" class="btn btn-primary">Add New Product</a>
                    </div>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="7" class="empty-state">No products found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php if ($product['image']): ?>
                                                    <img src="<?= htmlspecialchars($product['image']) ?>" 
                                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666;">No Image</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                                <?php if ($product['is_new']): ?>
                                                    <span style="background: #10B981; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 5px;">NEW</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($product['category_name'] ?? 'No Category') ?></td>
                                            <td>
                                                <?php if ($product['old_price']): ?>
                                                    <span style="text-decoration: line-through; color: #999; font-size: 12px;"><?= number_format($product['old_price'], 0) ?> MAD</span><br>
                                                <?php endif; ?>
                                                <strong><?= number_format($product['price'], 0) ?> MAD</strong>
                                            </td>
                                            <td><?= $product['stock_quantity'] ?></td>
                                            <td>
                                                <?php if ($product['is_active']): ?>
                                                    <span style="color: #10B981;">Active</span>
                                                <?php else: ?>
                                                    <span style="color: #EF4444;">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?action=edit&id=<?= $product['id'] ?>" class="btn btn-sm">Edit</a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                    <button type="submit" name="delete_product" class="btn btn-sm btn-error">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php elseif ($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Product Form -->
                    <div class="page-header">
                        <h1><?= $action === 'add' ? 'Add New Product' : 'Edit Product' ?></h1>
                        <a href="products.php" class="btn btn-secondary">Back to List</a>
                    </div>
                    
                    <form method="POST" class="form-grid">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="product_id" value="<?= $currentProduct['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?= htmlspecialchars($currentProduct['name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= ($currentProduct['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (MAD) *</label>
                            <input type="number" id="price" name="price" step="0.01" required 
                                   value="<?= $currentProduct['price'] ?? '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="old_price">Old Price (MAD)</label>
                            <input type="number" id="old_price" name="old_price" step="0.01" 
                                   value="<?= $currentProduct['old_price'] ?? '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" 
                                   value="<?= $currentProduct['stock_quantity'] ?? '0' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_new" value="1" 
                                       <?= ($currentProduct['is_new'] ?? false) ? 'checked' : '' ?>>
                                Mark as New Arrival
                            </label>
                        </div>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($currentProduct['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="image">Main Image URL *</label>
                            <input type="url" id="image" name="image" required 
                                   value="<?= htmlspecialchars($currentProduct['image'] ?? '') ?>"
                                   placeholder="https://example.com/image.jpg">
                        </div>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="additional_images">Additional Images (one URL per line)</label>
                            <textarea id="additional_images" name="additional_images" rows="3" 
                                      placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"><?php 
                                if ($currentProduct && $currentProduct['images']) {
                                    $images = json_decode($currentProduct['images'], true);
                                    if (is_array($images) && count($images) > 1) {
                                        echo htmlspecialchars(implode("\n", array_slice($images, 1)));
                                    }
                                }
                            ?></textarea>
                        </div>
                        
                        <div style="grid-column: 1 / -1;">
                            <h3>Product Features</h3>
                            <div id="features-container">
                                <?php 
                                $features = [];
                                if ($currentProduct && $currentProduct['features']) {
                                    $features = json_decode($currentProduct['features'], true) ?: [];
                                }
                                
                                if (empty($features)) {
                                    $features = ['Mouvement' => '', 'Matériau' => '', 'Garantie' => ''];
                                }
                                
                                $index = 0;
                                foreach ($features as $key => $value):
                                ?>
                                    <div class="feature-row" style="display: flex; gap: 1rem; margin-bottom: 0.5rem;">
                                        <input type="text" name="feature_keys[]" placeholder="Feature name" 
                                               value="<?= htmlspecialchars($key) ?>" style="flex: 1;">
                                        <input type="text" name="feature_values[]" placeholder="Feature value" 
                                               value="<?= htmlspecialchars($value) ?>" style="flex: 1;">
                                        <button type="button" onclick="removeFeature(this)" style="background: #EF4444; color: white; border: none; padding: 8px; border-radius: 4px;">Remove</button>
                                    </div>
                                <?php 
                                $index++;
                                endforeach; 
                                ?>
                            </div>
                            <button type="button" onclick="addFeature()" class="btn btn-secondary">Add Feature</button>
                        </div>
                        
                        <div style="grid-column: 1 / -1; margin-top: 2rem;">
                            <button type="submit" name="<?= $action === 'add' ? 'add_product' : 'update_product' ?>" 
                                    class="btn btn-primary">
                                <?= $action === 'add' ? 'Add Product' : 'Update Product' ?>
                            </button>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        function addFeature() {
            const container = document.getElementById('features-container');
            const div = document.createElement('div');
            div.className = 'feature-row';
            div.style.cssText = 'display: flex; gap: 1rem; margin-bottom: 0.5rem;';
            div.innerHTML = `
                <input type="text" name="feature_keys[]" placeholder="Feature name" style="flex: 1;">
                <input type="text" name="feature_values[]" placeholder="Feature value" style="flex: 1;">
                <button type="button" onclick="removeFeature(this)" style="background: #EF4444; color: white; border: none; padding: 8px; border-radius: 4px;">Remove</button>
            `;
            container.appendChild(div);
        }
        
        function removeFeature(button) {
            button.parentElement.remove();
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
        
        .feature-row {
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>
</html>