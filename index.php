<?php
$pageTitle = "School Management System - Property Custodian Management";

ob_start();
?>

<?php include 'components/login.php'; ?>

<!-- Main Application -->
<div id="mainApp" class="hidden min-h-screen flex">
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 flex-1 overflow-x-hidden">
        <div id="content-area" class="p-8">
            <?php include 'components/dashboard.php'; ?>

            <!-- Other modules will be loaded dynamically -->
            <div id="dynamic-content" class="hidden">
                <!-- Module content will be loaded here via JavaScript -->
            </div>
        </div>
    </main>
</div>

<?php include 'components/modal.php'; ?>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>