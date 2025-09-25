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
        if(isset($_GET['action'])) {
            switch($_GET['action']) {
                case 'stats':
                    getStats($db);
                    break;
                case 'recent_activities':
                    getRecentActivities($db);
                    break;
                case 'alerts':
                    getAlerts($db);
                    break;
            }
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function getStats($db) {
    $stats = array();

    // Total assets
    $query = "SELECT COUNT(*) as total FROM assets";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalAssets'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Available assets
    $query = "SELECT COUNT(*) as total FROM assets WHERE status = 'available'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['availableItems'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Maintenance items
    $query = "SELECT COUNT(*) as total FROM assets WHERE status = 'maintenance'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['maintenanceItems'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Damaged/Lost items
    $query = "SELECT COUNT(*) as total FROM assets WHERE status IN ('damaged', 'lost')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['damagedItems'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    http_response_code(200);
    echo json_encode($stats);
}

function getRecentActivities($db) {
    $query = "SELECT sl.action, sl.table_name, sl.created_at, u.full_name
              FROM system_logs sl
              JOIN users u ON sl.user_id = u.id
              ORDER BY sl.created_at DESC
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $activities = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = array(
            "action" => $row['action'],
            "table_name" => $row['table_name'],
            "user" => $row['full_name'],
            "created_at" => $row['created_at']
        );
    }

    http_response_code(200);
    echo json_encode($activities);
}

function getAlerts($db) {
    $alerts = array();

    // Low stock supplies
    $query = "SELECT name, current_stock, minimum_stock FROM supplies WHERE current_stock <= minimum_stock AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $alerts[] = array(
            "type" => "low_stock",
            "title" => "Low Stock Alert",
            "message" => "Supply '{$row['name']}' is running low ({$row['current_stock']} remaining)",
            "priority" => "medium"
        );
    }

    // Overdue maintenance
    $query = "SELECT a.name FROM maintenance_schedules ms
              JOIN assets a ON ms.asset_id = a.id
              WHERE ms.scheduled_date < CURDATE() AND ms.status = 'scheduled'";
    $stmt = $db->prepare($query);
    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $alerts[] = array(
            "type" => "overdue_maintenance",
            "title" => "Overdue Maintenance",
            "message" => "Asset '{$row['name']}' has overdue maintenance",
            "priority" => "high"
        );
    }

    // Expired supplies
    $query = "SELECT name, expiry_date FROM supplies WHERE expiry_date < CURDATE() AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $alerts[] = array(
            "type" => "expired_supplies",
            "title" => "Expired Supplies",
            "message" => "Supply '{$row['name']}' expired on {$row['expiry_date']}",
            "priority" => "high"
        );
    }

    http_response_code(200);
    echo json_encode($alerts);
}
?>