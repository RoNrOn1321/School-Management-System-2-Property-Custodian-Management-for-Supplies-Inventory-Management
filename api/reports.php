<?php
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
                case 'overview':
                    getOverviewReport($db);
                    break;
                case 'assets':
                    getAssetsReport($db);
                    break;
                case 'maintenance':
                    getMaintenanceReport($db);
                    break;
                case 'procurement':
                    getProcurementReport($db);
                    break;
                case 'audit':
                    getAuditReport($db);
                    break;
                case 'financial':
                    getFinancialReport($db);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Invalid action"));
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Action parameter required"));
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function getOverviewReport($db) {
    try {
        $report = array();

        // Assets Overview
        $checkAssets = $db->query("SHOW TABLES LIKE 'assets'");
        if($checkAssets->rowCount() > 0) {
            $query = "SELECT
                        COUNT(*) as total_assets,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_assets,
                        SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned_assets,
                        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_assets,
                        SUM(CASE WHEN status = 'damaged' THEN 1 ELSE 0 END) as damaged_assets,
                        SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_assets
                      FROM assets";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $report['assets'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Maintenance Overview
        $checkMaintenance = $db->query("SHOW TABLES LIKE 'maintenance_schedules'");
        if($checkMaintenance->rowCount() > 0) {
            $query = "SELECT
                        COUNT(*) as total_schedules,
                        SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN scheduled_date < CURDATE() AND status IN ('scheduled', 'in_progress') THEN 1 ELSE 0 END) as overdue
                      FROM maintenance_schedules";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $report['maintenance'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Procurement Overview
        $checkProcurement = $db->query("SHOW TABLES LIKE 'procurement_requests'");
        if($checkProcurement->rowCount() > 0) {
            $query = "SELECT
                        COUNT(*) as total_requests,
                        SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as completed
                      FROM procurement_requests";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $report['procurement'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Audits Overview
        $checkAudits = $db->query("SHOW TABLES LIKE 'property_audits'");
        if($checkAudits->rowCount() > 0) {
            $query = "SELECT
                        COUNT(*) as total_audits,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending_review
                      FROM property_audits";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $report['audits'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        http_response_code(200);
        echo json_encode($report);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error generating overview report", "error" => $e->getMessage()));
    }
}

function getAssetsReport($db) {
    try {
        $report = array();

        $checkAssets = $db->query("SHOW TABLES LIKE 'assets'");
        if($checkAssets->rowCount() == 0) {
            http_response_code(200);
            echo json_encode(array("message" => "No assets data available"));
            return;
        }

        // Asset distribution by category
        $query = "SELECT category, COUNT(*) as count FROM assets GROUP BY category ORDER BY count DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Asset distribution by status
        $query = "SELECT status, COUNT(*) as count FROM assets GROUP BY status";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Asset distribution by location
        $query = "SELECT location, COUNT(*) as count FROM assets WHERE location IS NOT NULL GROUP BY location ORDER BY count DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['by_location'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Monthly asset additions (last 12 months)
        $query = "SELECT
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                  FROM assets
                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['monthly_additions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($report);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error generating assets report", "error" => $e->getMessage()));
    }
}

function getMaintenanceReport($db) {
    try {
        $report = array();

        $checkMaintenance = $db->query("SHOW TABLES LIKE 'maintenance_schedules'");
        if($checkMaintenance->rowCount() == 0) {
            http_response_code(200);
            echo json_encode(array("message" => "No maintenance data available"));
            return;
        }

        // Maintenance by priority
        $query = "SELECT priority, COUNT(*) as count FROM maintenance_schedules GROUP BY priority";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Monthly maintenance completed (last 12 months)
        $query = "SELECT
                    DATE_FORMAT(completed_date, '%Y-%m') as month,
                    COUNT(*) as count
                  FROM maintenance_schedules
                  WHERE status = 'completed' AND completed_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(completed_date, '%Y-%m')
                  ORDER BY month DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['monthly_completed'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Average maintenance cost and duration
        $query = "SELECT
                    AVG(estimated_cost) as avg_cost,
                    AVG(estimated_duration) as avg_duration
                  FROM maintenance_schedules
                  WHERE status = 'completed'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['averages'] = $stmt->fetch(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($report);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error generating maintenance report", "error" => $e->getMessage()));
    }
}

function getProcurementReport($db) {
    try {
        $report = array();

        $checkProcurement = $db->query("SHOW TABLES LIKE 'procurement_requests'");
        if($checkProcurement->rowCount() == 0) {
            http_response_code(200);
            echo json_encode(array("message" => "No procurement data available"));
            return;
        }

        // Procurement by status
        $query = "SELECT status, COUNT(*) as count FROM procurement_requests GROUP BY status";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Monthly procurement requests (last 12 months)
        $query = "SELECT
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count,
                    SUM(estimated_cost) as total_cost
                  FROM procurement_requests
                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['monthly_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top requested items by department (since item_description doesn't exist)
        $query = "SELECT department, COUNT(*) as request_count
                  FROM procurement_requests
                  GROUP BY department
                  ORDER BY request_count DESC
                  LIMIT 10";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['top_items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($report);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error generating procurement report", "error" => $e->getMessage()));
    }
}

function getAuditReport($db) {
    try {
        $report = array();

        $checkAudits = $db->query("SHOW TABLES LIKE 'property_audits'");
        if($checkAudits->rowCount() == 0) {
            http_response_code(200);
            echo json_encode(array("message" => "No audit data available"));
            return;
        }

        // Audit statistics
        $query = "SELECT
                    COUNT(*) as total_audits,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_audits,
                    AVG(DATEDIFF(end_date, start_date)) as avg_duration_days
                  FROM property_audits";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['statistics'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Audits by type
        $query = "SELECT audit_type, COUNT(*) as count FROM property_audits GROUP BY audit_type";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent audit findings
        $query = "SELECT audit_type, department, start_date, end_date, summary
                  FROM property_audits
                  ORDER BY start_date DESC
                  LIMIT 10";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $report['recent_audits'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($report);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error generating audit report", "error" => $e->getMessage()));
    }
}

function getFinancialReport($db) {
    try {
        $report = array();

        // Total asset value
        $checkAssets = $db->query("SHOW TABLES LIKE 'assets'");
        if($checkAssets->rowCount() > 0) {
            $query = "SELECT
                        COUNT(*) as total_assets,
                        SUM(purchase_cost) as total_asset_value,
                        AVG(purchase_cost) as avg_asset_value
                      FROM assets
                      WHERE purchase_cost IS NOT NULL";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $report['asset_values'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Maintenance costs
        $checkMaintenance = $db->query("SHOW TABLES LIKE 'maintenance_schedules'");
        if($checkMaintenance->rowCount() > 0) {
            $query = "SELECT
                        SUM(estimated_cost) as total_maintenance_cost,
                        AVG(estimated_cost) as avg_maintenance_cost,
                        COUNT(*) as total_maintenance_records
                      FROM maintenance_schedules
                      WHERE estimated_cost IS NOT NULL";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $report['maintenance_costs'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Procurement costs
        $checkProcurement = $db->query("SHOW TABLES LIKE 'procurement_requests'");
        if($checkProcurement->rowCount() > 0) {
            $query = "SELECT
                        SUM(estimated_cost) as total_procurement_cost,
                        AVG(estimated_cost) as avg_procurement_cost,
                        COUNT(*) as total_procurement_requests
                      FROM procurement_requests
                      WHERE estimated_cost IS NOT NULL";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $report['procurement_costs'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Monthly financial summary (last 12 months)
        $financial_timeline = array();

        if($checkProcurement->rowCount() > 0) {
            $query = "SELECT
                        DATE_FORMAT(request_date, '%Y-%m') as month,
                        SUM(estimated_cost) as procurement_cost
                      FROM procurement_requests
                      WHERE request_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                        AND estimated_cost IS NOT NULL
                      GROUP BY DATE_FORMAT(request_date, '%Y-%m')
                      ORDER BY month DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $financial_timeline['procurement'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if($checkMaintenance->rowCount() > 0) {
            $query = "SELECT
                        DATE_FORMAT(scheduled_date, '%Y-%m') as month,
                        SUM(estimated_cost) as maintenance_cost
                      FROM maintenance_schedules
                      WHERE scheduled_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                        AND estimated_cost IS NOT NULL
                      GROUP BY DATE_FORMAT(scheduled_date, '%Y-%m')
                      ORDER BY month DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $financial_timeline['maintenance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $report['timeline'] = $financial_timeline;

        http_response_code(200);
        echo json_encode($report);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error generating financial report", "error" => $e->getMessage()));
    }
}
?>