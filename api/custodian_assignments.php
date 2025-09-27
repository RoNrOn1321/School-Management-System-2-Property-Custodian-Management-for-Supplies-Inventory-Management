<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'custodians') {
                getCustodians($db);
            } elseif ($action === 'available_assets') {
                getAvailableAssets($db);
            } elseif ($action === 'assignments') {
                getAssignments($db);
            } elseif ($action === 'assignment_details') {
                getAssignmentDetails($db, $_GET['id'] ?? null);
            } else {
                getAssignments($db);
            }
            break;

        case 'POST':
            if ($action === 'create_custodian') {
                createCustodian($db);
            } elseif ($action === 'create_assignment') {
                createAssignment($db);
            } else {
                createAssignment($db);
            }
            break;

        case 'PUT':
            if ($action === 'update_assignment') {
                updateAssignment($db, $_GET['id'] ?? null);
            }
            break;

        case 'DELETE':
            if ($action === 'delete_assignment') {
                deleteAssignment($db, $_GET['id'] ?? null);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}

function getCustodians($db) {
    $query = "SELECT c.*, u.full_name, u.email, u.department as user_department
              FROM custodians c
              LEFT JOIN users u ON c.user_id = u.id
              WHERE c.status = 'active'
              ORDER BY c.employee_id";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $custodians = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $custodians]);
}

function getAvailableAssets($db) {
    $query = "SELECT a.*, a.category as category_name
              FROM assets a
              WHERE a.status = 'available'
              ORDER BY a.name";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $assets]);
}

function getAssignments($db) {
    $query = "SELECT pa.*,
                     c.employee_id, c.department as custodian_department, c.position,
                     u.full_name as custodian_name, u.email as custodian_email,
                     a.asset_code, a.name as asset_name, a.description as asset_description,
                     a.category as asset_category,
                     assigned_user.full_name as assigned_by_name
              FROM property_assignments pa
              JOIN custodians c ON pa.custodian_id = c.id
              LEFT JOIN users u ON c.user_id = u.id
              JOIN assets a ON pa.asset_id = a.id
              LEFT JOIN users assigned_user ON pa.assigned_by = assigned_user.id
              WHERE pa.status = 'active'
              ORDER BY pa.assignment_date DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $assignments]);
}

function getAssignmentDetails($db, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Assignment ID required']);
        return;
    }

    $query = "SELECT pa.*,
                     c.employee_id, c.department as custodian_department, c.position, c.contact_number,
                     u.full_name as custodian_name, u.email as custodian_email,
                     a.asset_code, a.name as asset_name, a.description as asset_description,
                     a.category as asset_category,
                     assigned_user.full_name as assigned_by_name
              FROM property_assignments pa
              JOIN custodians c ON pa.custodian_id = c.id
              LEFT JOIN users u ON c.user_id = u.id
              JOIN assets a ON pa.asset_id = a.id
              LEFT JOIN users assigned_user ON pa.assigned_by = assigned_user.id
              WHERE pa.id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($assignment) {
        echo json_encode(['data' => $assignment]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Assignment not found']);
    }
}

function createCustodian($db) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['employee_id']) || !isset($input['full_name']) || !isset($input['department'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Required fields missing']);
        return;
    }

    $db->beginTransaction();

    try {
        // First create user if email is provided
        $user_id = null;
        if (!empty($input['email'])) {
            $user_query = "INSERT INTO users (username, password, full_name, email, role, department)
                          VALUES (:username, :password, :full_name, :email, 'custodian', :department)";
            $user_stmt = $db->prepare($user_query);
            $hashed_password = password_hash('custodian123', PASSWORD_DEFAULT);
            $user_stmt->bindParam(':username', $input['employee_id']);
            $user_stmt->bindParam(':password', $hashed_password);
            $user_stmt->bindParam(':full_name', $input['full_name']);
            $user_stmt->bindParam(':email', $input['email']);
            $user_stmt->bindParam(':department', $input['department']);
            $user_stmt->execute();
            $user_id = $db->lastInsertId();
        }

        // Create custodian record
        $custodian_query = "INSERT INTO custodians (user_id, employee_id, department, position, contact_number, office_location)
                           VALUES (:user_id, :employee_id, :department, :position, :contact_number, :office_location)";
        $custodian_stmt = $db->prepare($custodian_query);
        $position = $input['position'] ?? null;
        $contact_number = $input['contact_number'] ?? null;
        $office_location = $input['office_location'] ?? null;
        $custodian_stmt->bindParam(':user_id', $user_id);
        $custodian_stmt->bindParam(':employee_id', $input['employee_id']);
        $custodian_stmt->bindParam(':department', $input['department']);
        $custodian_stmt->bindParam(':position', $position);
        $custodian_stmt->bindParam(':contact_number', $contact_number);
        $custodian_stmt->bindParam(':office_location', $office_location);
        $custodian_stmt->execute();

        $custodian_id = $db->lastInsertId();

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Custodian created successfully',
            'custodian_id' => $custodian_id
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create custodian', 'message' => $e->getMessage()]);
    }
}

function createAssignment($db) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['custodian_id']) || !isset($input['asset_id']) || !isset($input['assignment_date'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Required fields missing']);
        return;
    }

    $db->beginTransaction();

    try {
        // Check if asset is available
        $asset_check = "SELECT status FROM assets WHERE id = :asset_id";
        $asset_stmt = $db->prepare($asset_check);
        $asset_stmt->bindParam(':asset_id', $input['asset_id']);
        $asset_stmt->execute();
        $asset = $asset_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$asset || $asset['status'] !== 'available') {
            http_response_code(400);
            echo json_encode(['error' => 'Asset is not available for assignment']);
            return;
        }

        // Create assignment
        $assignment_query = "INSERT INTO property_assignments (asset_id, custodian_id, assigned_by, assignment_date,
                            expected_return_date, assignment_purpose, conditions, notes)
                            VALUES (:asset_id, :custodian_id, :assigned_by, :assignment_date,
                            :expected_return_date, :assignment_purpose, :conditions, :notes)";

        $assignment_stmt = $db->prepare($assignment_query);
        $expected_return_date = $input['expected_return_date'] ?? null;
        $assignment_purpose = $input['assignment_purpose'] ?? null;
        $conditions = $input['conditions'] ?? null;
        $notes = $input['notes'] ?? null;
        $assignment_stmt->bindParam(':asset_id', $input['asset_id']);
        $assignment_stmt->bindParam(':custodian_id', $input['custodian_id']);
        $assignment_stmt->bindParam(':assigned_by', $_SESSION['user_id']);
        $assignment_stmt->bindParam(':assignment_date', $input['assignment_date']);
        $assignment_stmt->bindParam(':expected_return_date', $expected_return_date);
        $assignment_stmt->bindParam(':assignment_purpose', $assignment_purpose);
        $assignment_stmt->bindParam(':conditions', $conditions);
        $assignment_stmt->bindParam(':notes', $notes);
        $assignment_stmt->execute();

        $assignment_id = $db->lastInsertId();

        // Update asset status
        $update_asset = "UPDATE assets SET status = 'assigned' WHERE id = :asset_id";
        $update_stmt = $db->prepare($update_asset);
        $update_stmt->bindParam(':asset_id', $input['asset_id']);
        $update_stmt->execute();

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Assignment created successfully',
            'assignment_id' => $assignment_id
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create assignment', 'message' => $e->getMessage()]);
    }
}

function updateAssignment($db, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Assignment ID required']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        return;
    }

    try {
        $query = "UPDATE property_assignments SET ";
        $params = [];
        $setClauses = [];

        if (isset($input['expected_return_date'])) {
            $setClauses[] = "expected_return_date = :expected_return_date";
            $params[':expected_return_date'] = $input['expected_return_date'];
        }

        if (isset($input['assignment_purpose'])) {
            $setClauses[] = "assignment_purpose = :assignment_purpose";
            $params[':assignment_purpose'] = $input['assignment_purpose'];
        }

        if (isset($input['conditions'])) {
            $setClauses[] = "conditions = :conditions";
            $params[':conditions'] = $input['conditions'];
        }

        if (isset($input['notes'])) {
            $setClauses[] = "notes = :notes";
            $params[':notes'] = $input['notes'];
        }

        if (isset($input['status'])) {
            $setClauses[] = "status = :status";
            $params[':status'] = $input['status'];

            // If returning the asset, update asset status and set return date
            if ($input['status'] === 'returned') {
                $setClauses[] = "actual_return_date = CURDATE()";

                // Update asset status back to available
                $asset_update = "UPDATE assets a
                               JOIN property_assignments pa ON a.id = pa.asset_id
                               SET a.status = 'available'
                               WHERE pa.id = :assignment_id";
                $asset_stmt = $db->prepare($asset_update);
                $asset_stmt->bindParam(':assignment_id', $id);
                $asset_stmt->execute();
            }
        }

        if (empty($setClauses)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid fields to update']);
            return;
        }

        $query .= implode(', ', $setClauses) . " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Assignment updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Assignment not found or no changes made']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update assignment', 'message' => $e->getMessage()]);
    }
}

function deleteAssignment($db, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Assignment ID required']);
        return;
    }

    $db->beginTransaction();

    try {
        // Get asset ID before deleting assignment
        $asset_query = "SELECT asset_id FROM property_assignments WHERE id = :id";
        $asset_stmt = $db->prepare($asset_query);
        $asset_stmt->bindParam(':id', $id);
        $asset_stmt->execute();
        $assignment = $asset_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignment) {
            http_response_code(404);
            echo json_encode(['error' => 'Assignment not found']);
            return;
        }

        // Delete assignment
        $delete_query = "DELETE FROM property_assignments WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':id', $id);
        $delete_stmt->execute();

        // Update asset status back to available
        $update_asset = "UPDATE assets SET status = 'available' WHERE id = :asset_id";
        $update_stmt = $db->prepare($update_asset);
        $update_stmt->bindParam(':asset_id', $assignment['asset_id']);
        $update_stmt->execute();

        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully']);

    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete assignment', 'message' => $e->getMessage()]);
    }
}
?>