<?php
session_start();

// Check if user is logged in (for now, we'll allow access without login for testing)
// Uncomment the following lines when you want to require login:
// if (!isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit();
// }

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
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 w-full sm:w-auto">
                        <i class="fas fa-plus mr-2"></i>Add Supply Item
                    </button>
                    <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition duration-200 w-full sm:w-auto">
                        <i class="fas fa-exchange-alt mr-2"></i>Stock Transaction
                    </button>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" placeholder="Search supplies..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <option value="office">Office Supplies</option>
                            <option value="cleaning">Cleaning Supplies</option>
                            <option value="medical">Medical Supplies</option>
                            <option value="educational">Educational Materials</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Stock Levels</option>
                            <option value="low">Low Stock</option>
                            <option value="normal">Normal Stock</option>
                            <option value="high">High Stock</option>
                            <option value="out">Out of Stock</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Status</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Items</option>
                            <option value="expired">Expired</option>
                            <option value="expiring">Expiring Soon</option>
                            <option value="valid">Valid</option>
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
                                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Expiry Date</th>
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
                            <dd class="text-lg font-medium text-gray-900">0</dd>
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
                            <dd class="text-lg font-medium text-gray-900">0</dd>
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
                            <dd class="text-lg font-medium text-gray-900">0</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500 truncate">Expiring Soon</dt>
                            <dd class="text-lg font-medium text-gray-900">0</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="js/api.js"></script>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>