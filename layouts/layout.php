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
    </style>
    <?php if (isset($additionalStyles)) echo $additionalStyles; ?>
</head>
<body>
    <?php echo $content; ?>

    <!-- Scripts -->
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