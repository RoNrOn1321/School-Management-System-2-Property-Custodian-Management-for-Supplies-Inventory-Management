<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h2>Setting up Supplies Inventory Data</h2>";

    // Check if supplies table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'supplies'");
    if ($checkTable->rowCount() == 0) {
        echo "<p>❌ Supplies table does not exist. Please run the main database setup first.</p>";
        exit;
    }

    // Clear existing sample data
    $db->exec("DELETE FROM supply_transactions WHERE id > 0");
    $db->exec("DELETE FROM supplies WHERE id > 0");
    echo "<p>✅ Cleared existing supplies data</p>";

    // Insert sample supplies
    $sampleSupplies = [
        ['SUP001', 'A4 Bond Paper', 'White bond paper for printing and copying', 'office', 'ream', 50, 20, 250.00, 12500.00, 'Storage Room A', 'active'],
        ['SUP002', 'Blue Ballpen', 'Blue ink ballpoint pen for writing', 'office', 'box', 15, 10, 120.00, 1800.00, 'Storage Room A', 'active'],
        ['SUP003', 'Liquid Disinfectant', 'Alcohol-based disinfectant for cleaning', 'cleaning', 'bottle', 25, 15, 45.00, 1125.00, 'Janitor Closet', 'active'],
        ['SUP004', 'Face Masks', 'Disposable surgical face masks', 'medical', 'box', 8, 20, 180.00, 1440.00, 'Clinic Storage', 'active'],
        ['SUP005', 'Whiteboard Markers', 'Assorted color whiteboard markers', 'educational', 'set', 12, 8, 85.00, 1020.00, 'Storage Room B', 'active'],
        ['SUP006', 'Toilet Paper', 'Soft toilet tissue paper', 'cleaning', 'pack', 30, 25, 35.00, 1050.00, 'Janitor Closet', 'active'],
        ['SUP007', 'Hand Sanitizer', 'Alcohol-based hand sanitizer gel', 'medical', 'bottle', 5, 15, 65.00, 325.00, 'Clinic Storage', 'active'],
        ['SUP008', 'Manila Envelopes', 'Brown manila envelopes various sizes', 'office', 'pack', 20, 10, 95.00, 1900.00, 'Storage Room A', 'active'],
        ['SUP009', 'Floor Cleaner', 'Multi-purpose floor cleaning solution', 'cleaning', 'bottle', 18, 12, 55.00, 990.00, 'Janitor Closet', 'active'],
        ['SUP010', 'Copy Paper', 'Legal size copy paper', 'office', 'ream', 0, 15, 280.00, 0.00, 'Storage Room A', 'active']
    ];

    $insertSupply = "INSERT INTO supplies (item_code, name, description, category, unit, current_stock, minimum_stock, unit_cost, total_value, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insertSupply);

    foreach ($sampleSupplies as $supply) {
        $stmt->execute($supply);
    }

    echo "<p>✅ Sample supplies created</p>";

    // Insert sample transactions
    $sampleTransactions = [
        [1, 'in', 50, 250.00, 12500.00, 'PO-2024-001', 'Initial stock for A4 bond paper - Received from supplier', 1],
        [2, 'in', 25, 120.00, 3000.00, 'PO-2024-002', 'Initial stock for blue ballpens - Received from supplier', 1],
        [3, 'in', 30, 45.00, 1350.00, 'PO-2024-003', 'Initial stock for disinfectant - Received from supplier', 1],
        [4, 'in', 20, 180.00, 3600.00, 'PO-2024-004', 'Initial stock for face masks - Received from supplier', 1],
        [2, 'out', 10, 120.00, 1200.00, 'REQ-001', 'Distributed to Grade 1 teachers - Monthly distribution', 1],
        [3, 'out', 5, 45.00, 225.00, 'REQ-002', 'Cleaning supplies for classrooms - Weekly cleaning', 1],
        [4, 'out', 12, 180.00, 2160.00, 'REQ-003', 'Distributed to clinic - Medical supplies replenishment', 1],
        [5, 'in', 12, 85.00, 1020.00, 'PO-2024-005', 'Whiteboard markers for teachers - New purchase', 1],
        [6, 'in', 30, 35.00, 1050.00, 'PO-2024-006', 'Toilet paper stock - Monthly supply', 1],
        [7, 'in', 20, 65.00, 1300.00, 'PO-2024-007', 'Hand sanitizer for clinic - COVID supplies', 1],
        [7, 'out', 15, 65.00, 975.00, 'REQ-004', 'Distributed to classrooms - Safety protocol', 1],
        [8, 'in', 20, 95.00, 1900.00, 'PO-2024-008', 'Manila envelopes for admin - Office supplies', 1],
        [9, 'in', 18, 55.00, 990.00, 'PO-2024-009', 'Floor cleaner for maintenance - Cleaning supplies', 1]
    ];

    $insertTransaction = "INSERT INTO supply_transactions (supply_id, transaction_type, quantity, unit_cost, total_cost, reference_number, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtTrans = $db->prepare($insertTransaction);

    foreach ($sampleTransactions as $transaction) {
        $stmtTrans->execute($transaction);
    }

    echo "<p>✅ Sample transactions created</p>";

    echo "<h3>✅ Supplies inventory setup completed successfully!</h3>";
    echo "<p><a href='supplies-inventory.php' class='bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>Go to Supplies Inventory</a></p>";

} catch(Exception $e) {
    echo "<h3>❌ Database Error: " . $e->getMessage() . "</h3>";
}
?>