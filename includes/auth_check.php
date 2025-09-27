<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'department' => $_SESSION['department'] ?? ''
    ];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireRole($role) {
    requireAuth();
    if ($_SESSION['role'] !== $role) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied. Required role: ' . $role;
        exit();
    }
}

function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
}
?>