-- Check Database Structure
USE property_custodian_db;

-- Show all tables in the database
SELECT 'Current Tables in Database:' as Info;
SHOW TABLES;

-- Check the structure of key tables
SELECT 'Assets Table Structure:' as Info;
DESCRIBE assets;

SELECT 'Users Table Structure:' as Info;
DESCRIBE users;

-- Check if asset_tags table exists (from our enhancement)
SELECT 'Asset Tags Table Structure:' as Info;
DESCRIBE asset_tags;

-- Show current data counts
SELECT 'Current Data Counts:' as Info;
SELECT
    (SELECT COUNT(*) FROM users) as Total_Users,
    (SELECT COUNT(*) FROM assets) as Total_Assets,
    (SELECT COUNT(*) FROM asset_tags) as Total_Tags;

-- Show existing assets if any
SELECT 'Current Assets:' as Info;
SELECT id, name, status, location FROM assets LIMIT 10;