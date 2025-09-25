<!-- Login Screen -->
<div id="loginScreen" class="min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <img src="logos/logo.jpg" alt="School Logo" class="mx-auto h-16 w-16 rounded-full mb-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Property Custodian Management</h1>
            <p class="text-gray-600">School Management System</p>
        </div>

        <form id="loginForm" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input type="text" id="username" name="username" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>
        </form>

        <div class="mt-6 p-4 bg-gray-50 rounded-md text-sm">
            <p class="font-semibold text-gray-700 mb-2">Demo Accounts:</p>
            <p><strong>Admin:</strong> admin / admin123</p>
            <p><strong>Custodian:</strong> custodian / custodian123</p>
            <p><strong>Staff:</strong> staff / staff123</p>
        </div>
    </div>
</div>