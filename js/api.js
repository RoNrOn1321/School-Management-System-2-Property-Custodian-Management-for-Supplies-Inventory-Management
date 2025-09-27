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