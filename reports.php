<?php
session_start();

// Check if user is logged in (for now, we'll allow access without login for testing)
// Uncomment the following lines when you want to require login:
// if (!isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit();
// }

$pageTitle = "Reports & Analytics - Property Custodian Management";

ob_start();
?>

<!-- Reports Content -->
<div class="min-h-screen flex">
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 flex-1 overflow-x-hidden">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
                <div class="flex items-center gap-4">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                </div>
            </div>

            <!-- Reports interface goes here -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Generation</h3>
                <p class="text-gray-500">Reports and analytics interface will be implemented here.</p>
            </div>
        </div>
    </main>
</div>

<script src="js/api.js"></script>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>