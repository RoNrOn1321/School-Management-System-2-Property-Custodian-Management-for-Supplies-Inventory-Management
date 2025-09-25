<?php
// Simple version of assets API to isolate issues
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'PUT' && isset($_GET['id'])) {
        $input = file_get_contents("php://input");

        if (empty($input)) {
            echo json_encode(["message" => "No input data"]);
            exit();
        }

        $data = json_decode($input, true);

        if (!$data) {
            echo json_encode(["message" => "Invalid JSON"]);
            exit();
        }

        // Try basic database connection
        $host = 'localhost';
        $db_name = 'property_custodian_db';
        $username = 'root';
        $password = '';

        $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Simple update query
        $stmt = $pdo->prepare("UPDATE assets SET name = ? WHERE id = ?");
        $success = $stmt->execute([$data['name'] ?? 'Updated Asset', $_GET['id']]);

        if ($success) {
            echo json_encode(["message" => "Asset updated successfully"]);
        } else {
            echo json_encode(["message" => "Update failed"]);
        }
    } else {
        echo json_encode(["message" => "Method: $method, ID: " . ($_GET['id'] ?? 'none')]);
    }

} catch (Exception $e) {
    echo json_encode(["message" => "Error: " . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(["message" => "Fatal Error: " . $e->getMessage()]);
}
?>