<?php
require_once '../config/cors.php';
require_once '../config/database.php';

session_start();
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized"));
    exit();
}

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            getAsset($db, $_GET['id']);
        } else {
            getAssets($db);
        }
        break;
    case 'POST':
        createAsset($db);
        break;
    case 'PUT':
        if(isset($_GET['id'])) {
            updateAsset($db, $_GET['id']);
        }
        break;
    case 'DELETE':
        if(isset($_GET['id'])) {
            deleteAsset($db, $_GET['id']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function getAssets($db) {
    $query = "SELECT a.*, ac.name as category_name
              FROM assets a
              LEFT JOIN asset_categories ac ON a.category_id = ac.id
              ORDER BY a.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $assets = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $assets[] = $row;
    }

    http_response_code(200);
    echo json_encode($assets);
}

function getAsset($db, $id) {
    $query = "SELECT a.*, ac.name as category_name
              FROM assets a
              LEFT JOIN asset_categories ac ON a.category_id = ac.id
              WHERE a.id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode($asset);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Asset not found"));
    }
}

function createAsset($db) {
    $data = json_decode(file_get_contents("php://input"));

    if(!empty($data->asset_code) && !empty($data->name)) {
        $query = "INSERT INTO assets (asset_code, name, description, category_id, brand, model, serial_number, purchase_date, purchase_cost, current_value, location, condition_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($query);

        if($stmt->execute([
            $data->asset_code,
            $data->name,
            $data->description ?? null,
            $data->category_id ?? null,
            $data->brand ?? null,
            $data->model ?? null,
            $data->serial_number ?? null,
            $data->purchase_date ?? null,
            $data->purchase_cost ?? null,
            $data->current_value ?? null,
            $data->location ?? null,
            $data->condition_status ?? 'good'
        ])) {
            $asset_id = $db->lastInsertId();

            // Log the activity
            logActivity($db, $_SESSION['user_id'], 'create', 'assets', $asset_id);

            http_response_code(201);
            echo json_encode(array("message" => "Asset created successfully", "id" => $asset_id));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to create asset"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Asset code and name are required"));
    }
}

function updateAsset($db, $id) {
    $data = json_decode(file_get_contents("php://input"));

    $query = "UPDATE assets SET name = ?, description = ?, category_id = ?, brand = ?, model = ?, serial_number = ?, purchase_date = ?, purchase_cost = ?, current_value = ?, location = ?, status = ?, condition_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = $db->prepare($query);

    if($stmt->execute([
        $data->name,
        $data->description ?? null,
        $data->category_id ?? null,
        $data->brand ?? null,
        $data->model ?? null,
        $data->serial_number ?? null,
        $data->purchase_date ?? null,
        $data->purchase_cost ?? null,
        $data->current_value ?? null,
        $data->location ?? null,
        $data->status ?? 'available',
        $data->condition_status ?? 'good',
        $id
    ])) {
        // Log the activity
        logActivity($db, $_SESSION['user_id'], 'update', 'assets', $id);

        http_response_code(200);
        echo json_encode(array("message" => "Asset updated successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update asset"));
    }
}

function deleteAsset($db, $id) {
    $query = "DELETE FROM assets WHERE id = ?";
    $stmt = $db->prepare($query);

    if($stmt->execute([$id])) {
        // Log the activity
        logActivity($db, $_SESSION['user_id'], 'delete', 'assets', $id);

        http_response_code(200);
        echo json_encode(array("message" => "Asset deleted successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to delete asset"));
    }
}

function logActivity($db, $user_id, $action, $table_name, $record_id) {
    $query = "INSERT INTO system_logs (user_id, action, table_name, record_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id, $action, $table_name, $record_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
}
?>