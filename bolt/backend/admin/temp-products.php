<?php
// Temporary products page to test functionality
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$action = $_GET['action'] ?? 'list';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Admin</title>
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #007cba; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .nav { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .nav a { margin-right: 15px; padding: 8px 15px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; }
        .content { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #007cba; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #005a8b; }
        .btn-secondary { background: #6c757d; }
        .btn-danger { background: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛠️ Products Management (Temporary)</h1>
            <p>This is a temporary products management page while we fix the main admin panel.</p>
        </div>
        
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="?action=list">List Products</a>
            <a href="?action=add">Add Product</a>
            <a href="debug-admin.php">Debug Admin</a>
        </div>
        
        <div class="content">
            <?php if ($action === 'add'): ?>
                <h2>Add New Product</h2>
                
                <?php if (isset($_POST['add_product'])): ?>
                    <div class="success">
                        ✅ Product would be added successfully!<br>
                        <strong>Name:</strong> <?= htmlspecialchars($_POST['name'] ?? '') ?><br>
                        <strong>Price:</strong> <?= htmlspecialchars($_POST['price'] ?? '') ?> MAD<br>
                        <strong>Description:</strong> <?= htmlspecialchars($_POST['description'] ?? '') ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (MAD) *</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="old_price">Old Price (MAD)</label>
                        <input type="number" id="old_price" name="old_price" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="1">Montres Homme</option>
                            <option value="2">Montres Femme</option>
                            <option value="3">Nouveautés</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Main Image URL</label>
                        <input type="url" id="image" name="image" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_new" value="1"> Mark as New Arrival
                        </label>
                    </div>
                    
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                    <a href="?action=list" class="btn btn-secondary">Cancel</a>
                </form>
                
            <?php else: ?>
                <h2>Products List</h2>
                <p>This would show the list of products from the database.</p>
                
                <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <h3>🔧 Status</h3>
                    <p><strong>Current Issue:</strong> The main products.php file is missing, causing 404 errors.</p>
                    <p><strong>This page works because:</strong> It's a simplified temporary version.</p>
                    <p><strong>Next step:</strong> Create the full products.php file from the existing code.</p>
                </div>
                
                <a href="?action=add" class="btn">Add New Product</a>
                
                <h3>Sample Products (Demo)</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 10px; border: 1px solid #ddd;">Name</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Price</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Category</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;">Montre Classique Or</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">2,500 MAD</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">Montres Homme</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <a href="#" class="btn btn-sm">Edit</a>
                                <a href="#" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;">Montre Femme Diamant</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">3,200 MAD</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">Montres Femme</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <a href="#" class="btn btn-sm">Edit</a>
                                <a href="#" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <h3>🚀 Next Steps</h3>
            <ol>
                <li>This temporary page proves the admin system works</li>
                <li>The main products.php file needs to be created from the existing code</li>
                <li>Once created, you'll have full product management functionality</li>
                <li>The same applies to categories.php, orders.php, etc.</li>
            </ol>
        </div>
    </div>
</body>
</html>