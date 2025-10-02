<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/config.php';

class UsersAPI {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet($action);
                    break;
                case 'POST':
                    $this->handlePost($action);
                    break;
                case 'PUT':
                    $this->handlePut($action);
                    break;
                case 'DELETE':
                    $this->handleDelete($action);
                    break;
                default:
                    $this->sendError('Method not allowed', 405);
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function handleGet($action) {
        switch ($action) {
            case 'list':
                $this->getUsers();
                break;
            case 'details':
                $this->getUserDetails();
                break;
            case 'roles':
                $this->getRoles();
                break;
            case 'stats':
                $this->getUserStats();
                break;
            default:
                $this->getUsers();
        }
    }

    private function handlePost($action) {
        switch ($action) {
            case 'create':
                $this->createUser();
                break;
            default:
                $this->sendError('Invalid action', 400);
        }
    }

    private function handlePut($action) {
        switch ($action) {
            case 'update':
                $this->updateUser();
                break;
            case 'toggle_status':
                $this->toggleUserStatus();
                break;
            default:
                $this->sendError('Invalid action', 400);
        }
    }

    private function handleDelete($action) {
        switch ($action) {
            case 'delete':
                $this->deleteUser();
                break;
            default:
                $this->sendError('Invalid action', 400);
        }
    }

    private function getUsers() {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? ITEMS_PER_PAGE;
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';

        $offset = ($page - 1) * $limit;

        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($role)) {
            $whereConditions[] = "role = ?";
            $params[] = $role;
        }

        if (!empty($status)) {
            $whereConditions[] = "status = ?";
            $params[] = $status;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get users
        $query = "SELECT id, username, full_name, email, role, department, status, created_at, updated_at
                  FROM users $whereClause
                  ORDER BY created_at DESC
                  LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add role display names
        foreach ($users as &$user) {
            $user['role_display'] = USER_ROLES[$user['role']]['name'] ?? ucfirst($user['role']);
            $user['permissions'] = USER_ROLES[$user['role']]['permissions'] ?? [];
        }

        $this->sendSuccess([
            'users' => $users,
            'pagination' => [
                'total' => (int)$total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    private function getUserDetails() {
        $id = $_GET['id'] ?? 0;

        if (!$id) {
            $this->sendError('User ID is required', 400);
            return;
        }

        $query = "SELECT id, username, full_name, email, role, department, status, created_at, updated_at
                  FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->sendError('User not found', 404);
            return;
        }

        $user['role_display'] = USER_ROLES[$user['role']]['name'] ?? ucfirst($user['role']);
        $user['permissions'] = USER_ROLES[$user['role']]['permissions'] ?? [];

        // Get user's assigned assets count
        $assetQuery = "SELECT COUNT(*) as assigned_assets FROM assets WHERE assigned_to = ?";
        $assetStmt = $this->db->prepare($assetQuery);
        $assetStmt->execute([$id]);
        $user['assigned_assets'] = $assetStmt->fetch(PDO::FETCH_ASSOC)['assigned_assets'];

        $this->sendSuccess($user);
    }

    private function getRoles() {
        $roles = [];
        foreach (USER_ROLES as $key => $role) {
            $roles[] = [
                'key' => $key,
                'name' => $role['name'],
                'permissions' => $role['permissions']
            ];
        }

        $this->sendSuccess(['roles' => $roles]);
    }

    private function getUserStats() {
        // Total users by role
        $roleQuery = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
        $roleStmt = $this->db->prepare($roleQuery);
        $roleStmt->execute();
        $roleStats = $roleStmt->fetchAll(PDO::FETCH_ASSOC);

        // Total users by status
        $statusQuery = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
        $statusStmt = $this->db->prepare($statusQuery);
        $statusStmt->execute();
        $statusStats = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent users (last 30 days)
        $recentQuery = "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $recentStmt = $this->db->prepare($recentQuery);
        $recentStmt->execute();
        $recentUsers = $recentStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $this->sendSuccess([
            'role_stats' => $roleStats,
            'status_stats' => $statusStats,
            'recent_users' => $recentUsers,
            'total_users' => array_sum(array_column($roleStats, 'count'))
        ]);
    }

    private function createUser() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->sendError('Invalid JSON input', 400);
            return;
        }

        $required = ['username', 'password', 'full_name', 'role'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->sendError("Field '$field' is required", 400);
                return;
            }
        }

        // Validate role
        if (!array_key_exists($input['role'], USER_ROLES)) {
            $this->sendError('Invalid role', 400);
            return;
        }

        // Check if username already exists
        $checkQuery = "SELECT id FROM users WHERE username = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([$input['username']]);
        if ($checkStmt->fetch()) {
            $this->sendError('Username already exists', 409);
            return;
        }

        // Check if email already exists (if provided)
        if (!empty($input['email'])) {
            $emailCheckQuery = "SELECT id FROM users WHERE email = ?";
            $emailCheckStmt = $this->db->prepare($emailCheckQuery);
            $emailCheckStmt->execute([$input['email']]);
            if ($emailCheckStmt->fetch()) {
                $this->sendError('Email already exists', 409);
                return;
            }
        }

        // Hash password
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, password, full_name, email, role, department, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $input['username'],
            $hashedPassword,
            $input['full_name'],
            $input['email'] ?? null,
            $input['role'],
            $input['department'] ?? null,
            $input['status'] ?? 'active'
        ]);

        if ($result) {
            $userId = $this->db->lastInsertId();
            $this->sendSuccess([
                'message' => 'User created successfully',
                'user_id' => $userId
            ], 201);
        } else {
            $this->sendError('Failed to create user', 500);
        }
    }

    private function updateUser() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['id'])) {
            $this->sendError('User ID is required', 400);
            return;
        }

        $userId = $input['id'];

        // Check if user exists
        $checkQuery = "SELECT id FROM users WHERE id = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([$userId]);
        if (!$checkStmt->fetch()) {
            $this->sendError('User not found', 404);
            return;
        }

        $updateFields = [];
        $params = [];

        if (isset($input['full_name'])) {
            $updateFields[] = "full_name = ?";
            $params[] = $input['full_name'];
        }

        if (isset($input['email'])) {
            // Check if email already exists for different user
            if (!empty($input['email'])) {
                $emailCheckQuery = "SELECT id FROM users WHERE email = ? AND id != ?";
                $emailCheckStmt = $this->db->prepare($emailCheckQuery);
                $emailCheckStmt->execute([$input['email'], $userId]);
                if ($emailCheckStmt->fetch()) {
                    $this->sendError('Email already exists', 409);
                    return;
                }
            }
            $updateFields[] = "email = ?";
            $params[] = $input['email'];
        }

        if (isset($input['role']) && array_key_exists($input['role'], USER_ROLES)) {
            $updateFields[] = "role = ?";
            $params[] = $input['role'];
        }

        if (isset($input['department'])) {
            $updateFields[] = "department = ?";
            $params[] = $input['department'];
        }

        if (isset($input['status'])) {
            $updateFields[] = "status = ?";
            $params[] = $input['status'];
        }

        if (isset($input['password']) && !empty($input['password'])) {
            $updateFields[] = "password = ?";
            $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if (empty($updateFields)) {
            $this->sendError('No fields to update', 400);
            return;
        }

        $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $userId;

        $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($params);

        if ($result) {
            $this->sendSuccess(['message' => 'User updated successfully']);
        } else {
            $this->sendError('Failed to update user', 500);
        }
    }

    private function toggleUserStatus() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['id'])) {
            $this->sendError('User ID is required', 400);
            return;
        }

        $userId = $input['id'];

        $query = "UPDATE users SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END,
                  updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$userId]);

        if ($result && $stmt->rowCount() > 0) {
            $this->sendSuccess(['message' => 'User status updated successfully']);
        } else {
            $this->sendError('User not found or status unchanged', 404);
        }
    }

    private function deleteUser() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['id'])) {
            $this->sendError('User ID is required', 400);
            return;
        }

        $userId = $input['id'];

        // Check if user has assigned assets
        $assetQuery = "SELECT COUNT(*) as count FROM assets WHERE assigned_to = ?";
        $assetStmt = $this->db->prepare($assetQuery);
        $assetStmt->execute([$userId]);
        $assetCount = $assetStmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($assetCount > 0) {
            $this->sendError('Cannot delete user with assigned assets. Please reassign assets first.', 400);
            return;
        }

        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$userId]);

        if ($result && $stmt->rowCount() > 0) {
            $this->sendSuccess(['message' => 'User deleted successfully']);
        } else {
            $this->sendError('User not found', 404);
        }
    }

    private function sendSuccess($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    private function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}

$api = new UsersAPI();
$api->handleRequest();
?>