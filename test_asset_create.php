<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

try {
    echo json_encode(array("message" => "Starting test...", "step" => "1"));
    echo "\n";

    $database = new Database();
    $db = $database->getConnection();

    echo json_encode(array("message" => "Database connected", "step" => "2"));
    echo "\n";

    // Test data similar to what might be sent from the frontend
    $testData = json_decode('{"asset_code":"TEST001","name":"Test Asset","description":"Test Description","category":"Electronics","status":"available","condition_status":"good","location":"Office","purchase_date":"2025-09-25","purchase_cost":"100","current_value":"100"}');

    echo json_encode(array("message" => "Test data prepared", "step" => "3", "data" => $testData));
    echo "\n";

    // Check what columns actually exist in the assets table
    $checkStmt = $db->query("DESCRIBE assets");
    $columns = array();
    while ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }

    echo json_encode(array("message" => "Column check complete", "step" => "4", "columns" => $columns));
    echo "\n";

    // Build the INSERT query based on available columns
    $insertFields = array();
    $insertValues = array();
    $placeholders = array();

    if (in_array('asset_code', $columns) && !empty($testData->asset_code)) {
        $insertFields[] = 'asset_code';
        $insertValues[] = $testData->asset_code;
        $placeholders[] = '?';
    }

    if (in_array('name', $columns) && !empty($testData->name)) {
        $insertFields[] = 'name';
        $insertValues[] = $testData->name;
        $placeholders[] = '?';
    }

    if (in_array('description', $columns)) {
        $insertFields[] = 'description';
        $insertValues[] = $testData->description ?? null;
        $placeholders[] = '?';
    }

    // Handle category - check if it's category_id or category
    if (in_array('category_id', $columns)) {
        $insertFields[] = 'category_id';
        $insertValues[] = $testData->category ?? null;
        $placeholders[] = '?';
    } elseif (in_array('category', $columns)) {
        $insertFields[] = 'category';
        $insertValues[] = $testData->category ?? null;
        $placeholders[] = '?';
    }

    if (in_array('condition_status', $columns)) {
        $insertFields[] = 'condition_status';
        $insertValues[] = $testData->condition_status ?? 'good';
        $placeholders[] = '?';
    }

    if (in_array('location', $columns)) {
        $insertFields[] = 'location';
        $insertValues[] = $testData->location ?? null;
        $placeholders[] = '?';
    }

    if (in_array('purchase_date', $columns)) {
        $insertFields[] = 'purchase_date';
        $insertValues[] = $testData->purchase_date ?? null;
        $placeholders[] = '?';
    }

    if (in_array('purchase_cost', $columns)) {
        $insertFields[] = 'purchase_cost';
        $insertValues[] = $testData->purchase_cost ?? null;
        $placeholders[] = '?';
    }

    if (in_array('current_value', $columns)) {
        $insertFields[] = 'current_value';
        $insertValues[] = $testData->current_value ?? null;
        $placeholders[] = '?';
    }

    if (in_array('status', $columns)) {
        $insertFields[] = 'status';
        $insertValues[] = $testData->status ?? 'available';
        $placeholders[] = '?';
    }

    $query = "INSERT INTO assets (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";

    echo json_encode(array("message" => "Query prepared", "step" => "5", "query" => $query, "values" => $insertValues));
    echo "\n";

    $stmt = $db->prepare($query);

    if($stmt->execute($insertValues)) {
        $asset_id = $db->lastInsertId();
        echo json_encode(array("message" => "Asset created successfully", "step" => "6", "id" => $asset_id));
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode(array("message" => "Failed to create asset", "step" => "6", "error" => $errorInfo[2], "errorCode" => $errorInfo[1]));
    }

} catch (Exception $e) {
    echo json_encode(array("message" => "Database error", "error" => $e->getMessage(), "trace" => $e->getTraceAsString()));
}
?>