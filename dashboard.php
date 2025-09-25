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

    <!-- Main Content -->
    <main class="ml-64 flex-1 overflow-x-hidden">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <div class="flex items-center gap-4">
                    <button onclick="Dashboard.refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
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