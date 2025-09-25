// Dashboard functionality
class Dashboard {
    static async loadData() {
        try {
            // Load all dashboard data in parallel
            await Promise.all([
                this.loadStats(),
                this.loadRecentActivities(),
                this.loadAlerts()
            ]);
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            showNotification('Failed to load dashboard data', 'error');
        }
    }

    static async loadStats() {
        try {
            const stats = await API.getDashboardStats();

            // Update stat cards
            document.getElementById('totalAssets').textContent = stats.totalAssets || 0;
            document.getElementById('availableItems').textContent = stats.availableItems || 0;
            document.getElementById('maintenanceItems').textContent = stats.maintenanceItems || 0;
            document.getElementById('damagedItems').textContent = stats.damagedItems || 0;

        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    static async loadRecentActivities() {
        try {
            const activities = await API.getRecentActivities();
            const container = document.getElementById('recentActivities');

            if (activities.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">No recent activities</p>';
                return;
            }

            container.innerHTML = activities.map(activity => `
                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                    <div class="bg-blue-100 rounded-full p-2 mr-3">
                        <i class="fas fa-${this.getActivityIcon(activity.action)} text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">${this.formatActivityMessage(activity)}</p>
                        <p class="text-xs text-gray-500">${this.formatTimeAgo(activity.created_at)}</p>
                    </div>
                </div>
            `).join('');

        } catch (error) {
            console.error('Error loading activities:', error);
            const container = document.getElementById('recentActivities');
            container.innerHTML = '<p class="text-red-500 text-sm">Failed to load activities</p>';
        }
    }

    static async loadAlerts() {
        try {
            const alerts = await API.getAlerts();
            const container = document.getElementById('alertsList');

            if (alerts.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">No alerts at this time</p>';
                return;
            }

            container.innerHTML = alerts.map(alert => `
                <div class="flex items-start p-3 border-l-4 ${
                    alert.priority === 'high' ? 'border-red-500 bg-red-50' :
                    alert.priority === 'medium' ? 'border-yellow-500 bg-yellow-50' :
                    'border-blue-500 bg-blue-50'
                } rounded">
                    <div class="mr-3 pt-1">
                        <i class="fas fa-${this.getAlertIcon(alert.type)} ${
                            alert.priority === 'high' ? 'text-red-600' :
                            alert.priority === 'medium' ? 'text-yellow-600' :
                            'text-blue-600'
                        }"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">${alert.title}</p>
                        <p class="text-sm text-gray-700">${alert.message}</p>
                    </div>
                </div>
            `).join('');

        } catch (error) {
            console.error('Error loading alerts:', error);
            const container = document.getElementById('alertsList');
            container.innerHTML = '<p class="text-red-500 text-sm">Failed to load alerts</p>';
        }
    }

    static getActivityIcon(action) {
        const icons = {
            'create': 'plus',
            'update': 'edit',
            'delete': 'trash',
            'login': 'sign-in-alt',
            'assign': 'handshake',
            'return': 'undo'
        };
        return icons[action] || 'info-circle';
    }

    static formatActivityMessage(activity) {
        const actionMap = {
            'create': 'created',
            'update': 'updated',
            'delete': 'deleted',
            'login': 'logged in',
            'assign': 'assigned',
            'return': 'returned'
        };

        const action = actionMap[activity.action] || activity.action;
        const table = activity.table_name ? activity.table_name.replace('_', ' ') : 'item';

        return `${activity.user} ${action} ${table}`;
    }

    static getAlertIcon(type) {
        const icons = {
            'low_stock': 'exclamation-triangle',
            'overdue_maintenance': 'wrench',
            'expired_supplies': 'clock',
            'audit_required': 'clipboard-check'
        };
        return icons[type] || 'bell';
    }

    static formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        const minutes = Math.floor(diff / (1000 * 60));
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) {
            return `${days} day${days > 1 ? 's' : ''} ago`;
        } else if (hours > 0) {
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else if (minutes > 0) {
            return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else {
            return 'Just now';
        }
    }

    static refreshData() {
        this.loadData();
    }
}