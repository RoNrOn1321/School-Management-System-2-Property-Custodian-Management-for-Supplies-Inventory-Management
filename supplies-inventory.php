<?php
require_once 'includes/auth_check.php';

// Require authentication for this page
requireAuth();

$pageTitle = "Supplies Inventory - Property Custodian Management";

ob_start();
?>

<!-- Supplies Inventory Content -->
<div class="min-h-screen flex">
    <?php include 'components/sidebar.php'; ?>

    <!-- Mobile Header -->
    <div class="lg:hidden fixed top-0 left-0 right-0 bg-white shadow-md z-30 px-4 py-3 flex justify-between items-center">
        <button onclick="toggleMobileMenu()" class="p-2 text-gray-600">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-800">Supplies Inventory</h1>
        <div class="w-8"></div>
    </div>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 overflow-x-hidden">
        <div class="p-4 lg:p-8 pt-16 lg:pt-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Supplies Inventory</h1>
                <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4">
                    <button onclick="openAddSupplyModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 w-full sm:w-auto">
                        <i class="fas fa-plus mr-2"></i>Add Supply Item
                    </button>
                    <button onclick="openTransactionModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition duration-200 w-full sm:w-auto">
                        <i class="fas fa-exchange-alt mr-2"></i>Stock Transaction
                    </button>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" id="searchInput" placeholder="Search supplies..." onkeyup="filterSupplies()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="categoryFilter" onchange="filterSupplies()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <option value="office">Office Supplies</option>
                            <option value="cleaning">Cleaning Supplies</option>
                            <option value="medical">Medical Supplies</option>
                            <option value="educational">Educational Materials</option>
                            <option value="maintenance">Maintenance Supplies</option>
                            <option value="cafeteria">Cafeteria Supplies</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                        <select id="stockFilter" onchange="filterSupplies()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Stock Levels</option>
                            <option value="low">Low Stock</option>
                            <option value="normal">Normal Stock</option>
                            <option value="out">Out of Stock</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="statusFilter" onchange="filterSupplies()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="discontinued">Discontinued</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Supplies Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="table-responsive">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" class="rounded">
                                </th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Category</th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Min. Stock</th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Unit Cost</th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Location</th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="suppliesTableBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-gray-500">Loading supplies...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mt-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-boxes text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Items</dt>
                            <dd id="totalItems" class="text-lg font-medium text-gray-900">0</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">Low Stock Items</dt>
                            <dd id="lowStockItems" class="text-lg font-medium text-gray-900">0</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-times-circle text-red-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">Out of Stock</dt>
                            <dd id="outOfStockItems" class="text-lg font-medium text-gray-900">0</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-peso-sign text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Value</dt>
                            <dd id="expiringSoonItems" class="text-lg font-medium text-gray-900">₱0.00</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add Supply Modal -->
<div id="addSupplyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add Supply Item</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="supplyForm" onsubmit="handleSupplySubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Code *</label>
                        <input type="text" id="itemCode" name="item_code" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                        <input type="text" id="itemName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Category</option>
                            <option value="office">Office Supplies</option>
                            <option value="cleaning">Cleaning Supplies</option>
                            <option value="medical">Medical Supplies</option>
                            <option value="educational">Educational Materials</option>
                            <option value="maintenance">Maintenance Supplies</option>
                            <option value="cafeteria">Cafeteria Supplies</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <input type="text" id="unit" name="unit" placeholder="pcs, box, bottle, etc." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                        <input type="number" id="currentStock" name="current_stock" min="0" onchange="calculateTotalValue()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock</label>
                        <input type="number" id="minimumStock" name="minimum_stock" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost</label>
                        <input type="number" id="unitCost" name="unit_cost" step="0.01" min="0" onchange="calculateTotalValue()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Value</label>
                        <input type="number" id="totalValue" name="total_value" step="0.01" min="0" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Storage Location</label>
                        <input type="text" id="storageLocation" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active">Active</option>
                            <option value="discontinued">Discontinued</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Supply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transaction Modal -->
<div id="transactionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Stock Transaction</h3>
                <button onclick="closeTransactionModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="transactionForm" onsubmit="handleTransactionSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supply Item *</label>
                        <select id="transactionSupplyId" name="supply_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Supply Item</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type *</label>
                        <select id="transactionType" name="transaction_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Type</option>
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input type="number" id="transactionQuantity" name="quantity" required min="1" onchange="calculateTransactionCost()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost</label>
                        <input type="number" id="transactionUnitCost" name="unit_cost" step="0.01" min="0" onchange="calculateTransactionCost()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Cost</label>
                        <input type="number" id="transactionTotalCost" name="total_cost" step="0.01" min="0" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <input type="text" id="referenceNumber" name="reference_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea id="transactionNotes" name="notes" rows="3" placeholder="Enter transaction details, purpose, and any additional notes..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">Process Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/api.js"></script>
<script>
let supplies = [];
let filteredSupplies = [];
let currentEditId = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadSupplies();
});

// Load all supplies
async function loadSupplies() {
    try {
        const response = await API.getSupplies();
        supplies = response || [];
        filteredSupplies = [...supplies];
        renderSuppliesTable();
        updateSummaryCards();
        populateSupplySelect();
    } catch (error) {
        console.error('Error loading supplies:', error);
        showNotification('Error loading supplies', 'error');
    }
}

// Render supplies table
function renderSuppliesTable() {
    const tbody = document.getElementById('suppliesTableBody');

    if (filteredSupplies.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="px-6 py-4 text-center text-gray-500">No supplies found</td></tr>';
        return;
    }

    tbody.innerHTML = filteredSupplies.map(supply => {
        const stockStatus = getStockStatus(supply);
        const statusBadge = getStatusBadge(supply.status);

        return `
            <tr class="hover:bg-gray-50">
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="rounded">
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${supply.item_code}
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${supply.name}</div>
                    <div class="text-sm text-gray-500">${supply.description || ''}</div>
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                    ${supply.category || '-'}
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${supply.current_stock} ${supply.unit || 'pcs'}</div>
                    <div class="text-xs ${stockStatus.color}">${stockStatus.text}</div>
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                    ${supply.minimum_stock} ${supply.unit || 'pcs'}
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                    ${supply.unit_cost ? '₱' + parseFloat(supply.unit_cost).toFixed(2) : '-'}
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden xl:table-cell">
                    <div>${supply.location || '-'}</div>
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap">
                    ${statusBadge}
                </td>
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <button onclick="editSupply(${supply.id})" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteSupply(${supply.id})" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button onclick="quickTransaction(${supply.id})" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Get stock status
function getStockStatus(supply) {
    const current = parseInt(supply.current_stock) || 0;
    const minimum = parseInt(supply.minimum_stock) || 0;

    if (current === 0) {
        return { text: 'Out of Stock', color: 'text-red-600' };
    } else if (current <= minimum) {
        return { text: 'Low Stock', color: 'text-yellow-600' };
    } else {
        return { text: 'Normal', color: 'text-green-600' };
    }
}

// Get status badge
function getStatusBadge(status) {
    const badges = {
        'active': '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>',
        'discontinued': '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Discontinued</span>',
        'expired': '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Expired</span>'
    };
    return badges[status] || badges['active'];
}


// Update summary cards
function updateSummaryCards() {
    const total = supplies.length;
    const lowStock = supplies.filter(s => (parseInt(s.current_stock) || 0) <= (parseInt(s.minimum_stock) || 0) && parseInt(s.current_stock) > 0).length;
    const outOfStock = supplies.filter(s => (parseInt(s.current_stock) || 0) === 0).length;
    const totalValue = supplies.reduce((sum, s) => sum + (parseFloat(s.total_value) || 0), 0);

    document.getElementById('totalItems').textContent = total;
    document.getElementById('lowStockItems').textContent = lowStock;
    document.getElementById('outOfStockItems').textContent = outOfStock;
    document.getElementById('expiringSoonItems').textContent = '₱' + totalValue.toLocaleString('en-PH', {minimumFractionDigits: 2});
}

// Populate supply select for transactions
function populateSupplySelect() {
    const select = document.getElementById('transactionSupplyId');
    select.innerHTML = '<option value="">Select Supply Item</option>' +
        supplies.map(supply => `<option value="${supply.id}">${supply.item_code} - ${supply.name}</option>`).join('');
}

// Filter supplies
function filterSupplies() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const stockFilter = document.getElementById('stockFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    filteredSupplies = supplies.filter(supply => {
        const matchesSearch = !search ||
            supply.name.toLowerCase().includes(search) ||
            supply.item_code.toLowerCase().includes(search) ||
            (supply.description && supply.description.toLowerCase().includes(search));

        const matchesCategory = !category || supply.category === category;
        const matchesStatus = !statusFilter || supply.status === statusFilter;

        let matchesStock = true;
        if (stockFilter) {
            const current = parseInt(supply.current_stock) || 0;
            const minimum = parseInt(supply.minimum_stock) || 0;

            switch(stockFilter) {
                case 'low':
                    matchesStock = current <= minimum && current > 0;
                    break;
                case 'normal':
                    matchesStock = current > minimum;
                    break;
                case 'out':
                    matchesStock = current === 0;
                    break;
            }
        }

        return matchesSearch && matchesCategory && matchesStatus && matchesStock;
    });

    renderSuppliesTable();
}

// Modal functions
function openAddSupplyModal() {
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Add Supply Item';
    document.getElementById('supplyForm').reset();
    document.getElementById('addSupplyModal').classList.remove('hidden');
}

function editSupply(id) {
    const supply = supplies.find(s => s.id == id);
    if (!supply) return;

    currentEditId = id;
    document.getElementById('modalTitle').textContent = 'Edit Supply Item';

    // Populate form
    document.getElementById('itemCode').value = supply.item_code || '';
    document.getElementById('itemName').value = supply.name || '';
    document.getElementById('description').value = supply.description || '';
    document.getElementById('category').value = supply.category || '';
    document.getElementById('unit').value = supply.unit || '';
    document.getElementById('currentStock').value = supply.current_stock || '';
    document.getElementById('minimumStock').value = supply.minimum_stock || '';
    document.getElementById('totalValue').value = supply.total_value || '';
    document.getElementById('unitCost').value = supply.unit_cost || '';
    document.getElementById('storageLocation').value = supply.location || '';
    document.getElementById('status').value = supply.status || 'active';

    document.getElementById('addSupplyModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('addSupplyModal').classList.add('hidden');
    currentEditId = null;
}

function openTransactionModal() {
    document.getElementById('transactionForm').reset();
    document.getElementById('transactionModal').classList.remove('hidden');
}

function quickTransaction(supplyId) {
    openTransactionModal();
    document.getElementById('transactionSupplyId').value = supplyId;
}

function closeTransactionModal() {
    document.getElementById('transactionModal').classList.add('hidden');
}

// Handle form submissions
async function handleSupplySubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    try {
        if (currentEditId) {
            await API.updateSupply(currentEditId, data);
            showNotification('Supply updated successfully', 'success');
        } else {
            await API.createSupply(data);
            showNotification('Supply created successfully', 'success');
        }

        closeModal();
        loadSupplies();
    } catch (error) {
        console.error('Error saving supply:', error);
        showNotification('Error saving supply', 'error');
    }
}

async function handleTransactionSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    try {
        await API.createTransaction(data);
        showNotification('Transaction processed successfully', 'success');
        closeTransactionModal();
        loadSupplies();
    } catch (error) {
        console.error('Error processing transaction:', error);
        showNotification('Error processing transaction', 'error');
    }
}

// Delete supply
async function deleteSupply(id) {
    if (!confirm('Are you sure you want to delete this supply item?')) return;

    try {
        await API.deleteSupply(id);
        showNotification('Supply deleted successfully', 'success');
        loadSupplies();
    } catch (error) {
        console.error('Error deleting supply:', error);
        showNotification('Error deleting supply', 'error');
    }
}

// Calculate total value for supply
function calculateTotalValue() {
    const currentStock = parseFloat(document.getElementById('currentStock').value) || 0;
    const unitCost = parseFloat(document.getElementById('unitCost').value) || 0;
    const totalValue = currentStock * unitCost;
    document.getElementById('totalValue').value = totalValue.toFixed(2);
}

// Calculate total cost for transaction
function calculateTransactionCost() {
    const quantity = parseFloat(document.getElementById('transactionQuantity').value) || 0;
    const unitCost = parseFloat(document.getElementById('transactionUnitCost').value) || 0;
    const totalCost = quantity * unitCost;
    document.getElementById('transactionTotalCost').value = totalCost.toFixed(2);
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>