<?php
header('Content-Type: text/html');
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h2>Creating Default User</h2>";

    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Users table does not exist, creating it...</p>";

        $db->exec("CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            role ENUM('admin', 'custodian', 'staff', 'maintenance') NOT NULL,
            department VARCHAR(100),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "<p style='color: green;'>✓ Users table created</p>";
    } else {
        echo "<p style='color: green;'>✓ Users table exists</p>";
    }

    // Check if we have any users
    $stmt = $db->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['user_count'] == 0) {
        echo "<p>No users found, creating default admin user...</p>";

        // Create a default admin user
        $stmt = $db->prepare("INSERT INTO users (username, password, full_name, email, role, department, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);

        $stmt->execute([
            'admin',
            $password_hash,
            'System Administrator',
            'admin@example.com',
            'admin',
            'IT',
            'active'
        ]);

        echo "<p style='color: green;'>✓ Default admin user created</p>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>User ID:</strong> " . $db->lastInsertId() . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Found " . $result['user_count'] . " existing users</p>";

        // Show existing users
        $stmt = $db->query("SELECT id, username, full_name, role, status FROM users");
        echo "<h3>Existing Users:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Status</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<br><p style='color: green;'>✅ User setup completed!</p>";
    echo "<br><a href='test_real_api.php'>Test Asset API Again</a>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>