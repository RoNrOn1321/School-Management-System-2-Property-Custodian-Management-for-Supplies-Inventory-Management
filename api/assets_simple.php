<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=property_custodian_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Simple query without JOINs first
    $query = "SELECT * FROM assets LIMIT 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add empty tags array to each asset
    foreach ($assets as &$asset) {
        $asset['tags'] = [];
        $asset['category_name'] = 'Unknown';
    }

    echo json_encode($assets);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>