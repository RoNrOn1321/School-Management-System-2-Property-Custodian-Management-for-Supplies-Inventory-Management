<?php
require_once 'config/database.php';

echo "<h2>Setting up Custodian Assignment Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();

    // Read and execute the database setup SQL
    $sql = file_get_contents('database_setup.sql');

    // Split into individual statements (simple approach)
    $statements = explode(';', $sql);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
            } catch (PDOException $e) {
                // Skip errors for statements that might already exist
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "<p style='color: orange;'>⚠ Warning: " . $e->getMessage() . "</p>";
                }
            }
        }
    }

    echo "<h3>Verifying Tables</h3>";

    // Check if tables exist
    $tables = ['users', 'custodians', 'assets', 'asset_categories', 'property_assignments'];

    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✓ Table '$table' exists</p>";

                // Get count
                $countStmt = $db->query("SELECT COUNT(*) as count FROM $table");
                $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "<p style='margin-left: 20px;'>Records: $count</p>";
            } else {
                echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error checking table '$table': " . $e->getMessage() . "</p>";
        }
    }

    echo "<p><a href='test_custodian_assignments.php' style='background: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Run Tests</a>";
    echo "<a href='custodian-assignment.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Custodian Assignment Page</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Setup failed: " . $e->getMessage() . "</p>";
}
?>