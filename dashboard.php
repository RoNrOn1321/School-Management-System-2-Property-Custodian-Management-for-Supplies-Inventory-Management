<?php
session_start();

// Check if user is logged in (for now, we'll allow access without login for testing)
// Uncomment the following lines when you want to require login:
// if (!isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit();
// }

$pageTitle = "Dashboard - Property Custodian Management";

ob_start();
?>

<!-- Dashboard Content -->
<div class="min-h-screen flex">
    <?php include 'components/sidebar.php'; ?>

    <!-- Mobile Header -->
    <div class="lg:hidden fixed top-0 left-0 right-0 bg-white shadow-md z-30 px-4 py-3 flex justify-between items-center">
        <button onclick="toggleMobileMenu()" class="p-2 text-gray-600">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-800">Dashboard</h1>
        <div class="w-8"></div>
    </div>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 overflow-x-hidden">
        <div class="p-4 lg:p-8 pt-16 lg:pt-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Dashboard</h1>
                <div class="flex items-center gap-4">
                    <button onclick="Dashboard.refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 w-full sm:w-auto">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>

            <?php include 'components/dashboard.php'; ?>
        </div>
    </main>
</div>

<script src="js/api.js"></script>
<script src="js/dashboard.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Dashboard.loadData();
});
</script>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>