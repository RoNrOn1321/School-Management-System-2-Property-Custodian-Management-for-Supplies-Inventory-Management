<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Assets API</h2>";

try {
    echo "1. Testing database connection...<br>";
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connected<br>";

    echo "<br>2. Testing assets table...<br>";
    $query = "SELECT COUNT(*) FROM assets";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "✅ Assets table has $count records<br>";

    echo "<br>3. Testing full query...<br>";
    $query = "SELECT a.*, ac.name as category_name
              FROM assets a
              LEFT JOIN asset_categories ac ON a.category_id = ac.id
              ORDER BY a.created_at DESC
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ Query successful, found " . count($assets) . " assets<br>";

    echo "<br>4. Sample JSON output:<br>";
    header('Content-Type: application/json');
    echo json_encode($assets, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}
?>