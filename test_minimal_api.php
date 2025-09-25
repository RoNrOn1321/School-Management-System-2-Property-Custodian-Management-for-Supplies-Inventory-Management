<?php
// Minimal test API to isolate issues
header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';

    if ($method === 'PUT') {
        $input = file_get_contents("php://input");

        echo json_encode([
            'status' => 'success',
            'method' => $method,
            'input_received' => !empty($input),
            'input_length' => strlen($input),
            'data_preview' => substr($input, 0, 100)
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'method' => $method,
            'message' => 'Test API working'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>