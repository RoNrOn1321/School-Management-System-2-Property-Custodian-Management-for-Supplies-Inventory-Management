// API Configuration
const API_BASE_URL = 'api/';

class API {
    static async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Authentication
    static async login(username, password) {
        return this.request('auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });
    }

    static async logout() {
        return this.request('auth.php?action=logout', {
            method: 'POST'
        });
    }

    // Dashboard
    static async getDashboardStats() {
        return this.request('dashboard.php?action=stats');
    }

    static async getRecentActivities() {
        return this.request('dashboard.php?action=recent_activities');
    }

    static async getAlerts() {
        return this.request('dashboard.php?action=alerts');
    }

    // Assets
    static async getAssets() {
        return this.request('assets.php');
    }

    static async getAsset(id) {
        return this.request(`assets.php?id=${id}`);
    }

    static async createAsset(assetData) {
        return this.request('assets.php', {
            method: 'POST',
            body: JSON.stringify(assetData)
        });
    }

    static async updateAsset(id, assetData) {
        return this.request(`assets.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(assetData)
        });
    }

    static async deleteAsset(id) {
        return this.request(`assets.php?id=${id}`, {
            method: 'DELETE'
        });
    }

    // Supplies
    static async getSupplies() {
        return this.request('supplies.php');
    }

    static async getSupply(id) {
        return this.request(`supplies.php?id=${id}`);
    }

    static async createSupply(supplyData) {
        return this.request('supplies.php', {
            method: 'POST',
            body: JSON.stringify(supplyData)
        });
    }

    static async updateSupply(id, supplyData) {
        return this.request(`supplies.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(supplyData)
        });
    }

    static async deleteSupply(id) {
        return this.request(`supplies.php?id=${id}`, {
            method: 'DELETE'
        });
    }

    static async getSupplyTransactions() {
        return this.request('supplies.php?action=transactions');
    }

    static async createTransaction(transactionData) {
        return this.request('supplies.php?action=transaction', {
            method: 'POST',
            body: JSON.stringify(transactionData)
        });
    }

    // Property Issuance
    static async getPropertyIssuances() {
        return this.request('property_issuance.php');
    }

    static async getPropertyIssuance(id) {
        return this.request(`property_issuance.php?id=${id}`);
    }

    static async createPropertyIssuance(issuanceData) {
        return this.request('property_issuance.php', {
            method: 'POST',
            body: JSON.stringify(issuanceData)
        });
    }

    static async updatePropertyIssuance(id, issuanceData) {
        return this.request(`property_issuance.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(issuanceData)
        });
    }

    static async deletePropertyIssuance(id) {
        return this.request(`property_issuance.php?id=${id}`, {
            method: 'DELETE'
        });
    }

    static async getAvailableAssets() {
        return this.request('assets.php?status=available');
    }

    // Reports
    static async getOverviewReport() {
        return this.request('reports.php?action=overview');
    }

    static async getAssetsReport() {
        return this.request('reports.php?action=assets');
    }

    static async getMaintenanceReport() {
        return this.request('reports.php?action=maintenance');
    }

    static async getProcurementReport() {
        return this.request('reports.php?action=procurement');
    }

    static async getAuditReport() {
        return this.request('reports.php?action=audit');
    }

    static async getFinancialReport() {
        return this.request('reports.php?action=financial');
    }
}

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function formatCurrency(amount) {
    if (!amount) return '₱0.00';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount);
}

// Legacy API call function for backward compatibility
async function apiCall(url, method = 'GET', data = null) {
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        config.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, config);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || result.message || 'Request failed');
        }

        return result;
    } catch (error) {
        console.error('API Call Error:', error);
        throw error;
    }
}

// Alert function for user notifications
function showAlert(message, type = 'info') {
    showNotification(message, type);
}

// Modal utility functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Enhanced notification functions for procurement.js compatibility
function showSuccess(message) {
    showNotification(message, 'success');
}

function showError(message) {
    showNotification(message, 'error');
}

function showLoading() {
    // Create or show loading indicator
    let loadingIndicator = document.getElementById('loadingIndicator');
    if (!loadingIndicator) {
        loadingIndicator = document.createElement('div');
        loadingIndicator.id = 'loadingIndicator';
        loadingIndicator.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50';
        loadingIndicator.innerHTML = `
            <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="text-gray-700">Loading...</span>
            </div>
        `;
        document.body.appendChild(loadingIndicator);
    }
    loadingIndicator.classList.remove('hidden');
}

function hideLoading() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.classList.add('hidden');
    }
}