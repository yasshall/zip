<?php
// Simplified products management page for testing
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Products Management</title></head><body>";
echo "<h1>Products Management</h1>";
echo "<p>This is a simplified products page to test the admin functionality.</p>";

echo "<h2>Actions:</h2>";
echo "<ul>";
echo "<li><a href='?action=list'>List Products</a></li>";
echo "<li><a href='?action=add'>Add Product</a></li>";
echo "<li><a href='dashboard.php'>Back to Dashboard</a></li>";
echo "</ul>";

$action = $_GET['action'] ?? 'list';

echo "<h2>Current Action: " . htmlspecialchars($action) . "</h2>";

if ($action === 'add') {
    echo "<h3>Add New Product Form</h3>";
    echo "<form method='POST'>";
    echo "<p><label>Product Name: <input type='text' name='name' required></label></p>";
    echo "<p><label>Price: <input type='number' name='price' step='0.01' required></label></p>";
    echo "<p><label>Description: <textarea name='description'></textarea></label></p>";
    echo "<p><button type='submit' name='add_product'>Add Product</button></p>";
    echo "</form>";
    
    if (isset($_POST['add_product'])) {
        echo "<div style='color: green; padding: 10px; background: #e8f5e8; border-radius: 4px; margin: 10px 0;'>";
        echo "Product would be added: " . htmlspecialchars($_POST['name'] ?? '');
        echo "</div>";
    }
} else {
    echo "<h3>Products List</h3>";
    echo "<p>This would show the list of products from the database.</p>";
    echo "<p><a href='?action=add' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Add New Product</a></p>";
}

echo "</body></html>";
?>