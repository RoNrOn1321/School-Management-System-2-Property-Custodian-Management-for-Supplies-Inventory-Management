<?php
require_once '../config/cors.php';
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        http_response_code(500);
        echo json_encode(array("error" => "Database connection failed"));
        exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("error" => "Server error", "message" => $e->getMessage()));
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        if(isset($_GET['action'])) {
            if($_GET['action'] === 'login') {
                login($db);
            } elseif($_GET['action'] === 'logout') {
                logout();
            }
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function login($db) {
    try {
        $data = json_decode(file_get_contents("php://input"));

        if(!$data) {
            http_response_code(400);
            echo json_encode(array("error" => "Invalid JSON data"));
            return;
        }

        if(!empty($data->username) && !empty($data->password)) {
        $query = "SELECT id, username, password, full_name, email, role, department, status FROM users WHERE username = ? AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $data->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // For demo purposes, using simple password check
            // In production, use password_verify() with hashed passwords
            $demo_passwords = [
                'admin' => 'admin123',
                'custodian' => 'custodian123',
                'staff' => 'staff123'
            ];

            if(isset($demo_passwords[$data->username]) && $demo_passwords[$data->username] === $data->password) {
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                // Log the login
                logActivity($db, $row['id'], 'login', 'users', $row['id']);

                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login successful",
                    "user" => array(
                        "id" => $row['id'],
                        "username" => $row['username'],
                        "full_name" => $row['full_name'],
                        "email" => $row['email'],
                        "role" => $row['role'],
                        "department" => $row['department']
                    )
                ));
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Invalid credentials"));
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "User not found"));
        }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Username and password required"));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("error" => "Login error", "message" => $e->getMessage()));
    }
}

function logout() {
    session_start();
    session_destroy();
    http_response_code(200);
    echo json_encode(array("message" => "Logout successful"));
}

function logActivity($db, $user_id, $action, $table_name, $record_id) {
    $query = "INSERT INTO system_logs (user_id, action, table_name, record_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id, $action, $table_name, $record_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
}
?>