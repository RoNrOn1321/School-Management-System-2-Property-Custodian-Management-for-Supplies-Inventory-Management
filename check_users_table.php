<?php
header('Content-Type: text/html');
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h2>Users Table Analysis</h2>";

    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Users table does not exist</p>";
    } else {
        echo "<p style='color: green;'>✓ Users table exists</p>";

        // Show users table structure
        echo "<h3>Users table structure:</h3>";
        $stmt = $db->query("DESCRIBE users");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Count users
        $stmt = $db->query("SELECT COUNT(*) as user_count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total users in table: <strong>" . $result['user_count'] . "</strong></p>";

        if ($result['user_count'] > 0) {
            echo "<h3>Existing users:</h3>";
            $stmt = $db->query("SELECT id, username, full_name, role FROM users LIMIT 10");
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th></tr>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    // Check foreign key constraints on assets table
    echo "<h3>Assets table foreign key constraints:</h3>";
    $stmt = $db->query("
        SELECT
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'property_custodian_db'
        AND TABLE_NAME = 'assets'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");

    if ($stmt->rowCount() > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Constraint</th><th>Column</th><th>References Table</th><th>References Column</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['CONSTRAINT_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['COLUMN_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['REFERENCED_TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No foreign key constraints found on assets table.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>