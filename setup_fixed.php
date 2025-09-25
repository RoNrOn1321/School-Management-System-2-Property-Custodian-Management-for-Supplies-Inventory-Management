<?php
// Improved database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Setting up Property Custodian Database...</h2>";

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS property_custodian_db");
    $pdo->exec("USE property_custodian_db");
    echo "✓ Database ready<br>";

    // Check and create missing tables
    $tables_to_check = [
        'asset_tags' => "CREATE TABLE asset_tags (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            color VARCHAR(7) DEFAULT '#3B82F6',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'asset_tag_relationships' => "CREATE TABLE asset_tag_relationships (
            id INT PRIMARY KEY AUTO_INCREMENT,
            asset_id INT NOT NULL,
            tag_id INT NOT NULL,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES asset_tags(id) ON DELETE CASCADE,
            UNIQUE KEY unique_asset_tag (asset_id, tag_id)
        )"
    ];

    foreach ($tables_to_check as $table_name => $create_sql) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table_name'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec($create_sql);
            echo "✓ Created table: $table_name<br>";
        } else {
            echo "✓ Table exists: $table_name<br>";
        }
    }

    // Insert default tags if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_tags");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO asset_tags (name, color, description) VALUES
            ('High Priority', '#EF4444', 'High priority assets requiring special attention'),
            ('Fragile', '#F59E0B', 'Fragile items requiring careful handling'),
            ('Portable', '#10B981', 'Portable items that can be easily moved'),
            ('Expensive', '#8B5CF6', 'High-value assets requiring extra security'),
            ('Shared', '#06B6D4', 'Shared resources used by multiple departments')");
        echo "✓ Inserted default tags<br>";
    }

    // Check if we have sample data
    $stmt = $pdo->query("SELECT COUNT(*) FROM assets");
    $assetCount = $stmt->fetchColumn();
    echo "✓ Found $assetCount assets in database<br>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_categories");
    $categoryCount = $stmt->fetchColumn();
    echo "✓ Found $categoryCount categories in database<br>";

    echo "<br><strong style='color: green;'>✅ Database setup completed!</strong><br>";
    echo "<br><a href='asset-registry.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Asset Registry</a>";
    echo "<br><br><a href='test_api.php'>Test API Endpoints</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>❌ Error: " . $e->getMessage() . "</strong><br>";
    echo "<br>Make sure XAMPP MySQL service is running.";
}
?>