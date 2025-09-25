<?php
session_start();

// Check if user is logged in (for now, we'll allow access without login for testing)
// Uncomment the following lines when you want to require login:
// if (!isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit();
// }

$pageTitle = "Asset Registry & Tagging - Property Custodian Management";

ob_start();
?>

<!-- Asset Registry Content -->
<div class="min-h-screen flex">
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 flex-1 overflow-x-hidden">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Asset Registry & Tagging</h1>
                <div class="flex items-center gap-4">
                    <button onclick="App.openAssetModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Add Asset
                    </button>
                    <button onclick="App.openTagModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-tag mr-2"></i>Manage Tags
                    </button>
                </div>
            </div>

            <?php include 'components/asset-registry.php'; ?>
        </div>
    </main>
</div>

<?php include 'components/modal.php'; ?>

<script src="js/api.js"></script>
<script src="js/asset_management.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('assetsTable')) {
        initAssetManagement();
    }
});
</script>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>