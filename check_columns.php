<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=property_custodian_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Assets Table Structure:</h2>";
    $stmt = $pdo->query("DESCRIBE assets");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>