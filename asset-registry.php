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