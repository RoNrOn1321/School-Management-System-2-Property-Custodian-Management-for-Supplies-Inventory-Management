<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=property_custodian_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Adding Sample Values and Tags...</h2>";

    // Add sample values to assets
    $updates = [
        ['id' => 1, 'value' => 35000.00, 'category' => 1], // Desktop Computer
        ['id' => 2, 'value' => 35000.00, 'category' => 1], // Desktop Computer
        ['id' => 3, 'value' => 45000.00, 'category' => 1], // Laptop
        ['id' => 4, 'value' => 50000.00, 'category' => 1], // Laptop
        ['id' => 5, 'value' => 15000.00, 'category' => 1], // Monitor
        ['id' => 6, 'value' => 12000.00, 'category' => 2], // Office Chair
        ['id' => 7, 'value' => 8000.00, 'category' => 2],  // Student Desk
        ['id' => 8, 'value' => 15000.00, 'category' => 2], // Teacher Desk
        ['id' => 9, 'value' => 25000.00, 'category' => 3], // Microscope
        ['id' => 10, 'value' => 18000.00, 'category' => 3] // Balance
    ];

    foreach ($updates as $update) {
        $sql = "UPDATE assets SET current_value = ?, category = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$update['value'], $update['category'], $update['id']]);
        echo "✅ Updated asset ID {$update['id']} with value ₱{$update['value']}<br>";
    }

    // Add some sample tag assignments
    $tagAssignments = [
        ['asset_id' => 1, 'tag_id' => 1], // Desktop Computer -> High Priority
        ['asset_id' => 3, 'tag_id' => 3], // Laptop -> Portable
        ['asset_id' => 4, 'tag_id' => 3], // Laptop -> Portable
        ['asset_id' => 4, 'tag_id' => 4], // Laptop -> Expensive
        ['asset_id' => 9, 'tag_id' => 2], // Microscope -> Fragile
        ['asset_id' => 9, 'tag_id' => 4], // Microscope -> Expensive
        ['asset_id' => 6, 'tag_id' => 5], // Office Chair -> Shared
        ['asset_id' => 7, 'tag_id' => 5], // Student Desk -> Shared
    ];

    foreach ($tagAssignments as $assignment) {
        $sql = "INSERT IGNORE INTO asset_tag_relationships (asset_id, tag_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$assignment['asset_id'], $assignment['tag_id']]);
        echo "✅ Assigned tag to asset ID {$assignment['asset_id']}<br>";
    }

    echo "<br><strong style='color: green;'>✅ Sample data added!</strong><br>";
    echo "<br><a href='asset-registry.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Updated Asset Registry</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>❌ Error: " . $e->getMessage() . "</strong><br>";
}
?>