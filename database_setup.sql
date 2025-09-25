-- Create Database
CREATE DATABASE IF NOT EXISTS property_custodian_db;
USE property_custodian_db;

-- Users table for authentication and access control
CREATE TABLE users (
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
);

-- Asset categories
CREATE TABLE asset_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Assets/Properties registry
CREATE TABLE assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    brand VARCHAR(100),
    model VARCHAR(100),
    serial_number VARCHAR(100),
    purchase_date DATE,
    purchase_cost DECIMAL(15,2),
    depreciation_rate DECIMAL(5,2) DEFAULT 0.00,
    current_value DECIMAL(15,2),
    status ENUM('available', 'assigned', 'maintenance', 'damaged', 'lost', 'disposed') DEFAULT 'available',
    condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    location VARCHAR(200),
    qr_code VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES asset_categories(id)
);

-- Custodians table
CREATE TABLE custodians (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    contact_number VARCHAR(20),
    office_location VARCHAR(200),
    supervisor_id INT,
    status ENUM('active', 'inactive', 'transferred') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (supervisor_id) REFERENCES custodians(id)
);

-- Property assignments/transfers
CREATE TABLE property_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    custodian_id INT NOT NULL,
    assigned_by INT,
    assignment_date DATE NOT NULL,
    expected_return_date DATE,
    actual_return_date DATE,
    assignment_purpose TEXT,
    conditions TEXT,
    acknowledgment_signed BOOLEAN DEFAULT FALSE,
    acknowledgment_date TIMESTAMP,
    status ENUM('active', 'returned', 'transferred', 'lost') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (custodian_id) REFERENCES custodians(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

-- Supplies inventory
CREATE TABLE supplies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    unit VARCHAR(50),
    current_stock INT DEFAULT 0,
    minimum_stock INT DEFAULT 0,
    maximum_stock INT DEFAULT 0,
    unit_cost DECIMAL(10,2),
    supplier VARCHAR(200),
    storage_location VARCHAR(200),
    expiry_date DATE,
    status ENUM('active', 'discontinued', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Supply transactions (in/out)
CREATE TABLE supply_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    supply_id INT NOT NULL,
    transaction_type ENUM('in', 'out', 'adjustment') NOT NULL,
    quantity INT NOT NULL,
    reference_number VARCHAR(100),
    transaction_date DATE NOT NULL,
    requested_by INT,
    approved_by INT,
    purpose TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supply_id) REFERENCES supplies(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Maintenance schedules
CREATE TABLE maintenance_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    maintenance_type ENUM('preventive', 'corrective', 'emergency') NOT NULL,
    scheduled_date DATE NOT NULL,
    completed_date DATE,
    assigned_to INT,
    description TEXT,
    estimated_cost DECIMAL(10,2),
    actual_cost DECIMAL(10,2),
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Property audit records
CREATE TABLE property_audits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    audit_code VARCHAR(50) UNIQUE NOT NULL,
    audit_type ENUM('physical_inventory', 'financial_audit', 'compliance_check') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    auditor_id INT,
    department VARCHAR(100),
    status ENUM('planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned',
    total_assets_audited INT DEFAULT 0,
    discrepancies_found INT DEFAULT 0,
    summary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auditor_id) REFERENCES users(id)
);

-- Audit findings
CREATE TABLE audit_findings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    audit_id INT NOT NULL,
    asset_id INT,
    finding_type ENUM('missing', 'damaged', 'location_mismatch', 'data_error', 'unauthorized_use') NOT NULL,
    description TEXT,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    corrective_action TEXT,
    responsible_person INT,
    target_date DATE,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (audit_id) REFERENCES property_audits(id),
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (responsible_person) REFERENCES users(id)
);

-- Procurement requests
CREATE TABLE procurement_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_code VARCHAR(50) UNIQUE NOT NULL,
    request_type ENUM('asset', 'supply', 'service') NOT NULL,
    requestor_id INT NOT NULL,
    department VARCHAR(100),
    request_date DATE NOT NULL,
    required_date DATE,
    justification TEXT,
    estimated_cost DECIMAL(15,2),
    approved_cost DECIMAL(15,2),
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'ordered', 'received', 'cancelled') DEFAULT 'draft',
    approved_by INT,
    approval_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requestor_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Procurement request items
CREATE TABLE procurement_request_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    item_name VARCHAR(200) NOT NULL,
    description TEXT,
    quantity INT NOT NULL,
    unit VARCHAR(50),
    estimated_unit_cost DECIMAL(10,2),
    total_cost DECIMAL(10,2),
    specifications TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES procurement_requests(id)
);

-- System logs for audit trail
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default users
INSERT INTO users (username, password, full_name, email, role, department) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@school.edu', 'admin', 'IT Department'),
('custodian', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Property Custodian', 'custodian@school.edu', 'custodian', 'Property Office'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Member', 'staff@school.edu', 'staff', 'General Office');

-- Asset tags for categorization and filtering
CREATE TABLE asset_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7) DEFAULT '#3B82F6',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Asset tag relationships (many-to-many)
CREATE TABLE asset_tag_relationships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    tag_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES asset_tags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_asset_tag (asset_id, tag_id)
);

-- Insert default asset tags
INSERT INTO asset_tags (name, color, description) VALUES
('High Priority', '#EF4444', 'High priority assets requiring special attention'),
('Fragile', '#F59E0B', 'Fragile items requiring careful handling'),
('Portable', '#10B981', 'Portable items that can be easily moved'),
('Expensive', '#8B5CF6', 'High-value assets requiring extra security'),
('Shared', '#06B6D4', 'Shared resources used by multiple departments');

-- Insert default asset categories
INSERT INTO asset_categories (name, description) VALUES
('Computer Equipment', 'Desktop computers, laptops, monitors, keyboards, etc.'),
('Office Furniture', 'Desks, chairs, cabinets, tables'),
('Laboratory Equipment', 'Scientific instruments, microscopes, measuring tools'),
('Audio Visual Equipment', 'Projectors, speakers, cameras, recording devices'),
('Vehicles', 'School buses, service vehicles, motorcycles'),
('Kitchen Equipment', 'Stoves, refrigerators, cooking utensils'),
('Sports Equipment', 'Balls, nets, gymnasium equipment'),
('Books and References', 'Textbooks, reference materials, library books');

-- Insert sample assets
INSERT INTO assets (asset_code, name, description, category_id, brand, model, serial_number, purchase_date, purchase_cost, current_value, location) VALUES
('PC-2024-001', 'Desktop Computer', 'Dell OptiPlex 3080', 1, 'Dell', 'OptiPlex 3080', 'DT2024001', '2024-01-15', 35000.00, 30000.00, 'Computer Lab 1'),
('OF-2024-001', 'Office Chair', 'Ergonomic Office Chair', 2, 'Herman Miller', 'Aeron', 'CH2024001', '2024-02-01', 15000.00, 14000.00, 'Principal Office'),
('AV-2024-001', 'Projector', 'LED Projector 4K', 4, 'Epson', 'PowerLite L610U', 'PJ2024001', '2024-01-20', 45000.00, 42000.00, 'Conference Room');