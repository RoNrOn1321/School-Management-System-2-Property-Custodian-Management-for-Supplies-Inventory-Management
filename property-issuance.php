<?php
require_once 'includes/auth_check.php';

// Require authentication for this page
requireAuth();

$pageTitle = "Property Issuance - Property Custodian Management";

ob_start();
?>

<!-- Property Issuance Content -->
<div class="min-h-screen flex">
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 overflow-x-hidden lg:ml-64">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white shadow-sm border-b">
            <div class="flex items-center justify-between p-4">
                <h1 class="text-lg font-semibold text-gray-900">Property Issuance</h1>
                <button id="mobileSidebarToggle" class="p-2 text-gray-600 hover:text-gray-900">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <div class="p-4 sm:p-6 lg:p-8">
            <div class="hidden lg:flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 lg:mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4 sm:mb-0">Property Issuance</h1>
                <div class="flex items-center gap-4">
                    <button id="testApiBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-bug mr-2"></i>Test API
                    </button>
                    <button id="newIssuanceBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-plus mr-2"></i>New Issuance
                    </button>
                </div>
            </div>

            <!-- Property Issuance Interface -->
            <div id="issuanceFormContainer" class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Create New Property Issuance</h2>
                    <p class="text-sm text-gray-600 mt-1">Fill out the form below to issue property to a staff member</p>
                </div>
                <form id="issuanceForm">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6 mb-6">
                        <!-- Asset Selection -->
                        <div class="space-y-4">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Select Asset</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Asset Code</label>
                                    <input type="text" id="assetCode" name="asset_code" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter asset code or scan QR">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Asset Name</label>
                                    <select id="assetSelect" name="asset_id" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        <option value="">Select Asset</option>
                                    </select>
                                </div>
                                <div id="assetDetails" class="hidden bg-gray-50 p-3 rounded-md">
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1"><strong>Description:</strong> <span id="assetDescription"></span></p>
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1"><strong>Location:</strong> <span id="assetLocation"></span></p>
                                    <p class="text-xs sm:text-sm text-gray-600"><strong>Condition:</strong> <span id="assetCondition"></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Recipient Information -->
                        <div class="space-y-4">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Recipient Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                                    <input type="text" id="employeeId" name="employee_id" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter employee ID" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" id="recipientName" name="recipient_name" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter full name" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                    <select id="department" name="department" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        <option value="">Select Department</option>
                                        <option value="administration">Administration</option>
                                        <option value="academic">Academic Affairs</option>
                                        <option value="finance">Finance</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="it">Information Technology</option>
                                        <option value="library">Library</option>
                                        <option value="security">Security</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Issuance Details -->
                    <div class="space-y-4 mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Issuance Details</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date</label>
                                <input type="date" id="issueDate" name="issue_date" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Expected Return Date</label>
                                <input type="date" id="expectedReturnDate" name="expected_return_date" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purpose/Remarks</label>
                            <textarea id="purpose" name="purpose" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Enter purpose or additional remarks"></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4">
                        <button type="button" id="cancelBtn" class="w-full sm:w-auto px-4 sm:px-6 py-2 text-sm sm:text-base border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn" class="w-full sm:w-auto px-4 sm:px-6 py-2 text-sm sm:text-base bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                            Issue Property
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Issuances -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mt-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Recent Property Issuances</h3>
                    <button id="refreshIssuances" class="w-full sm:w-auto px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition duration-200">
                        <i class="fas fa-refresh mr-2"></i>Refresh
                    </button>
                </div>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset Code</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Asset Name</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Department</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Issue Date</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Expected Return</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="issuancesTableBody" class="bg-white divide-y divide-gray-200">
                                <tr id="loadingRow">
                                    <td colspan="8" class="px-3 sm:px-6 py-4 text-center text-gray-500 text-sm">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading issuances...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="js/api.js?v=<?php echo time(); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check if API class and methods are available
    console.log('API class:', API);
    console.log('API.getAvailableAssets:', typeof API.getAvailableAssets);
    console.log('API.getPropertyIssuances:', typeof API.getPropertyIssuances);

    // Debug: List all API methods
    console.log('All API methods:', Object.getOwnPropertyNames(API).filter(prop => typeof API[prop] === 'function'));

    // Fallback: Add missing methods if they don't exist
    if (typeof API.getAvailableAssets !== 'function') {
        console.log('Adding fallback getAvailableAssets method');
        API.getAvailableAssets = function() {
            return API.request('assets.php?status=available');
        };
    }

    if (typeof API.getPropertyIssuances !== 'function') {
        console.log('Adding fallback getPropertyIssuances method');
        API.getPropertyIssuances = function() {
            return API.request('property_issuance.php');
        };
    }

    // Add test method
    API.testPropertyIssuanceAPI = function() {
        return API.request('test_property_issuance.php');
    };

    if (typeof API.createPropertyIssuance !== 'function') {
        console.log('Adding fallback createPropertyIssuance method');
        API.createPropertyIssuance = function(issuanceData) {
            return API.request('property_issuance.php', {
                method: 'POST',
                body: JSON.stringify(issuanceData)
            });
        };
    }

    if (typeof API.updatePropertyIssuance !== 'function') {
        console.log('Adding fallback updatePropertyIssuance method');
        API.updatePropertyIssuance = function(id, issuanceData) {
            return API.request(`property_issuance.php?id=${id}`, {
                method: 'PUT',
                body: JSON.stringify(issuanceData)
            });
        };
    }

    if (typeof API.deletePropertyIssuance !== 'function') {
        console.log('Adding fallback deletePropertyIssuance method');
        API.deletePropertyIssuance = function(id) {
            return API.request(`property_issuance.php?id=${id}`, {
                method: 'DELETE'
            });
        };
    }

    // Form elements
    const issuanceForm = document.getElementById('issuanceForm');
    const assetCodeInput = document.getElementById('assetCode');
    const assetSelect = document.getElementById('assetSelect');
    const assetDetails = document.getElementById('assetDetails');
    const issueDate = document.getElementById('issueDate');
    const cancelBtn = document.getElementById('cancelBtn');
    const submitBtn = document.getElementById('submitBtn');
    const refreshBtn = document.getElementById('refreshIssuances');

    // Set default issue date to today
    issueDate.value = new Date().toISOString().split('T')[0];

    // Load available assets
    loadAvailableAssets();

    // Load recent issuances
    loadRecentIssuances();

    // Asset code input handler
    assetCodeInput.addEventListener('input', function() {
        const code = this.value.trim();
        if (code) {
            const option = Array.from(assetSelect.options).find(opt =>
                opt.dataset.assetCode && opt.dataset.assetCode.toLowerCase() === code.toLowerCase()
            );
            if (option) {
                assetSelect.value = option.value;
                showAssetDetails(option);
            }
        }
    });

    // Asset select change handler
    assetSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            assetCodeInput.value = selectedOption.dataset.assetCode || '';
            showAssetDetails(selectedOption);
        } else {
            hideAssetDetails();
        }
    });

    // Form submission
    issuanceForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const issuanceData = {
            asset_id: formData.get('asset_id'),
            employee_id: formData.get('employee_id'),
            recipient_name: formData.get('recipient_name'),
            department: formData.get('department'),
            issue_date: formData.get('issue_date'),
            expected_return_date: formData.get('expected_return_date') || null,
            purpose: formData.get('purpose') || null
        };

        // Validate required fields
        if (!issuanceData.asset_id || !issuanceData.employee_id || !issuanceData.recipient_name || !issuanceData.department) {
            showNotification('Please fill in all required fields', 'error');
            return;
        }

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

            const response = await API.createPropertyIssuance(issuanceData);
            showNotification('Property issued successfully!', 'success');

            // Reset form
            issuanceForm.reset();
            issueDate.value = new Date().toISOString().split('T')[0];
            hideAssetDetails();

            // Reload data
            loadAvailableAssets();
            loadRecentIssuances();

        } catch (error) {
            console.error('Error creating issuance:', error);
            showNotification(error.message || 'Failed to issue property', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Issue Property';
        }
    });

    // Cancel button
    cancelBtn.addEventListener('click', function() {
        issuanceForm.reset();
        issueDate.value = new Date().toISOString().split('T')[0];
        hideAssetDetails();
    });

    // Refresh button
    refreshBtn.addEventListener('click', function() {
        loadRecentIssuances();
    });

    // Test API button
    const testApiBtn = document.getElementById('testApiBtn');
    if (testApiBtn) {
        testApiBtn.addEventListener('click', async function() {
            console.log('Testing API...');
            try {
                const result = await API.testPropertyIssuanceAPI();
                console.log('Test API result:', result);
                showNotification('Test API successful: ' + result.message, 'success');
            } catch (error) {
                console.error('Test API failed:', error);
                showNotification('Test API failed: ' + error.message, 'error');
            }
        });
    }

    // New Issuance button
    const newIssuanceBtn = document.getElementById('newIssuanceBtn');
    if (newIssuanceBtn) {
        newIssuanceBtn.addEventListener('click', function() {
            // Clear and reset the form
            issuanceForm.reset();
            issueDate.value = new Date().toISOString().split('T')[0];
            hideAssetDetails();

            // Add highlight effect to form container
            const formContainer = document.getElementById('issuanceFormContainer');
            formContainer.classList.add('ring-2', 'ring-green-500', 'ring-opacity-50');

            // Scroll to the form
            formContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Focus on the first input after scroll
            setTimeout(() => {
                document.getElementById('assetCode').focus();
                // Remove highlight after a moment
                setTimeout(() => {
                    formContainer.classList.remove('ring-2', 'ring-green-500', 'ring-opacity-50');
                }, 2000);
            }, 500);

            showNotification('Ready to create new property issuance', 'success');
        });
    }

    // Functions
    async function loadAvailableAssets() {
        try {
            const response = await API.getAvailableAssets();
            const assets = response.assets || [];

            // Clear existing options (except the first one)
            assetSelect.innerHTML = '<option value="">Select Asset</option>';

            assets.forEach(asset => {
                const option = document.createElement('option');
                option.value = asset.id;
                option.textContent = `${asset.asset_code} - ${asset.name}`;
                option.dataset.assetCode = asset.asset_code;
                option.dataset.description = asset.description || '';
                option.dataset.location = asset.location || '';
                option.dataset.condition = asset.condition_status || '';
                assetSelect.appendChild(option);
            });

        } catch (error) {
            console.error('Error loading assets:', error);
            showNotification('Failed to load available assets', 'error');
        }
    }

    async function loadRecentIssuances() {
        try {
            const tableBody = document.getElementById('issuancesTableBody');
            tableBody.innerHTML = '<tr><td colspan="8" class="px-3 sm:px-6 py-4 text-center text-gray-500 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i>Loading issuances...</td></tr>';

            const response = await API.getPropertyIssuances();
            const issuances = response.issuances || [];

            if (issuances.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8" class="px-3 sm:px-6 py-4 text-center text-gray-500 text-sm">No recent issuances</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            issuances.forEach(issuance => {
                const row = createIssuanceRow(issuance);
                tableBody.appendChild(row);
            });

        } catch (error) {
            console.error('Error loading issuances:', error);
            const tableBody = document.getElementById('issuancesTableBody');
            tableBody.innerHTML = '<tr><td colspan="8" class="px-3 sm:px-6 py-4 text-center text-red-500 text-sm">Failed to load issuances</td></tr>';
        }
    }

    function createIssuanceRow(issuance) {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';

        const statusClass = getStatusClass(issuance.status);
        const statusText = getStatusText(issuance.status);

        row.innerHTML = `
            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">${issuance.asset_code || 'N/A'}</td>
            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden sm:table-cell">${issuance.asset_name || 'N/A'}</td>
            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                <div>
                    <div class="font-medium">${issuance.recipient_name}</div>
                    <div class="text-xs text-gray-500 sm:hidden">${issuance.asset_name || 'N/A'}</div>
                    <div class="text-xs text-gray-500 lg:hidden">${issuance.department || 'N/A'}</div>
                    <div class="text-xs text-gray-500 md:hidden">${formatDate(issuance.issue_date)}</div>
                </div>
            </td>
            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden lg:table-cell">${issuance.department || 'N/A'}</td>
            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden md:table-cell">${formatDate(issuance.issue_date)}</td>
            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden xl:table-cell">${formatDate(issuance.expected_return_date)}</td>
            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                    ${statusText}
                </span>
            </td>
            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-1 sm:space-x-2">
                    ${issuance.status === 'issued' ?
                        `<button onclick="returnProperty(${issuance.id})" class="text-green-600 hover:text-green-900 p-1" title="Mark as Returned">
                            <i class="fas fa-undo text-xs sm:text-sm"></i>
                        </button>` : ''
                    }
                    <button onclick="editIssuance(${issuance.id})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                        <i class="fas fa-edit text-xs sm:text-sm"></i>
                    </button>
                    <button onclick="deleteIssuance(${issuance.id})" class="text-red-600 hover:text-red-900 p-1" title="Delete">
                        <i class="fas fa-trash text-xs sm:text-sm"></i>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    function showAssetDetails(option) {
        document.getElementById('assetDescription').textContent = option.dataset.description || 'N/A';
        document.getElementById('assetLocation').textContent = option.dataset.location || 'N/A';
        document.getElementById('assetCondition').textContent = option.dataset.condition || 'N/A';
        assetDetails.classList.remove('hidden');
    }

    function hideAssetDetails() {
        assetDetails.classList.add('hidden');
    }

    function getStatusClass(status) {
        switch (status) {
            case 'issued': return 'bg-blue-100 text-blue-800';
            case 'returned': return 'bg-green-100 text-green-800';
            case 'overdue': return 'bg-red-100 text-red-800';
            case 'damaged': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function getStatusText(status) {
        switch (status) {
            case 'issued': return 'Issued';
            case 'returned': return 'Returned';
            case 'overdue': return 'Overdue';
            case 'damaged': return 'Damaged';
            default: return status;
        }
    }
});

// Global functions for row actions
async function returnProperty(issuanceId) {
    if (!confirm('Mark this property as returned?')) return;

    try {
        await API.updatePropertyIssuance(issuanceId, { status: 'returned' });
        showNotification('Property marked as returned', 'success');
        document.getElementById('refreshIssuances').click();
    } catch (error) {
        console.error('Error returning property:', error);
        showNotification('Failed to return property', 'error');
    }
}

async function editIssuance(issuanceId) {
    // This would open an edit modal or navigate to edit page
    showNotification('Edit functionality not implemented yet', 'warning');
}

async function deleteIssuance(issuanceId) {
    if (!confirm('Are you sure you want to delete this issuance record?')) return;

    try {
        await API.deletePropertyIssuance(issuanceId);
        showNotification('Issuance record deleted', 'success');
        document.getElementById('refreshIssuances').click();
    } catch (error) {
        console.error('Error deleting issuance:', error);
        showNotification('Failed to delete issuance record', 'error');
    }
}
</script>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>