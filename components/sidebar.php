<!-- Sidebar -->
<nav class="bg-white shadow-lg w-64 fixed h-full overflow-y-auto">
    <div class="p-6 border-b border-gray-200">
        <img src="logos/logo.jpg" alt="School Logo" class="h-12 w-12 rounded-full mx-auto mb-2">
        <h2 class="text-lg font-bold text-gray-800 text-center">Property Custodian</h2>
        <p class="text-sm text-gray-600 text-center">Management System</p>
    </div>

    <ul class="mt-6">
        <li><a href="#" data-module="dashboard" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-r-4 border-blue-600 bg-blue-50 text-blue-600">
            <i class="fas fa-chart-line mr-3"></i> Dashboard
        </a></li>
        <li><a href="#" data-module="asset-registry" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-tags mr-3"></i> Asset Registry & Tagging
        </a></li>
        <li><a href="#" data-module="property-issuance" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-handshake mr-3"></i> Property Issuance
        </a></li>
        <li><a href="#" data-module="supplies-inventory" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-boxes mr-3"></i> Supplies Inventory
        </a></li>
        <li><a href="#" data-module="custodian-assignment" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-user-tie mr-3"></i> Custodian Assignment
        </a></li>
        <li><a href="#" data-module="maintenance" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-wrench mr-3"></i> Preventive Maintenance
        </a></li>
        <li><a href="#" data-module="damaged-items" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-exclamation-triangle mr-3"></i> Damaged Items
        </a></li>
        <li><a href="#" data-module="property-audit" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-clipboard-check mr-3"></i> Property Audit
        </a></li>
        <li><a href="#" data-module="procurement" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-shopping-cart mr-3"></i> Procurement
        </a></li>
        <li><a href="#" data-module="reports" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-chart-bar mr-3"></i> Reports & Analytics
        </a></li>
        <li><a href="#" data-module="user-roles" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="fas fa-users-cog mr-3"></i> User Roles & Access
        </a></li>
    </ul>

    <div class="absolute bottom-0 w-64 p-6 border-t border-gray-200 bg-white">
        <div class="flex items-center mb-4">
            <i class="fas fa-user-circle text-2xl text-gray-400 mr-3"></i>
            <div>
                <p id="currentUsername" class="text-sm font-medium text-gray-700">Admin</p>
                <p id="currentRole" class="text-xs text-gray-500">Administrator</p>
            </div>
        </div>
        <button id="logoutBtn" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-200">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
        </button>
    </div>
</nav>