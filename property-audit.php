<?php
session_start();

// Check if user is logged in (for now, we'll allow access without login for testing)
// Uncomment the following lines when you want to require login:
// if (!isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit();
// }

$pageTitle = "Property Audit - Property Custodian Management";

ob_start();
?>

<!-- Property Audit Content -->
<div class="min-h-screen flex">
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 flex-1 overflow-x-hidden">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Property Audit</h1>
                <div class="flex items-center gap-4">
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Start New Audit
                    </button>
                </div>
            </div>

            <!-- Audit Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Audit Creation -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Audit Plan</h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Audit Type</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select audit type</option>
                                    <option value="physical">Physical Count</option>
                                    <option value="condition">Condition Assessment</option>
                                    <option value="location">Location Verification</option>
                                    <option value="custodian">Custodian Verification</option>
                                    <option value="comprehensive">Comprehensive Audit</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Audit Date</label>
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Audit Scope</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded mr-2">
                                    <span class="text-sm">All Assets</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded mr-2">
                                    <span class="text-sm">By Category</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded mr-2">
                                    <span class="text-sm">By Location</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded mr-2">
                                    <span class="text-sm">By Custodian</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded mr-2">
                                    <span class="text-sm">High Value Items</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded mr-2">
                                    <span class="text-sm">Custom Selection</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Auditor</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select auditor</option>
                                    <option value="auditor1">John Doe</option>
                                    <option value="auditor2">Jane Smith</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Expected Duration (days)</label>
                                <input type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="7">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Audit Objectives</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Describe the objectives and focus areas for this audit"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                                Create Audit Plan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Audit Summary -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Audit Status</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Active Audits</span>
                                <span class="font-medium">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Completed</span>
                                <span class="font-medium">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Pending Review</span>
                                <span class="font-medium">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Discrepancies</span>
                                <span class="font-medium text-red-600">0</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-2">
                            <button class="w-full text-left px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded">
                                <i class="fas fa-qrcode mr-2"></i>Scan Asset QR Code
                            </button>
                            <button class="w-full text-left px-3 py-2 text-sm text-green-600 hover:bg-green-50 rounded">
                                <i class="fas fa-check mr-2"></i>Mark Asset as Found
                            </button>
                            <button class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded">
                                <i class="fas fa-exclamation mr-2"></i>Report Discrepancy
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Audits Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Audit History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Audit ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scope</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auditor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No audits found</td>
                            </tr>
                        </tbody>
                    </table>
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