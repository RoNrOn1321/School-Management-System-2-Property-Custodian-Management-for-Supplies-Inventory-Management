<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .button { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .success { color: green; }
        .error { color: red; }
        input { margin: 5px; padding: 8px; width: 200px; }
        label { display: inline-block; width: 120px; }
    </style>
</head>
<body>
    <h1>Authentication Functions Test</h1>

    <div class="test-section">
        <h2>User Registration Test</h2>
        <form id="registerForm">
            <div><label>Username:</label><input type="text" id="regUsername" required></div>
            <div><label>Password:</label><input type="password" id="regPassword" required></div>
            <div><label>Full Name:</label><input type="text" id="regFullName" required></div>
            <div><label>Email:</label><input type="email" id="regEmail" required></div>
            <div><label>Role:</label>
                <select id="regRole">
                    <option value="staff">Staff</option>
                    <option value="custodian">Custodian</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div><label>Department:</label><input type="text" id="regDepartment" value="General"></div>
            <button type="submit" class="button">Register User</button>
        </form>
        <div id="registerResult"></div>
    </div>

    <div class="test-section">
        <h2>User Login Test</h2>
        <form id="loginForm">
            <div><label>Username:</label><input type="text" id="loginUsername" required></div>
            <div><label>Password:</label><input type="password" id="loginPassword" required></div>
            <button type="submit" class="button">Login</button>
        </form>
        <div id="loginResult"></div>
    </div>

    <div class="test-section">
        <h2>Demo Login Test</h2>
        <button class="button" onclick="testDemoLogin('admin', 'admin123')">Test Admin Login</button>
        <button class="button" onclick="testDemoLogin('custodian', 'custodian123')">Test Custodian Login</button>
        <button class="button" onclick="testDemoLogin('staff', 'staff123')">Test Staff Login</button>
        <div id="demoLoginResult"></div>
    </div>

    <div class="test-section">
        <h2>Logout Test</h2>
        <button class="button" onclick="testLogout()">Logout</button>
        <div id="logoutResult"></div>
    </div>

    <script>
        // Register form handler
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const userData = {
                username: document.getElementById('regUsername').value,
                password: document.getElementById('regPassword').value,
                full_name: document.getElementById('regFullName').value,
                email: document.getElementById('regEmail').value,
                role: document.getElementById('regRole').value,
                department: document.getElementById('regDepartment').value
            };

            try {
                const response = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(userData)
                });

                const result = await response.json();
                const resultDiv = document.getElementById('registerResult');

                if (response.ok) {
                    resultDiv.innerHTML = `<div class="success">✓ Registration successful: ${result.message}</div>
                                          <pre>${JSON.stringify(result.user, null, 2)}</pre>`;
                } else {
                    resultDiv.innerHTML = `<div class="error">✗ Registration failed: ${result.error}</div>`;
                }
            } catch (error) {
                document.getElementById('registerResult').innerHTML = `<div class="error">✗ Error: ${error.message}</div>`;
            }
        });

        // Login form handler
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const loginData = {
                username: document.getElementById('loginUsername').value,
                password: document.getElementById('loginPassword').value
            };

            try {
                const response = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(loginData)
                });

                const result = await response.json();
                const resultDiv = document.getElementById('loginResult');

                if (response.ok) {
                    resultDiv.innerHTML = `<div class="success">✓ Login successful: ${result.message}</div>
                                          <pre>${JSON.stringify(result.user, null, 2)}</pre>`;
                } else {
                    resultDiv.innerHTML = `<div class="error">✗ Login failed: ${result.message}</div>`;
                }
            } catch (error) {
                document.getElementById('loginResult').innerHTML = `<div class="error">✗ Error: ${error.message}</div>`;
            }
        });

        // Demo login function
        async function testDemoLogin(username, password) {
            const loginData = { username, password };

            try {
                const response = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(loginData)
                });

                const result = await response.json();
                const resultDiv = document.getElementById('demoLoginResult');

                if (response.ok) {
                    resultDiv.innerHTML = `<div class="success">✓ Demo login successful for ${username}: ${result.message}</div>
                                          <pre>${JSON.stringify(result.user, null, 2)}</pre>`;
                } else {
                    resultDiv.innerHTML = `<div class="error">✗ Demo login failed for ${username}: ${result.message}</div>`;
                }
            } catch (error) {
                document.getElementById('demoLoginResult').innerHTML = `<div class="error">✗ Error: ${error.message}</div>`;
            }
        }

        // Logout function
        async function testLogout() {
            try {
                const response = await fetch('api/auth.php?action=logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const result = await response.json();
                const resultDiv = document.getElementById('logoutResult');

                if (response.ok) {
                    resultDiv.innerHTML = `<div class="success">✓ Logout successful: ${result.message}</div>`;
                } else {
                    resultDiv.innerHTML = `<div class="error">✗ Logout failed: ${result.message}</div>`;
                }
            } catch (error) {
                document.getElementById('logoutResult').innerHTML = `<div class="error">✗ Error: ${error.message}</div>`;
            }
        }
    </script>
</body>
</html>