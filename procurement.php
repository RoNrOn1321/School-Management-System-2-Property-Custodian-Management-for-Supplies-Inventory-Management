<?php
require_once 'includes/auth_check.php';

// Require authentication for this page
requireAuth();

$pageTitle = "Procurement - Property Custodian Management";

ob_start();
?>

<!-- Procurement Content -->
<div class="min-h-screen flex">
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 flex-1 overflow-x-hidden">
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Procurement Management</h1>
                <div class="flex items-center gap-2 sm:gap-4">
                    <button onclick="openModal('newRequestModal')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-plus mr-1 sm:mr-2"></i>New Request
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <div class="flex items-center">
                        <div class="p-2 sm:p-3 rounded-full bg-blue-100 text-blue-600 flex-shrink-0">
                            <i class="fas fa-file-alt text-lg sm:text-xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Requests</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900" id="totalRequests">0</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <div class="flex items-center">
                        <div class="p-2 sm:p-3 rounded-full bg-yellow-100 text-yellow-600 flex-shrink-0">
                            <i class="fas fa-clock text-lg sm:text-xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Pending</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900" id="pendingRequests">0</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <div class="flex items-center">
                        <div class="p-2 sm:p-3 rounded-full bg-green-100 text-green-600 flex-shrink-0">
                            <i class="fas fa-check text-lg sm:text-xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Approved</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900" id="approvedRequests">0</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <div class="flex items-center">
                        <div class="p-2 sm:p-3 rounded-full bg-purple-100 text-purple-600 flex-shrink-0">
                            <i class="fas fa-peso-sign text-lg sm:text-xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Cost</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900" id="totalCost">₱0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <div class="space-y-4 sm:space-y-0 sm:flex sm:flex-wrap sm:gap-4 sm:items-center sm:justify-between">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 flex-1">
                        <input type="text" id="searchInput" placeholder="Search requests..."
                               class="w-full px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">

                        <select id="statusFilter" class="w-full px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="ordered">Ordered</option>
                            <option value="received">Received</option>
                        </select>

                        <select id="priorityFilter" class="w-full px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">
                            <option value="">All Priority</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>

                        <select id="typeFilter" class="w-full px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">
                            <option value="">All Types</option>
                            <option value="asset">Asset</option>
                            <option value="supply">Supply</option>
                            <option value="service">Service</option>
                        </select>
                    </div>

                    <button id="refreshBtn" class="w-full sm:w-auto bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Procurement Requests Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Procurement Requests</h3>
                </div>

                <!-- Mobile Card View (hidden on larger screens) -->
                <div class="block lg:hidden" id="mobileRequestsList">
                    <!-- Mobile cards will be populated here -->
                </div>

                <!-- Desktop Table View (hidden on mobile) -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requestor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="procurementTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="text-sm text-gray-500 text-center sm:text-left" id="paginationInfo">
                            Showing 0 to 0 of 0 results
                        </div>
                        <div class="flex items-center justify-center space-x-2" id="paginationControls">
                            <!-- Pagination controls will be generated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- New Request Modal -->
<div id="newRequestModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-full sm:w-11/12 max-w-4xl shadow-lg rounded-md bg-white min-h-screen sm:min-h-0">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900">New Procurement Request</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600" onclick="closeModal('newRequestModal')">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>

            <form id="newRequestForm" class="mt-4 sm:mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Request Type *</label>
                        <select id="requestType" name="request_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Type</option>
                            <option value="asset">Asset</option>
                            <option value="supply">Supply</option>
                            <option value="service">Service</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                        <input type="text" id="requestDepartment" name="department" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Request Date *</label>
                        <input type="date" id="requestDate" name="request_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Required Date</label>
                        <input type="date" id="requiredDate" name="required_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select id="requestPriority" name="priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requestor ID *</label>
                        <input type="number" id="requestorId" name="requestor_id" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Justification *</label>
                    <textarea id="requestJustification" name="justification" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Explain why this procurement is needed..."></textarea>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="requestNotes" name="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Additional notes or comments..."></textarea>
                </div>

                <!-- Items Section -->
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Request Items</h4>
                        <button type="button" id="addItemBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Add Item
                        </button>
                    </div>

                    <div id="itemsContainer">
                        <!-- Items will be added here dynamically -->
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeModal('newRequestModal')"
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition duration-200 text-sm sm:text-base">
                        Cancel
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 text-sm sm:text-base">
                        <i class="fas fa-save mr-2"></i>Save Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div id="viewRequestModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-full sm:w-11/12 max-w-4xl shadow-lg rounded-md bg-white min-h-screen sm:min-h-0">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Procurement Request Details</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600" onclick="closeModal('viewRequestModal')">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>

            <div id="requestDetailsContent" class="overflow-auto">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script src="js/api.js"></script>
<script src="js/procurement.js"></script>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>