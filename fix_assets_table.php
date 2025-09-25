<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=property_custodian_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Checking and Fixing Assets Table...</h2>";

    // Show current table structure
    echo "<h3>Current assets table columns:</h3>";
    $stmt = $pdo->query("DESCRIBE assets");
    $existingColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }

    echo "<br><h3>Adding missing columns...</h3>";

    // List of columns that should exist
    $requiredColumns = [
        'current_value' => "ADD COLUMN current_value DECIMAL(15,2)",
        'purchase_cost' => "ADD COLUMN purchase_cost DECIMAL(15,2)",
        'purchase_date' => "ADD COLUMN purchase_date DATE"
    ];

    foreach ($requiredColumns as $column => $sql) {
        if (!in_array($column, $existingColumns)) {
            $pdo->exec("ALTER TABLE assets $sql");
            echo "✅ Added column: $column<br>";
        } else {
            echo "✓ Column exists: $column<br>";
        }
    }

    // Now add sample values
    echo "<br><h3>Adding sample values...</h3>";
    $updates = [
        ['id' => 1, 'value' => 35000.00, 'category' => 1],
        ['id' => 2, 'value' => 35000.00, 'category' => 1],
        ['id' => 3, 'value' => 45000.00, 'category' => 1],
        ['id' => 4, 'value' => 50000.00, 'category' => 1],
        ['id' => 5, 'value' => 15000.00, 'category' => 1],
        ['id' => 6, 'value' => 12000.00, 'category' => 2],
        ['id' => 7, 'value' => 8000.00, 'category' => 2],
        ['id' => 8, 'value' => 15000.00, 'category' => 2],
        ['id' => 9, 'value' => 25000.00, 'category' => 3],
        ['id' => 10, 'value' => 18000.00, 'category' => 3]
    ];

    foreach ($updates as $update) {
        $sql = "UPDATE assets SET current_value = ?, category = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$update['value'], $update['category'], $update['id']])) {
            echo "✅ Updated asset ID {$update['id']} with value ₱" . number_format($update['value']) . "<br>";
        }
    }

    // Add tag assignments
    echo "<br><h3>Adding sample tags...</h3>";
    $tagAssignments = [
        ['asset_id' => 1, 'tag_id' => 1], // Desktop -> High Priority
        ['asset_id' => 3, 'tag_id' => 3], // Laptop -> Portable
        ['asset_id' => 4, 'tag_id' => 3], // Laptop -> Portable
        ['asset_id' => 4, 'tag_id' => 4], // Laptop -> Expensive
        ['asset_id' => 9, 'tag_id' => 2], // Microscope -> Fragile
        ['asset_id' => 6, 'tag_id' => 5], // Chair -> Shared
    ];

    foreach ($tagAssignments as $assignment) {
        $sql = "INSERT IGNORE INTO asset_tag_relationships (asset_id, tag_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$assignment['asset_id'], $assignment['tag_id']])) {
            echo "✅ Assigned tag to asset ID {$assignment['asset_id']}<br>";
        }
    }

    echo "<br><strong style='color: green;'>✅ Assets table fixed and sample data added!</strong><br>";
    echo "<br><a href='asset-registry.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Updated Asset Registry</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>❌ Error: " . $e->getMessage() . "</strong><br>";
}
?>