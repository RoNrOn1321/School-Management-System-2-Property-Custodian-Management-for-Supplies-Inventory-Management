<?php
// Debug version - add includes one by one to find the problem

// Step 1: Test basic setup
try {
    echo "Step 1: Basic PHP execution - OK\n";

    // Step 2: Test CORS include
    require_once __DIR__ . '/../config/cors.php';
    echo "Step 2: CORS include - OK\n";

    // Step 3: Test Database include
    require_once __DIR__ . '/../config/database.php';
    echo "Step 3: Database include - OK\n";

    // Step 4: Test Database connection
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    echo "Step 4: Database connection - OK\n";

    // Step 5: Test session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "Step 5: Session start - OK\n";

    // Step 6: Test request method
    $method = $_SERVER['REQUEST_METHOD'];
    echo "Step 6: Request method ($method) - OK\n";

    // Step 7: Test PUT processing
    if ($method === 'PUT' && isset($_GET['id'])) {
        echo "Step 7: PUT request with ID - OK\n";

        // Step 8: Test input reading
        $input = file_get_contents("php://input");
        echo "Step 8: Input reading (length: " . strlen($input) . ") - OK\n";

        if (!empty($input)) {
            // Step 9: Test JSON decode
            $data = json_decode($input);
            if ($data !== null) {
                echo "Step 9: JSON decode - OK\n";

                // Step 10: Test database query preparation
                $query = "UPDATE assets SET asset_code = ?, name = ?, description = ?, category = ?, purchase_date = ?, purchase_cost = ?, current_value = ?, location = ?, status = ?, condition_status = ?, assigned_to = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt = $db->prepare($query);
                echo "Step 10: Query preparation - OK\n";

                // Step 11: Test query execution with safe values
                $result = $stmt->execute([
                    isset($data->asset_code) ? $data->asset_code : null,
                    $data->name ?? 'Test Asset',
                    isset($data->description) ? $data->description : null,
                    isset($data->category) ? $data->category : null,
                    isset($data->purchase_date) ? $data->purchase_date : null,
                    isset($data->purchase_cost) ? $data->purchase_cost : null,
                    isset($data->current_value) ? $data->current_value : null,
                    isset($data->location) ? $data->location : null,
                    isset($data->status) ? $data->status : 'available',
                    isset($data->condition_status) ? $data->condition_status : 'good',
                    isset($data->assigned_to) ? $data->assigned_to : null,
                    $_GET['id']
                ]);

                if ($result) {
                    echo "Step 11: Query execution - OK\n";
                    echo "SUCCESS: All steps completed\n";
                } else {
                    echo "Step 11: Query execution - FAILED\n";
                }
            } else {
                echo "Step 9: JSON decode - FAILED\n";
            }
        } else {
            echo "Step 8: Input reading - FAILED (empty input)\n";
        }
    } else {
        echo "Step 7: Not a PUT request with ID\n";
    }

    // Final JSON response
    http_response_code(200);
    echo "\n" . json_encode(["message" => "Debug completed successfully"]);

} catch (Exception $e) {
    echo "EXCEPTION at some step: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    http_response_code(500);
    echo json_encode(["message" => "Exception: " . $e->getMessage()]);
} catch (Error $e) {
    echo "FATAL ERROR at some step: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    http_response_code(500);
    echo json_encode(["message" => "Fatal Error: " . $e->getMessage()]);
}
?>