<?php
// Complete database setup script
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

    // Create assets table if it doesn't exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'assets'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("CREATE TABLE assets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            asset_code VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            purchase_date DATE,
            purchase_cost DECIMAL(15,2),
            current_value DECIMAL(15,2),
            status ENUM('available', 'assigned', 'maintenance', 'damaged', 'lost', 'disposed') DEFAULT 'available',
            condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
            location VARCHAR(200),
            qr_code VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "✓ Created assets table<br>";
    } else {
        echo "✓ Assets table exists<br>";
    }

    // Create asset categories table if it doesn't exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'asset_categories'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("CREATE TABLE asset_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✓ Created asset_categories table<br>";

        // Insert default categories
        $pdo->exec("INSERT INTO asset_categories (name, description) VALUES
            ('Electronics', 'Electronic devices and equipment'),
            ('Furniture', 'Office and school furniture'),
            ('Vehicles', 'Transportation vehicles'),
            ('Tools', 'Tools and equipment'),
            ('Books', 'Books and educational materials'),
            ('Sports', 'Sports equipment and supplies')");
        echo "✓ Inserted default categories<br>";
    } else {
        echo "✓ Asset categories table exists<br>";
    }

    // Create asset tags table if it doesn't exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'asset_tags'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("CREATE TABLE asset_tags (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            color VARCHAR(7) DEFAULT '#3B82F6',
            description TEXT,
            created_by INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✓ Created asset_tags table<br>";
    } else {
        echo "✓ Asset tags table exists<br>";
    }

    // Create asset tag relationships table if it doesn't exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'asset_tag_relationships'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("CREATE TABLE asset_tag_relationships (
            id INT PRIMARY KEY AUTO_INCREMENT,
            asset_id INT NOT NULL,
            tag_id INT NOT NULL,
            assigned_by INT DEFAULT 1,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_asset_id (asset_id),
            INDEX idx_tag_id (tag_id),
            UNIQUE KEY unique_asset_tag (asset_id, tag_id)
        )");
        echo "✓ Created asset_tag_relationships table<br>";
    } else {
        echo "✓ Asset tag relationships table exists<br>";
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

    // Insert sample assets if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM assets");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO assets (asset_code, name, description, category, status, condition_status, location, purchase_cost, current_value) VALUES
            ('COMP001', 'Desktop Computer', 'Dell OptiPlex desktop computer', 'Electronics', 'available', 'good', 'Office Room 101', 800.00, 600.00),
            ('CHAIR001', 'Office Chair', 'Ergonomic office chair', 'Furniture', 'available', 'good', 'Office Room 101', 150.00, 120.00),
            ('PROJ001', 'Projector', 'HD projector for presentations', 'Electronics', 'available', 'excellent', 'Conference Room', 500.00, 450.00)");
        echo "✓ Inserted sample assets<br>";
    }

    // Show table structures
    echo "<h3>Table Structures:</h3>";

    echo "<h4>Assets table:</h4>";
    $stmt = $pdo->query("DESCRIBE assets");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Check data
    $stmt = $pdo->query("SELECT COUNT(*) FROM assets");
    $assetCount = $stmt->fetchColumn();
    echo "<p>✓ Found $assetCount assets in database</p>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_categories");
    $categoryCount = $stmt->fetchColumn();
    echo "<p>✓ Found $categoryCount categories in database</p>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_tags");
    $tagCount = $stmt->fetchColumn();
    echo "<p>✓ Found $tagCount tags in database</p>";

    echo "<br><strong style='color: green;'>✅ Database setup completed!</strong><br>";
    echo "<br><a href='asset-registry.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Asset Registry</a>";
    echo "<br><br><a href='test_asset_create.php'>Test Asset Creation</a>";
    echo "<br><a href='test_db_connection.php'>Test Database Connection</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>❌ Error: " . $e->getMessage() . "</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "<br>Make sure XAMPP MySQL service is running.";
}
?>