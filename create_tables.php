<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h2>Creating Database Tables</h2>";

    // Create property_issuances table (without foreign key constraint initially)
    $createPropertyIssuancesTable = "
    CREATE TABLE IF NOT EXISTS property_issuances (
        id INT PRIMARY KEY AUTO_INCREMENT,
        asset_id INT NOT NULL,
        employee_id VARCHAR(50) NOT NULL,
        recipient_name VARCHAR(255) NOT NULL,
        department VARCHAR(100),
        issue_date DATE NOT NULL,
        expected_return_date DATE,
        actual_return_date DATETIME,
        purpose TEXT,
        remarks TEXT,
        status ENUM('issued', 'returned', 'overdue', 'damaged') DEFAULT 'issued',
        issued_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $db->exec($createPropertyIssuancesTable);
    echo "<p>✅ property_issuances table created successfully</p>";

    // Create users table if it doesn't exist (for foreign key reference)
    $createUsersTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        role ENUM('admin', 'custodian', 'staff', 'maintenance') DEFAULT 'staff',
        department VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $db->exec($createUsersTable);
    echo "<p>✅ users table created successfully</p>";

    // Create assets table if it doesn't exist
    $createAssetsTable = "
    CREATE TABLE IF NOT EXISTS assets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        asset_code VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        category INT,
        purchase_date DATE,
        purchase_cost DECIMAL(15,2),
        current_value DECIMAL(15,2),
        location VARCHAR(255),
        status ENUM('available', 'assigned', 'maintenance', 'damaged', 'lost', 'disposed') DEFAULT 'available',
        condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
        assigned_to INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $db->exec($createAssetsTable);
    echo "<p>✅ assets table created successfully</p>";

    // Insert sample data
    echo "<h3>Inserting Sample Data</h3>";

    // Add foreign key constraint after assets table is ready
    try {
        // Check if foreign key constraint already exists
        $fkExists = $db->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
                               WHERE CONSTRAINT_NAME = 'property_issuances_ibfk_1'
                               AND TABLE_SCHEMA = 'property_custodian_db'")->fetchColumn();

        if($fkExists == 0) {
            $db->exec("ALTER TABLE property_issuances
                      ADD CONSTRAINT property_issuances_ibfk_1
                      FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE");
            echo "<p>✅ Foreign key constraint added</p>";
        }
    } catch(Exception $e) {
        echo "<p>⚠️ Foreign key constraint setup skipped: " . $e->getMessage() . "</p>";
    }

    // Insert sample user if none exists
    $checkUser = $db->query("SELECT COUNT(*) FROM users");
    if($checkUser->fetchColumn() == 0) {
        $insertUser = "INSERT INTO users (username, password, full_name, email, role, department) VALUES
        ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'System Administrator', 'admin@school.edu', 'admin', 'administration'),
        ('custodian1', '" . password_hash('custodian123', PASSWORD_DEFAULT) . "', 'John Custodian', 'custodian@school.edu', 'custodian', 'administration')";
        $db->exec($insertUser);
        echo "<p>✅ Sample users created</p>";
    }

    // Insert sample assets if none exists
    $checkAssets = $db->query("SELECT COUNT(*) FROM assets");
    if($checkAssets->fetchColumn() == 0) {
        $insertAssets = "INSERT INTO assets (asset_code, name, description, category, purchase_date, purchase_cost, current_value, location, status, condition_status) VALUES
        ('COMP001', 'Dell Laptop OptiPlex 3000', 'Business laptop for office use', 1, '2023-01-15', 45000.00, 40000.00, 'IT Office Room 101', 'available', 'excellent'),
        ('FURN002', 'Office Chair Executive', 'Ergonomic office chair with lumbar support', 2, '2023-02-20', 8500.00, 7500.00, 'Admin Office Room 201', 'available', 'good'),
        ('PROJ003', 'Epson Projector EB-X41', 'LCD projector for presentations', 4, '2023-03-10', 25000.00, 22000.00, 'Conference Room A', 'available', 'excellent'),
        ('DESK004', 'Wooden Office Desk', 'L-shaped executive desk', 2, '2023-01-30', 15000.00, 13500.00, 'Manager Office Room 205', 'assigned', 'good'),
        ('PRINT005', 'HP LaserJet Pro MFP', 'Multi-function laser printer', 1, '2023-04-05', 18000.00, 16000.00, 'Print Station Floor 1', 'available', 'good')";
        $db->exec($insertAssets);
        echo "<p>✅ Sample assets created</p>";
    }

    // Insert sample property issuance if none exists
    $checkIssuances = $db->query("SELECT COUNT(*) FROM property_issuances");
    if($checkIssuances->fetchColumn() == 0) {
        // First check if we have assets to reference
        $checkAssets = $db->query("SELECT COUNT(*) FROM assets");
        if($checkAssets->fetchColumn() > 0) {
            // Get actual asset IDs that exist
            $assetIds = $db->query("SELECT id FROM assets LIMIT 2")->fetchAll(PDO::FETCH_COLUMN);

            if(count($assetIds) >= 2) {
                $insertIssuances = "INSERT INTO property_issuances (asset_id, employee_id, recipient_name, department, issue_date, expected_return_date, purpose, status, issued_by) VALUES
                ({$assetIds[0]}, 'EMP001', 'Maria Santos', 'administration', '2024-01-15', '2024-12-31', 'Daily office work and management tasks', 'issued', 1),
                ({$assetIds[1]}, 'EMP002', 'Juan dela Cruz', 'it', '2024-02-01', '2024-06-30', 'Software development and system maintenance', 'returned', 1)";
                $db->exec($insertIssuances);

                // Update the assigned asset status
                $db->exec("UPDATE assets SET assigned_to = 'EMP001', status = 'assigned' WHERE id = {$assetIds[0]}");
                $db->exec("UPDATE assets SET status = 'available', assigned_to = NULL WHERE id = {$assetIds[1]}");

                echo "<p>✅ Sample property issuances created</p>";
            } else {
                echo "<p>⚠️ Not enough assets to create sample issuances</p>";
            }
        } else {
            echo "<p>⚠️ No assets found to create sample issuances</p>";
        }
    }

    echo "<h3>✅ Database setup completed successfully!</h3>";
    echo "<p><a href='property-issuance.php'>Go to Property Issuance Page</a></p>";

} catch(Exception $e) {
    echo "<h3>❌ Database Error: " . $e->getMessage() . "</h3>";
}
?>