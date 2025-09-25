-- Asset Tags Enhancement for Property Custodian Management System
USE property_custodian_db;

-- Asset tags table for managing custom tags
CREATE TABLE IF NOT EXISTS asset_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3B82F6', -- Hex color code for tag display
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Asset-tag relationship table (many-to-many)
CREATE TABLE IF NOT EXISTS asset_tag_relationships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    tag_id INT NOT NULL,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_asset_tag (asset_id, tag_id),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES asset_tags(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

-- Add QR code generation flag to assets table
-- Note: If qr_code column doesn't exist, add it first manually or run:
-- ALTER TABLE assets ADD COLUMN qr_code VARCHAR(255);

ALTER TABLE assets
ADD COLUMN IF NOT EXISTS qr_code VARCHAR(255),
ADD COLUMN IF NOT EXISTS qr_generated BOOLEAN DEFAULT FALSE;

-- Insert default asset tags (ignore duplicates)
INSERT IGNORE INTO asset_tags (name, description, color, created_by) VALUES
('High Priority', 'Critical assets requiring special attention', '#DC2626', 1),
('Fragile', 'Assets that require careful handling', '#F59E0B', 1),
('Portable', 'Easily movable assets', '#10B981', 1),
('Expensive', 'High-value assets requiring extra security', '#7C3AED', 1),
('Outdated', 'Assets scheduled for replacement', '#6B7280', 1),
('New', 'Recently acquired assets', '#06B6D4', 1),
('Warranty', 'Assets currently under warranty', '#059669', 1),
('Shared', 'Assets used by multiple departments', '#0EA5E9', 1),
('Personal', 'Assets assigned to specific individuals', '#8B5CF6', 1),
('Backup', 'Redundant or backup equipment', '#84CC16', 1);

-- Create indexes for better performance (only for existing columns)
CREATE INDEX IF NOT EXISTS idx_asset_tags_name ON asset_tags(name);
CREATE INDEX IF NOT EXISTS idx_asset_tag_relationships_asset ON asset_tag_relationships(asset_id);
CREATE INDEX IF NOT EXISTS idx_asset_tag_relationships_tag ON asset_tag_relationships(tag_id);
CREATE INDEX IF NOT EXISTS idx_assets_qr_code ON assets(qr_code);
CREATE INDEX IF NOT EXISTS idx_assets_status ON assets(status);

-- Note: If you have a category_id column in your assets table, you can manually add:
-- CREATE INDEX IF NOT EXISTS idx_assets_status_category ON assets(status, category_id);