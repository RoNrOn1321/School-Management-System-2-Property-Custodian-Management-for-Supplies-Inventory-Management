// Asset Management JavaScript Functions

// Global variables
let currentAssets = [];
let allTags = [];
let allCategories = [];
let selectedAssetTags = [];

// Initialize Asset Management
function initAssetManagement() {
    loadCategories();
    loadTags();
    loadAssets();
    setupEventListeners();
}

// Setup Event Listeners
function setupEventListeners() {
    // Search functionality
    document.getElementById('searchAssets').addEventListener('input', debounce(filterAssets, 300));

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="assetSelect"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Load Categories
async function loadCategories() {
    try {
        const response = await fetch('./api/asset_categories.php');
        if (response.ok) {
            allCategories = await response.json();
            populateCategoryFilters();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load Tags
async function loadTags() {
    try {
        const response = await fetch('./api/asset_tags.php');
        if (response.ok) {
            allTags = await response.json();
            populateTagFilters();
            populateTagsList();
        }
    } catch (error) {
        console.error('Error loading tags:', error);
    }
}

// Load Assets
async function loadAssets(filters = {}) {
    try {
        let url = './api/assets.php';
        const params = new URLSearchParams();

        if (filters.search) params.append('search', filters.search);
        if (filters.category) params.append('category', filters.category);
        if (filters.status) params.append('status', filters.status);
        if (filters.tag) params.append('tag', filters.tag);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        const response = await fetch(url);
        if (response.ok) {
            currentAssets = await response.json();
            renderAssetsTable();
        }
    } catch (error) {
        console.error('Error loading assets:', error);
    }
}

// Populate Category Filters
function populateCategoryFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    const assetCategory = document.getElementById('assetCategory');

    // Clear existing options except first
    categoryFilter.innerHTML = '<option value="">All Categories</option>';
    assetCategory.innerHTML = '<option value="">Select Category</option>';

    allCategories.forEach(category => {
        categoryFilter.innerHTML += `<option value="${category.id}">${category.name}</option>`;
        assetCategory.innerHTML += `<option value="${category.id}">${category.name}</option>`;
    });
}

// Populate Tag Filters
function populateTagFilters() {
    const tagFilter = document.getElementById('tagFilter');
    const availableTags = document.getElementById('availableTags');

    // Clear existing options except first
    tagFilter.innerHTML = '<option value="">All Tags</option>';
    availableTags.innerHTML = '<option value="">Select a tag to add</option>';

    allTags.forEach(tag => {
        tagFilter.innerHTML += `<option value="${tag.id}">${tag.name}</option>`;
        availableTags.innerHTML += `<option value="${tag.id}">${tag.name}</option>`;
    });
}

// Populate Tags List for Management
function populateTagsList() {
    const tagsList = document.getElementById('tagsList');

    if (!allTags || allTags.length === 0) {
        tagsList.innerHTML = '<p class="text-gray-500 text-center py-4">No tags found</p>';
        return;
    }

    tagsList.innerHTML = allTags.map(tag => `
        <div class="flex items-center justify-between p-3 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <span class="inline-block w-4 h-4 rounded" style="background-color: ${tag.color}"></span>
                <div>
                    <span class="font-medium">${tag.name}</span>
                    ${tag.description ? `<p class="text-sm text-gray-500">${tag.description}</p>` : ''}
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">${tag.usage_count || 0} assets</span>
                <button onclick="deleteTag(${tag.id})" class="text-red-600 hover:text-red-800"
                        ${(tag.usage_count && tag.usage_count > 0) ? 'disabled title="Cannot delete tag in use"' : ''}>
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    `).join('');
}

// Render Assets Table
function renderAssetsTable() {
    const tbody = document.getElementById('assetsTableBody');

    if (!currentAssets || currentAssets.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="px-6 py-4 text-center text-gray-500">No assets found</td></tr>';
        return;
    }

    tbody.innerHTML = currentAssets.map(asset => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" name="assetSelect" value="${asset.id}" class="rounded">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${asset.asset_code}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">${asset.name}</div>
                ${asset.description ? `<div class="text-sm text-gray-500">${asset.description}</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900">${asset.category_name || 'N/A'}</span>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                    ${renderAssetTags(asset.tags)}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadgeClass(asset.status)}">
                    ${asset.status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${asset.location || 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                ${asset.qr_generated && asset.qr_code ?
                    `<button onclick="showQRCode(${asset.id})" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-qrcode"></i>
                    </button>` :
                    `<button onclick="generateQRCode(${asset.id})" class="text-gray-400 hover:text-blue-600" title="Generate QR Code">
                        <i class="fas fa-qrcode"></i>
                    </button>`
                }
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ₱${asset.current_value ? parseFloat(asset.current_value).toLocaleString() : 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex gap-2">
                    <button onclick="editAsset(${asset.id})" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteAsset(${asset.id})" class="text-red-600 hover:text-red-900">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Render Asset Tags
function renderAssetTags(tags) {
    if (!tags || tags.length === 0) {
        return '<span class="text-gray-400 text-xs">No tags</span>';
    }

    return tags.map(tag => `
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
              style="background-color: ${tag.color}15; color: ${tag.color}; border: 1px solid ${tag.color}40;">
            ${tag.name}
        </span>
    `).join('');
}

// Get Status Badge Class
function getStatusBadgeClass(status) {
    const classes = {
        'available': 'bg-green-100 text-green-800',
        'assigned': 'bg-blue-100 text-blue-800',
        'maintenance': 'bg-yellow-100 text-yellow-800',
        'damaged': 'bg-red-100 text-red-800',
        'lost': 'bg-gray-100 text-gray-800',
        'disposed': 'bg-purple-100 text-purple-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

// Filter Assets
function filterAssets() {
    const filters = {
        search: document.getElementById('searchAssets').value,
        category: document.getElementById('categoryFilter').value,
        status: document.getElementById('statusFilter').value,
        tag: document.getElementById('tagFilter').value
    };

    loadAssets(filters);
}

// Modal Functions
window.App = window.App || {};

App.openAssetModal = function(assetId = null) {
    selectedAssetTags = [];

    if (assetId) {
        // Edit mode
        document.getElementById('assetModalTitle').textContent = 'Edit Asset';
        document.getElementById('assetSubmitText').textContent = 'Update Asset';
        loadAssetForEdit(assetId);
    } else {
        // Add mode
        document.getElementById('assetModalTitle').textContent = 'Add Asset';
        document.getElementById('assetSubmitText').textContent = 'Save Asset';
        document.getElementById('assetForm').reset();
        document.getElementById('assetId').value = '';
        updateSelectedTagsDisplay();
    }

    document.getElementById('assetModal').classList.remove('hidden');
};

App.closeAssetModal = function() {
    document.getElementById('assetModal').classList.add('hidden');
};

App.openTagModal = function() {
    document.getElementById('tagModal').classList.remove('hidden');
    loadTags(); // Refresh tags list
};

App.closeTagModal = function() {
    document.getElementById('tagModal').classList.add('hidden');
};

App.closeQRModal = function() {
    document.getElementById('qrModal').classList.add('hidden');
};

// Asset CRUD Operations
async function handleAssetSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const assetData = Object.fromEntries(formData);
    const assetId = assetData.asset_id;

    try {
        const method = assetId ? 'PUT' : 'POST';
        const url = assetId ? `./api/assets.php?id=${assetId}` : './api/assets.php';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(assetData)
        });

        const result = await response.json();

        if (response.ok) {
            showNotification(result.message, 'success');
            App.closeAssetModal();

            // Handle tag assignments for new assets
            if (!assetId && selectedAssetTags.length > 0) {
                await assignTagsToAsset(result.id, selectedAssetTags);
            } else if (assetId && selectedAssetTags.length > 0) {
                await updateAssetTags(assetId, selectedAssetTags);
            }

            loadAssets();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error saving asset:', error);
        showNotification('Error saving asset', 'error');
    }
}

// Load Asset for Edit
async function loadAssetForEdit(assetId) {
    try {
        const response = await fetch(`./api/assets.php?id=${assetId}`);
        if (response.ok) {
            const asset = await response.json();

            // Populate form fields
            document.getElementById('assetId').value = asset.id;
            document.getElementById('assetCode').value = asset.asset_code || '';
            document.getElementById('assetName').value = asset.name || '';
            document.getElementById('assetDescription').value = asset.description || '';
            document.getElementById('assetCategory').value = asset.category_id || '';
            document.getElementById('assetStatus').value = asset.status || '';
            document.getElementById('assetBrand').value = asset.brand || '';
            document.getElementById('assetModel').value = asset.model || '';
            document.getElementById('assetSerialNumber').value = asset.serial_number || '';
            document.getElementById('assetLocation').value = asset.location || '';
            document.getElementById('assetPurchaseDate').value = asset.purchase_date || '';
            document.getElementById('assetPurchaseCost').value = asset.purchase_cost || '';
            document.getElementById('assetCurrentValue').value = asset.current_value || '';

            // Set selected tags
            selectedAssetTags = asset.tags || [];
            updateSelectedTagsDisplay();
        }
    } catch (error) {
        console.error('Error loading asset:', error);
        showNotification('Error loading asset', 'error');
    }
}

// Edit Asset
function editAsset(assetId) {
    App.openAssetModal(assetId);
}

// Delete Asset
async function deleteAsset(assetId) {
    if (!confirm('Are you sure you want to delete this asset?')) {
        return;
    }

    try {
        const response = await fetch(`./api/assets.php?id=${assetId}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (response.ok) {
            showNotification(result.message, 'success');
            loadAssets();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting asset:', error);
        showNotification('Error deleting asset', 'error');
    }
}

// Tag Management
async function handleTagSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const tagData = Object.fromEntries(formData);

    try {
        const response = await fetch('./api/asset_tags.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(tagData)
        });

        const result = await response.json();

        if (response.ok) {
            showNotification(result.message, 'success');
            event.target.reset();
            document.getElementById('tagColor').value = '#3B82F6';
            loadTags();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error creating tag:', error);
        showNotification('Error creating tag', 'error');
    }
}

// Delete Tag
async function deleteTag(tagId) {
    if (!confirm('Are you sure you want to delete this tag?')) {
        return;
    }

    try {
        const response = await fetch(`./api/asset_tags.php?id=${tagId}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (response.ok) {
            showNotification(result.message, 'success');
            loadTags();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting tag:', error);
        showNotification('Error deleting tag', 'error');
    }
}

// Add Tag to Asset
function addTagToAsset() {
    const tagSelect = document.getElementById('availableTags');
    const tagId = tagSelect.value;

    if (!tagId) return;

    const tag = allTags.find(t => t.id == tagId);
    if (!tag) return;

    // Check if tag already selected
    if (selectedAssetTags.some(t => t.id == tagId)) {
        showNotification('Tag already added', 'warning');
        return;
    }

    selectedAssetTags.push(tag);
    updateSelectedTagsDisplay();
    tagSelect.value = '';
}

// Remove Tag from Asset
function removeTagFromAsset(tagId) {
    selectedAssetTags = selectedAssetTags.filter(t => t.id != tagId);
    updateSelectedTagsDisplay();
}

// Update Selected Tags Display
function updateSelectedTagsDisplay() {
    const container = document.getElementById('selectedTags');

    if (selectedAssetTags.length === 0) {
        container.innerHTML = '<span class="text-gray-400 text-sm">No tags selected</span>';
        return;
    }

    container.innerHTML = selectedAssetTags.map(tag => `
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium gap-2"
              style="background-color: ${tag.color}15; color: ${tag.color}; border: 1px solid ${tag.color}40;">
            ${tag.name}
            <button type="button" onclick="removeTagFromAsset(${tag.id})" class="hover:bg-red-100 rounded-full p-1">
                <i class="fas fa-times text-xs"></i>
            </button>
        </span>
    `).join('');
}

// Assign Tags to Asset
async function assignTagsToAsset(assetId, tags) {
    for (const tag of tags) {
        try {
            await fetch('./api/asset_tags.php?assign=1', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    asset_id: assetId,
                    tag_id: tag.id
                })
            });
        } catch (error) {
            console.error('Error assigning tag:', error);
        }
    }
}

// Update Asset Tags (for edit mode)
async function updateAssetTags(assetId, newTags) {
    // This is a simplified approach - in a real app you'd want to
    // compare current tags with new tags and only make necessary changes

    // For now, we'll just show a notification
    // The full implementation would involve fetching current tags,
    // comparing, and making appropriate assign/unassign calls
    console.log('Tag updates would be implemented here for asset:', assetId);
}

// QR Code Functions
async function generateQRCode(assetId) {
    try {
        const response = await fetch('./api/qr_generator.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ asset_id: assetId })
        });

        const result = await response.json();

        if (response.ok) {
            showNotification(result.message, 'success');
            loadAssets(); // Refresh to show QR code icon
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error generating QR code:', error);
        showNotification('Error generating QR code', 'error');
    }
}

async function showQRCode(assetId) {
    try {
        const response = await fetch(`./api/qr_generator.php?asset_id=${assetId}`);
        const result = await response.json();

        if (response.ok && result.generated) {
            const qrContainer = document.getElementById('qrCodeContent');
            qrContainer.innerHTML = `
                <img src="${result.qr_url}" alt="QR Code" class="mx-auto mb-2">
                <p class="text-sm text-gray-600">Asset: ${result.qr_data}</p>
            `;

            document.getElementById('qrModal').classList.remove('hidden');
        } else {
            showNotification('QR code not found', 'error');
        }
    } catch (error) {
        console.error('Error showing QR code:', error);
        showNotification('Error showing QR code', 'error');
    }
}

function printQRCode() {
    const qrContent = document.getElementById('qrCodeContent');
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head><title>QR Code</title></head>
            <body style="text-align: center; padding: 20px;">
                ${qrContent.innerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function downloadQRCode() {
    const img = document.querySelector('#qrCodeContent img');
    if (img) {
        const link = document.createElement('a');
        link.download = 'qr-code.png';
        link.href = img.src;
        link.click();
    }
}

// Export Functions
function exportAssets() {
    // Get selected assets or all if none selected
    const selectedCheckboxes = document.querySelectorAll('input[name="assetSelect"]:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    if (selectedIds.length === 0 && !confirm('Export all assets?')) {
        return;
    }

    // Create CSV
    const headers = ['Asset Code', 'Name', 'Category', 'Status', 'Location', 'Tags', 'Current Value'];
    const csvContent = [
        headers.join(','),
        ...currentAssets
            .filter(asset => selectedIds.length === 0 || selectedIds.includes(asset.id.toString()))
            .map(asset => [
                asset.asset_code || '',
                `"${asset.name || ''}"`,
                `"${asset.category_name || ''}"`,
                asset.status || '',
                `"${asset.location || ''}"`,
                `"${asset.tags ? asset.tags.map(t => t.name).join(', ') : ''}"`,
                asset.current_value || ''
            ].join(','))
    ].join('\n');

    // Download
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `assets-${new Date().toISOString().split('T')[0]}.csv`;
    link.click();

    showNotification('Assets exported successfully', 'success');
}

// Bulk Actions
function bulkActions() {
    const selectedCheckboxes = document.querySelectorAll('input[name="assetSelect"]:checked');

    if (selectedCheckboxes.length === 0) {
        showNotification('Please select assets first', 'warning');
        return;
    }

    // Show bulk actions menu (simplified implementation)
    const action = prompt(`Select action for ${selectedCheckboxes.length} assets:\n1. Change Status\n2. Add Tag\n3. Remove Tag\n\nEnter 1, 2, or 3:`);

    switch(action) {
        case '1':
            bulkChangeStatus(selectedCheckboxes);
            break;
        case '2':
            bulkAddTag(selectedCheckboxes);
            break;
        case '3':
            bulkRemoveTag(selectedCheckboxes);
            break;
        default:
            return;
    }
}

// Bulk Change Status
async function bulkChangeStatus(checkboxes) {
    const newStatus = prompt('Enter new status (available, assigned, maintenance, damaged, lost, disposed):');
    if (!newStatus) return;

    const validStatuses = ['available', 'assigned', 'maintenance', 'damaged', 'lost', 'disposed'];
    if (!validStatuses.includes(newStatus)) {
        showNotification('Invalid status', 'error');
        return;
    }

    // Implementation would involve API calls to update each asset
    showNotification('Bulk status update not fully implemented yet', 'info');
}

// Bulk Add Tag
function bulkAddTag(checkboxes) {
    showNotification('Bulk tag operations not fully implemented yet', 'info');
}

// Bulk Remove Tag
function bulkRemoveTag(checkboxes) {
    showNotification('Bulk tag operations not fully implemented yet', 'info');
}

// Notification Function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg max-w-sm ${getNotificationClass(type)}`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${getNotificationIcon(type)} mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationClass(type) {
    const classes = {
        'success': 'bg-green-100 text-green-800 border border-green-300',
        'error': 'bg-red-100 text-red-800 border border-red-300',
        'warning': 'bg-yellow-100 text-yellow-800 border border-yellow-300',
        'info': 'bg-blue-100 text-blue-800 border border-blue-300'
    };
    return classes[type] || classes.info;
}

function getNotificationIcon(type) {
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    return icons[type] || icons.info;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('assetsTable')) {
        initAssetManagement();
    }
});