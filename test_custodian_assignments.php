<?php
// Direct database test (bypassing session for testing)
require_once 'config/database.php';

echo "<h2>Testing Custodian Assignment Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h3>1. Testing Database Connection</h3>";
    echo "<p style='color: green;'>✓ Database connection successful</p>";

    echo "<h3>2. Testing Custodians Table</h3>";
    $query = "SELECT COUNT(*) as count FROM custodians";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Custodians in database: " . $result['count'] . "</p>";

    echo "<h3>3. Testing Assets Table</h3>";
    $query = "SELECT COUNT(*) as count FROM assets WHERE status = 'available'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Available assets: " . $result['count'] . "</p>";

    echo "<h3>4. Testing Property Assignments Table</h3>";
    $query = "SELECT COUNT(*) as count FROM property_assignments WHERE status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Active assignments: " . $result['count'] . "</p>";

    echo "<h3>5. Sample Data Check</h3>";

    // Check if we have sample custodians
    $query = "SELECT * FROM custodians LIMIT 3";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $custodians = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($custodians)) {
        echo "<p style='color: orange;'>⚠ No custodians found. Creating sample data...</p>";

        // Create sample custodian
        $query = "INSERT INTO custodians (employee_id, department, position) VALUES ('EMP001', 'IT Department', 'Systems Administrator')";
        $stmt = $db->prepare($query);
        $stmt->execute();
        echo "<p style='color: green;'>✓ Sample custodian created</p>";
    } else {
        echo "<p style='color: green;'>✓ Custodians found:</p>";
        foreach ($custodians as $custodian) {
            echo "<p>- {$custodian['employee_id']} ({$custodian['department']})</p>";
        }
    }

    // Check if we have sample assets
    $query = "SELECT * FROM assets WHERE status = 'available' LIMIT 3";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($assets)) {
        echo "<p style='color: orange;'>⚠ No available assets found. You may need to run the database setup script.</p>";
    } else {
        echo "<p style='color: green;'>✓ Available assets found:</p>";
        foreach ($assets as $asset) {
            echo "<p>- {$asset['asset_code']}: {$asset['name']}</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='custodian-assignment.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Custodian Assignment Page</a></p>";
?>