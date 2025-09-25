<?php
session_start();

// For now, allow access without login by redirecting to dashboard
// Later, you can implement proper login logic here
header('Location: dashboard.php');
exit();
?>