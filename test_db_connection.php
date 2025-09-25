<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

try {
    echo "Testing database connection...\n";

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "✓ Database connection successful\n";

        // Check if property_custodian_db database exists
        $stmt = $db->query("SELECT DATABASE() as current_db");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ Connected to database: " . $result['current_db'] . "\n";

        // List all tables
        echo "\nTables in database:\n";
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($tables)) {
            echo "⚠ No tables found in database\n";
        } else {
            foreach ($tables as $table) {
                echo "  - $table\n";
            }
        }

        // Check if assets table exists
        if (in_array('assets', $tables)) {
            echo "\nAssets table structure:\n";
            $stmt = $db->query("DESCRIBE assets");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($columns as $column) {
                echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
            }
        } else {
            echo "\n❌ Assets table does not exist\n";
        }

        // Check if asset_tags table exists
        if (in_array('asset_tags', $tables)) {
            echo "\nAsset tags table exists ✓\n";
        } else {
            echo "\n❌ Asset tags table does not exist\n";
        }

        // Check if asset_tag_relationships table exists
        if (in_array('asset_tag_relationships', $tables)) {
            echo "\nAsset tag relationships table exists ✓\n";
        } else {
            echo "\n❌ Asset tag relationships table does not exist\n";
        }

    } else {
        echo "❌ Failed to establish database connection\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>