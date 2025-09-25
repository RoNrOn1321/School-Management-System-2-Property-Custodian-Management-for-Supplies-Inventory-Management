-- Minimal Sample Data - Works with basic assets table structure
USE property_custodian_db;

-- Insert additional users first
INSERT IGNORE INTO users (username, password, full_name, email, role, department, status) VALUES
('jsmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Smith', 'john.smith@school.edu', 'staff', 'IT Department', 'active'),
('mgarcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Garcia', 'maria.garcia@school.edu', 'custodian', 'Property Office', 'active'),
('rjohnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert Johnson', 'robert.johnson@school.edu', 'maintenance', 'Facilities', 'active'),
('lwilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Linda Wilson', 'linda.wilson@school.edu', 'staff', 'Academic Office', 'active'),
('dbrown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Brown', 'david.brown@school.edu', 'staff', 'Library', 'active');

-- Check what columns exist in assets table
SELECT 'Checking assets table structure:' as Info;
DESCRIBE assets;

-- Insert assets using only basic columns that should exist
INSERT IGNORE INTO assets (asset_code, name, description, status, location) VALUES
('PC-2024-001', 'Desktop Computer - Lab 1', 'Dell OptiPlex 3080 for Computer Lab', 'available', 'Computer Lab 1'),
('PC-2024-002', 'Desktop Computer - Lab 2', 'Dell OptiPlex 3080 for Computer Lab', 'assigned', 'Computer Lab 1'),
('LAP-2024-001', 'Laptop - Teacher', 'HP EliteBook 840 G8 for faculty use', 'assigned', 'Faculty Office 205'),
('LAP-2024-002', 'Laptop - Administration', 'Lenovo ThinkPad X1 Carbon for admin staff', 'available', 'Admin Office'),
('MON-2024-001', 'Monitor - 24 inch', 'Dell UltraSharp U2419H', 'assigned', 'Computer Lab 1'),
('OF-2024-001', 'Executive Office Chair', 'Ergonomic chair for principal office', 'assigned', 'Principal Office'),
('OF-2024-002', 'Student Desk', 'Standard classroom desk', 'available', 'Classroom 101'),
('OF-2024-003', 'Teacher Desk', 'Large wooden desk for faculty', 'assigned', 'Classroom 205'),
('LAB-2024-001', 'Microscope - Compound', 'Binocular compound microscope', 'available', 'Science Lab A'),
('LAB-2024-002', 'Balance - Digital', 'Precision digital balance 0.1g', 'available', 'Chemistry Lab'),
('AV-2024-001', 'Projector - Conference Room', 'LED Projector 4K for presentations', 'available', 'Conference Room A'),
('AV-2024-002', 'Smart TV - 55 inch', 'Interactive smart TV for classroom', 'assigned', 'Classroom 301'),
('SP-2024-001', 'Basketball Hoop - Outdoor', 'Adjustable basketball hoop system', 'available', 'Basketball Court'),
('SP-2024-002', 'Volleyball Net System', 'Professional volleyball net with poles', 'assigned', 'Gymnasium'),
('MU-2024-001', 'Digital Piano', 'Weighted key digital piano', 'assigned', 'Music Room');

-- Try to add QR codes if the column exists
UPDATE assets SET qr_code = CONCAT('QR_', asset_code, '_', UNIX_TIMESTAMP())
WHERE asset_code IN ('PC-2024-001', 'LAP-2024-001', 'LAB-2024-001', 'AV-2024-001', 'MU-2024-001');

-- Try to set qr_generated flag if the column exists
UPDATE assets SET qr_generated = TRUE
WHERE qr_code IS NOT NULL;

-- Show what we created
SELECT 'Sample Data Summary:' as Info;
SELECT
    (SELECT COUNT(*) FROM users) as Total_Users,
    (SELECT COUNT(*) FROM assets) as Total_Assets,
    (SELECT COUNT(*) FROM asset_tags) as Total_Tags;

SELECT 'Sample Assets Created:' as Info;
SELECT asset_code, name, status, location FROM assets ORDER BY asset_code;

SELECT 'Setup completed with basic sample data!' as Result;