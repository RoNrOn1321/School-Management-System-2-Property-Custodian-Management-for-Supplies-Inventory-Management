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
            } elseif($_GET['action'] === 'register') {
                register($db);
            }
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function register($db) {
    try {
        $data = json_decode(file_get_contents("php://input"));

        if(!$data) {
            http_response_code(400);
            echo json_encode(array("error" => "Invalid JSON data"));
            return;
        }

        // Validate required fields
        if(empty($data->username) || empty($data->password) || empty($data->full_name) || empty($data->email)) {
            http_response_code(400);
            echo json_encode(array("error" => "All fields are required: username, password, full_name, email"));
            return;
        }

        // Validate email format
        if(!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(array("error" => "Invalid email format"));
            return;
        }

        // Check if username already exists
        $query = "SELECT id FROM users WHERE username = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $data->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(array("error" => "Username already exists"));
            return;
        }

        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $data->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(array("error" => "Email already exists"));
            return;
        }

        // Set default role if not provided
        $role = isset($data->role) ? $data->role : 'staff';
        $department = isset($data->department) ? $data->department : 'General';

        // Hash password for production use
        $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);

        // Insert new user
        $query = "INSERT INTO users (username, password, full_name, email, role, department, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())";
        $stmt = $db->prepare($query);

        if($stmt->execute([$data->username, $hashedPassword, $data->full_name, $data->email, $role, $department])) {
            $userId = $db->lastInsertId();

            // Log the registration
            logActivity($db, $userId, 'register', 'users', $userId);

            http_response_code(201);
            echo json_encode(array(
                "message" => "User registered successfully",
                "user" => array(
                    "id" => $userId,
                    "username" => $data->username,
                    "full_name" => $data->full_name,
                    "email" => $data->email,
                    "role" => $role,
                    "department" => $department
                )
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("error" => "Failed to register user"));
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("error" => "Registration error", "message" => $e->getMessage()));
    }
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

            // Check password - support both hashed passwords and demo accounts
            $demo_passwords = [
                'admin' => 'admin123',
                'custodian' => 'custodian123',
                'staff' => 'staff123'
            ];

            $passwordValid = false;

            // First try hashed password verification
            if(password_verify($data->password, $row['password'])) {
                $passwordValid = true;
            }
            // Fallback to demo password check for existing demo accounts
            elseif(isset($demo_passwords[$data->username]) && $demo_passwords[$data->username] === $data->password) {
                $passwordValid = true;
            }

            if($passwordValid) {
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['department'] = $row['department'];

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