<?php
require_once 'includes/auth_check.php';

// If user is already logged in, redirect to dashboard
redirectIfLoggedIn();

$pageTitle = "Login - Property Custodian Management";

ob_start();
?>

<?php include 'components/login.php'; ?>

<script src="js/api.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');

    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        try {
            const response = await fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password })
            });

            const result = await response.json();

            if (response.ok) {
                // Login successful, redirect to dashboard
                window.location.href = 'dashboard.php';
            } else {
                // Show error message
                showError(result.message || 'Login failed');
            }
        } catch (error) {
            showError('An error occurred. Please try again.');
        }
    });

    function showError(message) {
        // Remove existing error messages
        const existingError = document.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Create and show error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        errorDiv.textContent = message;

        const form = document.getElementById('loginForm');
        form.parentNode.insertBefore(errorDiv, form);

        // Auto-hide error after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
});
</script>

<?php
$content = ob_get_clean();
include 'layouts/layout.php';
?>