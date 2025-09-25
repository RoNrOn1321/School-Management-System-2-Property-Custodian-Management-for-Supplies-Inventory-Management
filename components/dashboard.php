<!-- Dashboard Module -->
<div id="dashboard-module" class="module">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Property Management Dashboard</h1>
        <p class="text-gray-600">Welcome back, <span id="welcomeUser" class="font-medium">Admin</span></p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-full p-3 mr-4">
                    <i class="fas fa-boxes text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Total Assets</h3>
                    <p class="text-2xl font-bold text-blue-600" id="totalAssets">0</p>
                    <span class="text-sm text-green-500">+5 this month</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-green-500 rounded-full p-3 mr-4">
                    <i class="fas fa-check-circle text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Available Items</h3>
                    <p class="text-2xl font-bold text-green-600" id="availableItems">0</p>
                    <span class="text-sm text-gray-500">In good condition</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-yellow-500 rounded-full p-3 mr-4">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Needs Maintenance</h3>
                    <p class="text-2xl font-bold text-yellow-600" id="maintenanceItems">0</p>
                    <span class="text-sm text-yellow-500">Requires attention</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-red-500 rounded-full p-3 mr-4">
                    <i class="fas fa-times-circle text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Damaged/Lost</h3>
                    <p class="text-2xl font-bold text-red-600" id="damagedItems">0</p>
                    <span class="text-sm text-red-500">Out of service</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Activities</h3>
            <div id="recentActivities" class="space-y-4">
                <!-- Activities will be loaded here -->
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Alerts & Notifications</h3>
            <div id="alertsList" class="space-y-4">
                <!-- Alerts will be loaded here -->
            </div>
        </div>
    </div>
</div>