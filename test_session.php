<?php
session_start();

echo "<h2>Session Test</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Data:</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Authentication Check</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✓ User is logged in as: " . $_SESSION['username'] . " (ID: " . $_SESSION['user_id'] . ")</p>";
} else {
    echo "<p style='color: red;'>✗ User is not logged in</p>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
}

echo "<h3>Test API Call with Session</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<button onclick='testAPI()'>Test Custodian API</button>";
    echo "<div id='apiResult'></div>";

    echo "<script>
    async function testAPI() {
        try {
            const response = await fetch('api/custodian_assignments.php?action=custodians');
            const data = await response.json();
            document.getElementById('apiResult').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        } catch (error) {
            document.getElementById('apiResult').innerHTML = '<p style=\"color: red;\">Error: ' + error.message + '</p>';
        }
    }
    </script>";
}
?>