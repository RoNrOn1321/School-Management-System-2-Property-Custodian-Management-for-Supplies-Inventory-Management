<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Add debug logging
function debug_log($message, $data = null) {
    $log_message = "[" . date('Y-m-d H:i:s') . "] " . $message;
    if ($data !== null) {
        $log_message .= " | Data: " . json_encode($data);
    }
    error_log($log_message);
}

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Temporarily disabled for testing
// if(!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode(array("message" => "Unauthorized"));
//     exit();
// }

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
$input_data = file_get_contents("php://input");
debug_log("API Request", ["method" => $method, "get" => $_GET, "input" => $input_data]);

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            getAsset($db, $_GET['id']);
        } else {
            getAssets($db);
        }
        break;
    case 'POST':
        createAsset($db, $input_data);
        break;
    case 'PUT':
        if(isset($_GET['id'])) {
            updateAsset($db, $_GET['id'], $input_data);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Asset ID required for update"));
        }
        break;
    case 'DELETE':
        if(isset($_GET['id'])) {
            deleteAsset($db, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Asset ID required for delete"));
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function getAssets($db) {
    try {
        // Build query with filters
        $whereClause = "";
        $params = array();

        // Add search filter
        if(isset($_GET['search']) && !empty($_GET['search'])) {
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "(a.name LIKE ? OR a.asset_code LIKE ? OR a.description LIKE ?)";
            $searchTerm = "%" . $_GET['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add category filter
        if(isset($_GET['category']) && !empty($_GET['category'])) {
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "(ac.name = ? OR a.category = ?)";
            $params[] = $_GET['category'];
            $params[] = $_GET['category'];
        }

        // Add status filter
        if(isset($_GET['status']) && !empty($_GET['status'])) {
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "a.status = ?";
            $params[] = $_GET['status'];
        }

        // Add tag filter
        if(isset($_GET['tag']) && !empty($_GET['tag'])) {
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "a.id IN (SELECT asset_id FROM asset_tag_relationships WHERE tag_id = ?)";
            $params[] = $_GET['tag'];
        }

        // Check if assets table exists
        $checkTable = $db->query("SHOW TABLES LIKE 'assets'");
        if($checkTable->rowCount() == 0) {
            http_response_code(500);
            echo json_encode(array("message" => "Assets table not found. Please run database setup."));
            return;
        }

        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
        $offset = ($page - 1) * $limit;

        // First get the total count
        $countQuery = "SELECT COUNT(DISTINCT a.id) as total
                       FROM assets a
                       LEFT JOIN asset_categories ac ON a.category = ac.id
                       LEFT JOIN asset_tag_relationships atr ON a.id = atr.asset_id
                       LEFT JOIN asset_tags at ON atr.tag_id = at.id" . $whereClause;

        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Then get the paginated results
        $query = "SELECT a.*,
                  COALESCE(ac.name, 'Uncategorized') as category_name,
                  GROUP_CONCAT(DISTINCT CONCAT(COALESCE(at.id, ''), ':', COALESCE(at.name, ''), ':', COALESCE(at.color, '#3B82F6')) SEPARATOR '|') as tags
                  FROM assets a
                  LEFT JOIN asset_categories ac ON a.category = ac.id
                  LEFT JOIN asset_tag_relationships atr ON a.id = atr.asset_id
                  LEFT JOIN asset_tags at ON atr.tag_id = at.id" .
                  $whereClause . "
                  GROUP BY a.id
                  ORDER BY a.created_at DESC
                  LIMIT " . intval($limit) . " OFFSET " . intval($offset);

        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare query");
        }

        $stmt->execute($params);

        $assets = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Parse tags
            $tags = array();
            if(!empty($row['tags'])) {
                $tagPairs = explode('|', $row['tags']);
                foreach($tagPairs as $tagPair) {
                    $tagData = explode(':', $tagPair);
                    if(count($tagData) >= 3) {
                        $tags[] = array(
                            'id' => $tagData[0],
                            'name' => $tagData[1],
                            'color' => $tagData[2]
                        );
                    }
                }
            }
            $row['tags'] = $tags;
            unset($row['tags']); // Remove the raw tags string
            $row['tags'] = $tags;

            $assets[] = $row;
        }

        // Calculate pagination info
        $totalPages = ceil($totalCount / $limit);

        http_response_code(200);
        echo json_encode(array(
            'assets' => $assets,
            'pagination' => array(
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => intval($totalCount),
                'per_page' => $limit,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1
            )
        ));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error fetching assets", "error" => $e->getMessage()));
    }
}

function getAsset($db, $id) {
    $query = "SELECT a.*, COALESCE(ac.name, 'Uncategorized') as category_name,
              GROUP_CONCAT(DISTINCT CONCAT(at.id, ':', at.name, ':', at.color) SEPARATOR '|') as tags
              FROM assets a
              LEFT JOIN asset_categories ac ON a.category = ac.id
              LEFT JOIN asset_tag_relationships atr ON a.id = atr.asset_id
              LEFT JOIN asset_tags at ON atr.tag_id = at.id
              WHERE a.id = ?
              GROUP BY a.id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);

        // Parse tags
        $tags = array();
        if(!empty($asset['tags'])) {
            $tagPairs = explode('|', $asset['tags']);
            foreach($tagPairs as $tagPair) {
                $tagData = explode(':', $tagPair);
                if(count($tagData) >= 3) {
                    $tags[] = array(
                        'id' => $tagData[0],
                        'name' => $tagData[1],
                        'color' => $tagData[2]
                    );
                }
            }
        }
        unset($asset['tags']); // Remove raw tags string
        $asset['tags'] = $tags;

        http_response_code(200);
        echo json_encode($asset);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Asset not found"));
    }
}

function createAsset($db, $input = null) {
    debug_log("createAsset called");
    if ($input === null) {
        $input = file_get_contents("php://input");
    }
    debug_log("Raw input", $input);

    $data = json_decode($input);
    debug_log("Parsed data", $data);

    if(!empty($data->asset_code) && !empty($data->name)) {
        debug_log("Validation passed", ["asset_code" => $data->asset_code, "name" => $data->name]);
        try {
            // Check what columns actually exist in the assets table
            $checkStmt = $db->query("DESCRIBE assets");
            $columns = array();
            while ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $columns[] = $row['Field'];
            }

            // Build the INSERT query based on available columns
            $insertFields = array();
            $insertValues = array();
            $placeholders = array();

            if (in_array('asset_code', $columns) && !empty($data->asset_code)) {
                $insertFields[] = 'asset_code';
                $insertValues[] = $data->asset_code;
                $placeholders[] = '?';
            }

            if (in_array('name', $columns) && !empty($data->name)) {
                $insertFields[] = 'name';
                $insertValues[] = $data->name;
                $placeholders[] = '?';
            }

            if (in_array('description', $columns)) {
                $insertFields[] = 'description';
                $insertValues[] = $data->description ?? null;
                $placeholders[] = '?';
            }

            // Handle category - check if it's category_id or category
            if (in_array('category_id', $columns)) {
                $insertFields[] = 'category_id';
                $insertValues[] = $data->category ?? null;
                $placeholders[] = '?';
            } elseif (in_array('category', $columns)) {
                $insertFields[] = 'category';
                $insertValues[] = $data->category ?? null;
                $placeholders[] = '?';
            }

            if (in_array('condition_status', $columns)) {
                $insertFields[] = 'condition_status';
                $insertValues[] = $data->condition_status ?? 'good';
                $placeholders[] = '?';
            }

            if (in_array('location', $columns)) {
                $insertFields[] = 'location';
                $insertValues[] = $data->location ?? null;
                $placeholders[] = '?';
            }

            if (in_array('purchase_date', $columns)) {
                $insertFields[] = 'purchase_date';
                $insertValues[] = $data->purchase_date ?? null;
                $placeholders[] = '?';
            }

            if (in_array('purchase_cost', $columns)) {
                $insertFields[] = 'purchase_cost';
                $insertValues[] = $data->purchase_cost ?? null;
                $placeholders[] = '?';
            }

            if (in_array('current_value', $columns)) {
                $insertFields[] = 'current_value';
                $insertValues[] = $data->current_value ?? null;
                $placeholders[] = '?';
            }

            if (in_array('status', $columns)) {
                $insertFields[] = 'status';
                $insertValues[] = $data->status ?? 'available';
                $placeholders[] = '?';
            }

            // Only include assigned_to if the column exists and we have a valid value
            if (in_array('assigned_to', $columns)) {
                $assigned_to_value = $data->assigned_to ?? null;

                // If assigned_to is provided, validate it exists in users table
                if ($assigned_to_value !== null && !empty($assigned_to_value) && $assigned_to_value !== '') {
                    // Check if it's a valid integer
                    if (is_numeric($assigned_to_value)) {
                        $checkUser = $db->prepare("SELECT id FROM users WHERE id = ?");
                        $checkUser->execute([intval($assigned_to_value)]);
                        if ($checkUser->rowCount() == 0) {
                            // User doesn't exist, set to null instead
                            $assigned_to_value = null;
                            debug_log("Invalid assigned_to user ID in create, setting to null", $data->assigned_to);
                        } else {
                            $assigned_to_value = intval($assigned_to_value);
                        }
                    } else {
                        // Invalid format, set to null
                        $assigned_to_value = null;
                        debug_log("Non-numeric assigned_to value in create, setting to null", $data->assigned_to);
                    }
                } else {
                    $assigned_to_value = null;
                }

                $insertFields[] = 'assigned_to';
                $insertValues[] = $assigned_to_value;
                $placeholders[] = '?';
            }

            $query = "INSERT INTO assets (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $db->prepare($query);

            if($stmt->execute($insertValues)) {
                $asset_id = $db->lastInsertId();

                // Log the activity
                // logActivity($db, $_SESSION['user_id'], 'create', 'assets', $asset_id);

                http_response_code(201);
                echo json_encode(array("message" => "Asset created successfully", "id" => $asset_id));
            } else {
                $errorInfo = $stmt->errorInfo();
                http_response_code(500);
                echo json_encode(array("message" => "Failed to create asset", "error" => $errorInfo[2]));
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => "Database error", "error" => $e->getMessage()));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Asset code and name are required"));
    }
}

function updateAsset($db, $id, $input = null) {
    debug_log("updateAsset called", ["id" => $id]);
    if ($input === null) {
        $input = file_get_contents("php://input");
    }
    debug_log("Raw input", $input);

    if (empty($input)) {
        http_response_code(400);
        echo json_encode(array("message" => "No input data received"));
        return;
    }

    $data = json_decode($input);

    if ($data === null) {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid JSON data"));
        return;
    }

    // Validate required fields
    if (empty($data->name)) {
        http_response_code(400);
        echo json_encode(array("message" => "Asset name is required"));
        return;
    }


    // First, let's check what columns actually exist in the assets table
    try {
        $checkStmt = $db->query("DESCRIBE assets");
        $columns = array();
        while ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }

        // Build the UPDATE query based on available columns
        $updateFields = array();
        $updateValues = array();

        if (in_array('asset_code', $columns) && isset($data->asset_code)) {
            $updateFields[] = 'asset_code = ?';
            $updateValues[] = $data->asset_code;
        }

        if (in_array('name', $columns) && !empty($data->name)) {
            $updateFields[] = 'name = ?';
            $updateValues[] = $data->name;
        }

        if (in_array('description', $columns)) {
            $updateFields[] = 'description = ?';
            $updateValues[] = isset($data->description) ? $data->description : null;
        }

        // Handle category - check if it's category_id or category
        if (in_array('category_id', $columns)) {
            $updateFields[] = 'category_id = ?';
            $updateValues[] = isset($data->category) ? $data->category : null;
        } elseif (in_array('category', $columns)) {
            $updateFields[] = 'category = ?';
            $updateValues[] = isset($data->category) ? $data->category : null;
        }

        if (in_array('purchase_date', $columns)) {
            $updateFields[] = 'purchase_date = ?';
            $updateValues[] = isset($data->purchase_date) ? $data->purchase_date : null;
        }

        if (in_array('purchase_cost', $columns)) {
            $updateFields[] = 'purchase_cost = ?';
            $updateValues[] = isset($data->purchase_cost) ? $data->purchase_cost : null;
        }

        if (in_array('current_value', $columns)) {
            $updateFields[] = 'current_value = ?';
            $updateValues[] = isset($data->current_value) ? $data->current_value : null;
        }

        if (in_array('location', $columns)) {
            $updateFields[] = 'location = ?';
            $updateValues[] = isset($data->location) ? $data->location : null;
        }

        if (in_array('status', $columns)) {
            $updateFields[] = 'status = ?';
            $updateValues[] = isset($data->status) ? $data->status : 'available';
        }

        if (in_array('condition_status', $columns)) {
            $updateFields[] = 'condition_status = ?';
            $updateValues[] = isset($data->condition_status) ? $data->condition_status : 'good';
        }

        // Only include assigned_to if the column exists and we have a valid value
        if (in_array('assigned_to', $columns)) {
            $assigned_to_value = isset($data->assigned_to) ? $data->assigned_to : null;

            // If assigned_to is provided, validate it exists in users table
            if ($assigned_to_value !== null && !empty($assigned_to_value) && $assigned_to_value !== '') {
                // Check if it's a valid integer
                if (is_numeric($assigned_to_value)) {
                    $checkUser = $db->prepare("SELECT id FROM users WHERE id = ?");
                    $checkUser->execute([intval($assigned_to_value)]);
                    if ($checkUser->rowCount() == 0) {
                        // User doesn't exist, set to null instead
                        $assigned_to_value = null;
                        debug_log("Invalid assigned_to user ID, setting to null", $data->assigned_to);
                    } else {
                        $assigned_to_value = intval($assigned_to_value);
                    }
                } else {
                    // Invalid format, set to null
                    $assigned_to_value = null;
                    debug_log("Non-numeric assigned_to value, setting to null", $data->assigned_to);
                }
            } else {
                $assigned_to_value = null;
            }

            $updateFields[] = 'assigned_to = ?';
            $updateValues[] = $assigned_to_value;
        }

        if (in_array('updated_at', $columns)) {
            $updateFields[] = 'updated_at = CURRENT_TIMESTAMP';
        }

        // Add the ID for the WHERE clause
        $updateValues[] = $id;

        $query = "UPDATE assets SET " . implode(', ', $updateFields) . " WHERE id = ?";

        $stmt = $db->prepare($query);

        if($stmt->execute($updateValues)) {
            // Log the activity
            // logActivity($db, $_SESSION['user_id'], 'update', 'assets', $id);

            http_response_code(200);
            echo json_encode(array("message" => "Asset updated successfully"));
        } else {
            $errorInfo = $stmt->errorInfo();
            http_response_code(500);
            echo json_encode(array("message" => "Failed to update asset", "error" => $errorInfo[2]));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error", "error" => $e->getMessage()));
    }
}

function deleteAsset($db, $id) {
    $query = "DELETE FROM assets WHERE id = ?";
    $stmt = $db->prepare($query);

    if($stmt->execute([$id])) {
        // Log the activity
        // logActivity($db, $_SESSION['user_id'], 'delete', 'assets', $id);

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