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
            getSupply($db, $_GET['id']);
        } elseif(isset($_GET['action']) && $_GET['action'] === 'transactions') {
            getTransactions($db);
        } else {
            getSupplies($db);
        }
        break;
    case 'POST':
        if(isset($_GET['action']) && $_GET['action'] === 'transaction') {
            createTransaction($db);
        } else {
            createSupply($db);
        }
        break;
    case 'PUT':
        if(isset($_GET['id'])) {
            updateSupply($db, $_GET['id']);
        }
        break;
    case 'DELETE':
        if(isset($_GET['id'])) {
            deleteSupply($db, $_GET['id']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function getSupplies($db) {
    $query = "SELECT * FROM supplies ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $supplies = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $supplies[] = $row;
    }

    http_response_code(200);
    echo json_encode($supplies);
}

function getSupply($db, $id) {
    $query = "SELECT * FROM supplies WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $supply = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode($supply);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Supply not found"));
    }
}

function getTransactions($db) {
    $query = "SELECT st.*, s.name as supply_name, u1.full_name as requested_by_name, u2.full_name as approved_by_name
              FROM supply_transactions st
              JOIN supplies s ON st.supply_id = s.id
              LEFT JOIN users u1 ON st.requested_by = u1.id
              LEFT JOIN users u2 ON st.approved_by = u2.id
              ORDER BY st.created_at DESC
              LIMIT 50";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $transactions = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $transactions[] = $row;
    }

    http_response_code(200);
    echo json_encode($transactions);
}

function createSupply($db) {
    $data = json_decode(file_get_contents("php://input"));

    if(!empty($data->item_code) && !empty($data->name)) {
        $query = "INSERT INTO supplies (item_code, name, description, category, unit, current_stock, minimum_stock, maximum_stock, unit_cost, supplier, storage_location, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($query);

        if($stmt->execute([
            $data->item_code,
            $data->name,
            $data->description ?? null,
            $data->category ?? null,
            $data->unit ?? 'pcs',
            $data->current_stock ?? 0,
            $data->minimum_stock ?? 0,
            $data->maximum_stock ?? 0,
            $data->unit_cost ?? null,
            $data->supplier ?? null,
            $data->storage_location ?? null,
            $data->expiry_date ?? null
        ])) {
            $supply_id = $db->lastInsertId();

            // Log the activity
            logActivity($db, $_SESSION['user_id'], 'create', 'supplies', $supply_id);

            http_response_code(201);
            echo json_encode(array("message" => "Supply created successfully", "id" => $supply_id));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to create supply"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Item code and name are required"));
    }
}

function createTransaction($db) {
    $data = json_decode(file_get_contents("php://input"));

    if(!empty($data->supply_id) && !empty($data->transaction_type) && isset($data->quantity)) {
        $db->beginTransaction();

        try {
            // Insert transaction
            $query = "INSERT INTO supply_transactions (supply_id, transaction_type, quantity, reference_number, transaction_date, requested_by, approved_by, purpose, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($query);
            $stmt->execute([
                $data->supply_id,
                $data->transaction_type,
                $data->quantity,
                $data->reference_number ?? null,
                $data->transaction_date ?? date('Y-m-d'),
                $_SESSION['user_id'],
                $data->approved_by ?? $_SESSION['user_id'],
                $data->purpose ?? null,
                $data->notes ?? null
            ]);

            $transaction_id = $db->lastInsertId();

            // Update supply stock
            if($data->transaction_type === 'in') {
                $query = "UPDATE supplies SET current_stock = current_stock + ? WHERE id = ?";
            } else {
                $query = "UPDATE supplies SET current_stock = current_stock - ? WHERE id = ?";
            }

            $stmt = $db->prepare($query);
            $stmt->execute([$data->quantity, $data->supply_id]);

            $db->commit();

            // Log the activity
            logActivity($db, $_SESSION['user_id'], 'create', 'supply_transactions', $transaction_id);

            http_response_code(201);
            echo json_encode(array("message" => "Transaction created successfully", "id" => $transaction_id));

        } catch(Exception $e) {
            $db->rollback();
            http_response_code(500);
            echo json_encode(array("message" => "Failed to create transaction"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Supply ID, transaction type and quantity are required"));
    }
}

function updateSupply($db, $id) {
    $data = json_decode(file_get_contents("php://input"));

    $query = "UPDATE supplies SET name = ?, description = ?, category = ?, unit = ?, minimum_stock = ?, maximum_stock = ?, unit_cost = ?, supplier = ?, storage_location = ?, expiry_date = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = $db->prepare($query);

    if($stmt->execute([
        $data->name,
        $data->description ?? null,
        $data->category ?? null,
        $data->unit ?? 'pcs',
        $data->minimum_stock ?? 0,
        $data->maximum_stock ?? 0,
        $data->unit_cost ?? null,
        $data->supplier ?? null,
        $data->storage_location ?? null,
        $data->expiry_date ?? null,
        $data->status ?? 'active',
        $id
    ])) {
        // Log the activity
        logActivity($db, $_SESSION['user_id'], 'update', 'supplies', $id);

        http_response_code(200);
        echo json_encode(array("message" => "Supply updated successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update supply"));
    }
}

function deleteSupply($db, $id) {
    $query = "DELETE FROM supplies WHERE id = ?";
    $stmt = $db->prepare($query);

    if($stmt->execute([$id])) {
        // Log the activity
        logActivity($db, $_SESSION['user_id'], 'delete', 'supplies', $id);

        http_response_code(200);
        echo json_encode(array("message" => "Supply deleted successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to delete supply"));
    }
}

function logActivity($db, $user_id, $action, $table_name, $record_id) {
    $query = "INSERT INTO system_logs (user_id, action, table_name, record_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id, $action, $table_name, $record_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
}
?>