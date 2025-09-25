<?php
// Test API endpoints directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing API Endpoints</h2>";

echo "<h3>1. Testing Database Connection:</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        echo "✅ Database connection successful<br>";

        // Test if tables exist
        $tables = ['assets', 'asset_categories', 'asset_tags'];
        foreach ($tables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Table '$table' exists<br>";
            } else {
                echo "❌ Table '$table' missing<br>";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

echo "<h3>2. Testing Assets API:</h3>";
ob_start();
include 'api/assets.php';
$output = ob_get_clean();
echo "API Output:<br><pre>" . htmlspecialchars($output) . "</pre>";

echo "<hr>";

echo "<h3>3. Testing Categories API:</h3>";
ob_start();
include 'api/asset_categories.php';
$output = ob_get_clean();
echo "Categories Output:<br><pre>" . htmlspecialchars($output) . "</pre>";
?>