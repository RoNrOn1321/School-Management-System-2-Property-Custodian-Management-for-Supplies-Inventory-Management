<?php
require_once '../config/cors.php';
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if($db) {
        echo json_encode(array(
            "status" => "success",
            "message" => "Database connection successful"
        ));
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Database connection failed"
        ));
    }
} catch(Exception $e) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ));
}
?>