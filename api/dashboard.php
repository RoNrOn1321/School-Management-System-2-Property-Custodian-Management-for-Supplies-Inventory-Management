<?php
// Prevent HTML error output
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

session_start();
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized"));
    exit();
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
    try {
        // Check if assets table exists
        $checkTable = $db->query("SHOW TABLES LIKE 'assets'");
        if($checkTable->rowCount() == 0) {
            // Return default stats if table doesn't exist
            $stats = array(
                'totalAssets' => 0,
                'availableItems' => 0,
                'maintenanceItems' => 0,
                'damagedItems' => 0
            );
            http_response_code(200);
            echo json_encode($stats);
            return;
        }

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
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error fetching stats", "error" => $e->getMessage()));
    }
}

function getRecentActivities($db) {
    try {
        // Check if required tables exist
        $checkSystemLogs = $db->query("SHOW TABLES LIKE 'system_logs'");
        $checkUsers = $db->query("SHOW TABLES LIKE 'users'");

        if($checkSystemLogs->rowCount() == 0 || $checkUsers->rowCount() == 0) {
            // Return empty activities if tables don't exist
            http_response_code(200);
            echo json_encode(array());
            return;
        }

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
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error fetching activities", "error" => $e->getMessage()));
    }
}

function getAlerts($db) {
    try {
        $alerts = array();

        // Check if supplies table exists for supply alerts
        $checkSupplies = $db->query("SHOW TABLES LIKE 'supplies'");
        if($checkSupplies->rowCount() > 0) {
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
        }

        // Check if maintenance tables exist for maintenance alerts
        $checkMaintenance = $db->query("SHOW TABLES LIKE 'maintenance_schedules'");
        $checkAssets = $db->query("SHOW TABLES LIKE 'assets'");
        if($checkMaintenance->rowCount() > 0 && $checkAssets->rowCount() > 0) {
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
        }

        http_response_code(200);
        echo json_encode($alerts);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error fetching alerts", "error" => $e->getMessage()));
    }
}
?>