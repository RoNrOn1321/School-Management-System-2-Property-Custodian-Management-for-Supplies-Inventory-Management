# Asset Registry & Tagging Setup Guide

## Overview

This guide provides instructions for setting up and using the enhanced Asset Registry & Tagging functionality for the School Management System - Property Custodian Management.

## Features Implemented

### 🏷️ **Asset Tagging System**
- Create custom tags with colors
- Assign multiple tags to assets
- Filter assets by tags
- Tag management interface

### 📱 **QR Code Generation**
- Generate QR codes for assets
- Print and download QR codes
- QR code tracking and management

### 🔍 **Advanced Search & Filtering**
- Search by name, code, description
- Filter by category, status, tags
- Real-time search with debouncing

### 📊 **Export Functionality**
- Export to CSV, Excel, PDF formats
- Include/exclude specific fields
- Bulk export with filtering

### ✅ **Enhanced Asset Management**
- Comprehensive asset modal
- Bulk operations
- Asset assignment tracking

## Installation Steps

### 1. Database Setup

Run the following SQL files in order:

```bash
# First, ensure the main database is set up
mysql -u root -p < database_setup.sql

# Then add the asset tagging enhancements
mysql -u root -p < asset_tags_setup.sql
```

### 2. File Structure

The following files have been created/modified:

```
├── api/
│   ├── asset_tags.php          # Tag management API
│   ├── asset_categories.php    # Category management API
│   ├── qr_generator.php        # QR code generation API
│   ├── asset_export.php        # Export functionality API
│   └── assets.php              # Enhanced assets API
├── components/
│   └── asset-registry.php     # Enhanced UI component
├── js/
│   └── asset_management.js    # Frontend JavaScript
├── asset_tags_setup.sql       # Database migration
└── ASSET_REGISTRY_SETUP.md    # This setup guide
```

### 3. Include JavaScript File

Add the asset management JavaScript to your main layout or dashboard:

```html
<script src="./js/asset_management.js"></script>
```

### 4. Update Navigation (if needed)

Ensure the asset registry component is properly included in your navigation system.

## Database Schema

### New Tables Created

#### `asset_tags`
- Stores tag definitions with colors
- Links to user who created the tag

#### `asset_tag_relationships`
- Many-to-many relationship between assets and tags
- Tracks who assigned the tag and when

#### Enhanced `assets` table
- Added `qr_generated` boolean field
- Improved QR code tracking

## Usage Instructions

### Creating Tags

1. Navigate to Asset Registry
2. Click "Manage Tags" button
3. Fill in tag name, select color, add description
4. Click "Create Tag"

### Adding Assets with Tags

1. Click "Add Asset" button
2. Fill in asset information
3. In the Tags section, select tags from dropdown
4. Click the "+" button to add selected tags
5. Save the asset

### Generating QR Codes

1. Locate asset in the assets table
2. Click the QR code icon in the QR Code column
3. For new assets: generates QR code
4. For existing QR codes: displays the QR code
5. Use Print or Download buttons as needed

### Filtering Assets

- **Search**: Type in the search box for real-time filtering
- **Category**: Select from category dropdown
- **Status**: Filter by asset status
- **Tag**: Filter assets by specific tag

### Exporting Assets

1. Optional: Select specific assets using checkboxes
2. Click "Export" button
3. Choose format (CSV/Excel/PDF)
4. File will download automatically

### Bulk Operations

1. Select multiple assets using checkboxes
2. Click "Bulk Actions" button
3. Choose operation:
   - Change Status
   - Add Tag
   - Remove Tag

## API Endpoints

### Asset Tags
- `GET /api/asset_tags.php` - Get all tags
- `POST /api/asset_tags.php` - Create new tag
- `DELETE /api/asset_tags.php?id={id}` - Delete tag
- `POST /api/asset_tags.php?assign=1` - Assign tag to asset
- `DELETE /api/asset_tags.php?unassign=1` - Remove tag from asset

### QR Codes
- `POST /api/qr_generator.php` - Generate QR code for asset
- `GET /api/qr_generator.php?asset_id={id}` - Get QR code info

### Asset Export
- `POST /api/asset_export.php` - Export assets with filters

### Enhanced Assets API
- Now includes tag information in responses
- Supports filtering by search, category, status, tag

## Configuration Options

### QR Code Service
The system uses QRServer.com API for QR code generation. To use a different service:

1. Edit `api/qr_generator.php`
2. Modify the `generateQRCodeURL()` function
3. Update the QR data format as needed

### Tag Colors
Default tag colors are defined in the database setup. Customize as needed:

```sql
UPDATE asset_tags SET color = '#YOUR_COLOR' WHERE name = 'TAG_NAME';
```

### Export Fields
Customize export fields in `api/asset_export.php` by modifying the `$fieldMap` array.

## Security Notes

1. All API endpoints require user authentication
2. System logs track all tag operations
3. QR codes contain asset information - ensure appropriate access controls
4. Export functionality logs what data was exported and by whom

## Troubleshooting

### Common Issues

1. **Tags not loading**: Check database connection and ensure asset_tags table exists
2. **QR codes not generating**: Verify internet connection for QRServer.com API
3. **Export not working**: Check file permissions and PHP memory limits
4. **Search not responsive**: Ensure JavaScript files are loaded correctly

### Performance Optimization

For large datasets:

1. Add database indexes (already included in setup SQL)
2. Implement pagination for assets table
3. Consider caching for frequently accessed data

## Customization

### Adding New Tag Features
1. Extend the `asset_tags` table with new fields
2. Update the API endpoints
3. Modify the frontend forms and displays

### Custom Export Formats
1. Add new cases in `asset_export.php`
2. Implement the export function
3. Update frontend export options

### Integration with Other Systems
The QR code data format can be customized to integrate with:
- Mobile apps for asset scanning
- External inventory systems
- Audit and compliance tools

## Support and Maintenance

### Database Maintenance
- Regularly backup the asset_tags and asset_tag_relationships tables
- Monitor system_logs for audit trails
- Clean up orphaned tag relationships

### Performance Monitoring
- Monitor API response times
- Check database query performance
- Review export file sizes and generation times

## Next Steps

Consider implementing:
1. Asset history tracking
2. Mobile app for QR code scanning
3. Advanced reporting and analytics
4. Integration with procurement systems
5. Automated depreciation calculations

---

For technical support or questions about this implementation, refer to the system documentation or contact the development team.