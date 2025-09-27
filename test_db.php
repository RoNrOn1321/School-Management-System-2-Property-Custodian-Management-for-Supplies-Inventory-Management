<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h3>Database Connection: SUCCESS</h3>";

    // Check what tables exist
    $query = "SHOW TABLES";
    $stmt = $db->query($query);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Existing Tables:</h3>";
    echo "<ul>";
    foreach($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    // Check if property_issuances table exists
    if(in_array('property_issuances', $tables)) {
        echo "<h3>property_issuances table: EXISTS</h3>";

        // Show table structure
        $query = "DESCRIBE property_issuances";
        $stmt = $db->query($query);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h4>Table Structure:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h3>property_issuances table: MISSING</h3>";
        echo "<p>This table needs to be created!</p>";
    }

} catch(Exception $e) {
    echo "<h3>Database Error: " . $e->getMessage() . "</h3>";
}
?>