<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=property_custodian_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Creating Missing Tables...</h2>";

    // Create asset_categories table
    $sql = "CREATE TABLE IF NOT EXISTS asset_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "✅ Created asset_categories table<br>";

    // Insert default categories
    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_categories");
    if ($stmt->fetchColumn() == 0) {
        $sql = "INSERT INTO asset_categories (name, description) VALUES
            ('Computer Equipment', 'Desktop computers, laptops, monitors, keyboards, etc.'),
            ('Office Furniture', 'Desks, chairs, cabinets, tables'),
            ('Laboratory Equipment', 'Scientific instruments, microscopes, measuring tools'),
            ('Audio Visual Equipment', 'Projectors, speakers, cameras, recording devices'),
            ('Vehicles', 'School buses, service vehicles, motorcycles'),
            ('Kitchen Equipment', 'Stoves, refrigerators, cooking utensils'),
            ('Sports Equipment', 'Balls, nets, gymnasium equipment'),
            ('Books and References', 'Textbooks, reference materials, library books')";

        $pdo->exec($sql);
        echo "✅ Inserted default categories<br>";
    }

    // Create asset_tags table if missing
    $sql = "CREATE TABLE IF NOT EXISTS asset_tags (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        color VARCHAR(7) DEFAULT '#3B82F6',
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "✅ Created asset_tags table<br>";

    // Insert default tags
    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_tags");
    if ($stmt->fetchColumn() == 0) {
        $sql = "INSERT INTO asset_tags (name, color, description) VALUES
            ('High Priority', '#EF4444', 'High priority assets requiring special attention'),
            ('Fragile', '#F59E0B', 'Fragile items requiring careful handling'),
            ('Portable', '#10B981', 'Portable items that can be easily moved'),
            ('Expensive', '#8B5CF6', 'High-value assets requiring extra security'),
            ('Shared', '#06B6D4', 'Shared resources used by multiple departments')";

        $pdo->exec($sql);
        echo "✅ Inserted default tags<br>";
    }

    // Create asset_tag_relationships table
    $sql = "CREATE TABLE IF NOT EXISTS asset_tag_relationships (
        id INT PRIMARY KEY AUTO_INCREMENT,
        asset_id INT NOT NULL,
        tag_id INT NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES asset_tags(id) ON DELETE CASCADE,
        UNIQUE KEY unique_asset_tag (asset_id, tag_id)
    )";

    $pdo->exec($sql);
    echo "✅ Created asset_tag_relationships table<br>";

    echo "<br><strong style='color: green;'>✅ All missing tables created!</strong><br>";
    echo "<br><a href='asset-registry.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Asset Registry</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>❌ Error: " . $e->getMessage() . "</strong><br>";
}
?>