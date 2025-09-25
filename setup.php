<?php
// Database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Setting up Property Custodian Database...</h2>";

    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/database_setup.sql');

    if ($sql === false) {
        throw new Exception("Could not read database_setup.sql file");
    }

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "✓ Executed: " . substr($statement, 0, 50) . "...<br>";
        }
    }

    echo "<br><strong style='color: green;'>✅ Database setup completed successfully!</strong><br>";
    echo "<br>Default users created:<br>";
    echo "• admin / password (admin@school.edu)<br>";
    echo "• custodian / password (custodian@school.edu)<br>";
    echo "• staff / password (staff@school.edu)<br>";
    echo "<br><a href='asset-registry.php'>Go to Asset Registry</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>❌ Error: " . $e->getMessage() . "</strong><br>";
    echo "<br>Make sure XAMPP MySQL service is running.";
}
?>