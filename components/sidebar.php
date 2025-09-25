<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMobileMenu()"></div>

<!-- Sidebar -->
<nav class="bg-white shadow-lg w-64 fixed h-full overflow-y-auto sidebar-mobile lg:translate-x-0 z-50" id="sidebar">
    <div class="p-6 border-b border-gray-200">
        <img src="logos/logo.jpg" alt="School Logo" class="h-12 w-12 rounded-full mx-auto mb-2">
        <h2 class="text-lg font-bold text-gray-800 text-center">Property Custodian</h2>
        <p class="text-sm text-gray-600 text-center">Management System</p>
    </div>

    <ul class="mt-6">
        <li><a href="dashboard.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-chart-line mr-3"></i> Dashboard
        </a></li>
        <li><a href="asset-registry.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'asset-registry.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-tags mr-3"></i> Asset Registry & Tagging
        </a></li>
        <li><a href="property-issuance.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'property-issuance.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-handshake mr-3"></i> Property Issuance
        </a></li>
        <li><a href="supplies-inventory.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'supplies-inventory.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-boxes mr-3"></i> Supplies Inventory
        </a></li>
        <li><a href="custodian-assignment.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'custodian-assignment.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-user-tie mr-3"></i> Custodian Assignment
        </a></li>
        <li><a href="maintenance.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'maintenance.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-wrench mr-3"></i> Preventive Maintenance
        </a></li>
        <li><a href="damaged-items.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'damaged-items.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-exclamation-triangle mr-3"></i> Damaged Items
        </a></li>
        <li><a href="property-audit.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'property-audit.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-clipboard-check mr-3"></i> Property Audit
        </a></li>
        <li><a href="procurement.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'procurement.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-shopping-cart mr-3"></i> Procurement
        </a></li>
        <li><a href="reports.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
            <i class="fas fa-chart-bar mr-3"></i> Reports & Analytics
        </a></li>
        <li><a href="user-roles.php" class="menu-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?php echo (basename($_SERVER['PHP_SELF']) == 'user-roles.php') ? 'border-r-4 border-blue-600 bg-blue-50 text-blue-600' : ''; ?>">
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

<script>
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileMenuOverlay');

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

// Close mobile menu when clicking on a menu item
document.addEventListener('DOMContentLoaded', function() {
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleMobileMenu();
            }
        });
    });
});
</script>