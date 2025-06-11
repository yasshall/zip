<?php
// Simple test file to check admin functionality
session_start();

echo "<h1>Admin Test Page</h1>";
echo "<p>If you can see this, the admin directory is working.</p>";

echo "<h2>File Structure Test:</h2>";
echo "<ul>";

$adminFiles = [
    'index.php' => file_exists('index.php'),
    'dashboard.php' => file_exists('dashboard.php'),
    'products.php' => file_exists('products.php'),
    'categories.php' => file_exists('categories.php'),
    'orders.php' => file_exists('orders.php'),
    'newsletter.php' => file_exists('newsletter.php'),
    'settings.php' => file_exists('settings.php'),
];

foreach ($adminFiles as $file => $exists) {
    $status = $exists ? '✅ EXISTS' : '❌ MISSING';
    echo "<li>$file: $status</li>";
}

echo "</ul>";

echo "<h2>Session Info:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Navigation Links:</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Login Page</a></li>";
echo "<li><a href='dashboard.php'>Dashboard</a></li>";
echo "<li><a href='products.php'>Products (this should work after fix)</a></li>";
echo "</ul>";
?>