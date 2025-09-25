<?php
// EXACT copy of the original assets.php structure to isolate the issue

// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection error", "error" => $e->getMessage()));
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// Test only the PUT case
if ($method === 'PUT' && isset($_GET['id'])) {
    updateAsset($db, $_GET['id']);
} else {
    echo json_encode(array("message" => "This is a test - Method: $method, ID: " . ($_GET['id'] ?? 'not set')));
}

function updateAsset($db, $id) {
    $input = file_get_contents("php://input");

    if (empty($input)) {
        http_response_code(400);
        echo json_encode(array("message" => "No input data received"));
        return;
    }

    $data = json_decode($input);

    if ($data === null) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid JSON data"));
        return;
    }

    // Validate required fields
    if (empty($data->name)) {
        http_response_code(400);
        echo json_encode(array("message" => "Asset name is required"));
        return;
    }

    $query = "UPDATE assets SET asset_code = ?, name = ?, description = ?, category = ?, purchase_date = ?, purchase_cost = ?, current_value = ?, location = ?, status = ?, condition_status = ?, assigned_to = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = $db->prepare($query);

    if($stmt->execute([
        isset($data->asset_code) ? $data->asset_code : null,
        $data->name,
        isset($data->description) ? $data->description : null,
        isset($data->category) ? $data->category : null,
        isset($data->purchase_date) ? $data->purchase_date : null,
        isset($data->purchase_cost) ? $data->purchase_cost : null,
        isset($data->current_value) ? $data->current_value : null,
        isset($data->location) ? $data->location : null,
        isset($data->status) ? $data->status : 'available',
        isset($data->condition_status) ? $data->condition_status : 'good',
        isset($data->assigned_to) ? $data->assigned_to : null,
        $id
    ])) {
        http_response_code(200);
        echo json_encode(array("message" => "Asset updated successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update asset"));
    }
}
?>