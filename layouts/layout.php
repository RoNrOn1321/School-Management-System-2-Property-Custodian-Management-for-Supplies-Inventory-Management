<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'School Management System - Property Custodian Management'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-top: #edeffe;
            --bg-bottom: #f0f9ff;
        }

        body {
            background: linear-gradient(180deg, var(--bg-top), var(--bg-bottom));
            min-height: 100vh;
        }

        /* Mobile menu toggle */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
            display: none;
        }

        .mobile-menu-overlay.active {
            display: block;
        }

        /* Responsive table wrapper */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Mobile responsive utilities */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 800px;
            }

            .mobile-hidden {
                display: none !important;
            }

            .mobile-full-width {
                width: 100% !important;
            }
        }

        /* Sidebar responsive styles */
        @media (max-width: 1024px) {
            .sidebar-mobile {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }

            .sidebar-mobile.active {
                transform: translateX(0);
            }
        }
    </style>
    <?php if (isset($additionalStyles)) echo $additionalStyles; ?>
</head>
<body>
    <?php echo $content; ?>

    <!-- Scripts -->
    <script src="js/responsive.js"></script>
    <?php
    // Only load auth.js on pages that need it
    $currentPage = basename($_SERVER['PHP_SELF']);
    if ($currentPage === 'index.php' || $currentPage === 'dashboard.php') {
        echo '<script src="js/auth.js"></script>';
    }
    ?>
    <?php if (isset($additionalScripts)) echo $additionalScripts; ?>
</body>
</html>