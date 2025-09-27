<?php
echo "<h2>API Debug Test</h2>";

// Test 1: Check if files exist
echo "<h3>File Existence Check:</h3>";
$files = [
    'config/cors.php',
    'config/database.php',
    'api/property_issuance.php'
];

foreach($files as $file) {
    if(file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

// Test 2: Database connection
echo "<h3>Database Connection Test:</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connection successful<br>";

    // Check tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(', ', $tables) . "<br>";

    if(in_array('property_issuances', $tables)) {
        echo "✅ property_issuances table exists<br>";
    } else {
        echo "❌ property_issuances table missing<br>";
    }

} catch(Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Direct API call simulation
echo "<h3>Direct API Test:</h3>";
try {
    // Simulate GET request
    $_SERVER['REQUEST_METHOD'] = 'GET';

    // Capture output
    ob_start();

    // Include the API file
    include 'api/property_issuance.php';

    $output = ob_get_clean();
    echo "✅ API executed successfully<br>";
    echo "Output: <pre>" . htmlspecialchars($output) . "</pre>";

} catch(Exception $e) {
    ob_end_clean();
    echo "❌ API error: " . $e->getMessage() . "<br>";
} catch(Error $e) {
    ob_end_clean();
    echo "❌ PHP Error: " . $e->getMessage() . "<br>";
}

// Test 4: Check error logs
echo "<h3>Recent Error Logs:</h3>";
$errorLog = ini_get('error_log');
if($errorLog && file_exists($errorLog)) {
    $logs = file_get_contents($errorLog);
    $recentLogs = array_slice(explode("\n", $logs), -10);
    echo "<pre>" . htmlspecialchars(implode("\n", $recentLogs)) . "</pre>";
} else {
    echo "No error log file found or configured<br>";
}
?>