class DamagedItemsManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.uploadedFiles = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadDamagedItems();
        this.loadStats();
        this.setDefaultDate();
    }

    bindEvents() {
        // Report Damage button
        const reportBtn = document.querySelector('h1 + div button.bg-red-600');
        if (reportBtn) {
            reportBtn.addEventListener('click', () => this.toggleReportForm());
        }

        // Form submission
        const form = document.getElementById('damage-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitDamageReport();
            });
        }

        // Cancel button
        const cancelBtn = document.getElementById('cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.resetForm());
        }

        // Asset code input for auto-search
        const assetCodeInput = document.getElementById('asset_code');
        if (assetCodeInput) {
            assetCodeInput.addEventListener('blur', () => this.searchAsset());
            assetCodeInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.searchAsset();
                }
            });
        }

        // File upload handling
        const fileInput = document.getElementById('damage_photos');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileUpload(e));
        }
    }

    setDefaultDate() {
        const dateInput = document.querySelector('input[type="date"]');
        if (dateInput && !dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    }

    toggleReportForm() {
        const form = document.getElementById('damage-report-form');
        if (form) {
            form.classList.toggle('hidden');
        }
    }

    async searchAsset() {
        const assetCodeInput = document.getElementById('asset_code');
        const assetNameInput = document.getElementById('asset_name');
        const locationInput = document.getElementById('current_location');

        const assetCode = assetCodeInput?.value.trim();
        if (!assetCode) return;

        try {
            const response = await fetch(`api/damaged_items.php?action=search_asset&asset_code=${encodeURIComponent(assetCode)}`);
            const result = await response.json();

            if (result.success) {
                const asset = result.data;
                if (assetNameInput) assetNameInput.value = asset.name;
                if (locationInput) locationInput.value = asset.location || '';

                // Show success feedback
                this.showNotification('Asset found and details loaded', 'success');
            } else {
                // Clear fields and show error
                if (assetNameInput) assetNameInput.value = '';
                if (locationInput) locationInput.value = '';
                this.showNotification('Asset not found', 'error');
            }
        } catch (error) {
            console.error('Error searching asset:', error);
            this.showNotification('Error searching for asset', 'error');
        }
    }

    async submitDamageReport() {
        const form = document.getElementById('damage-form');
        const formData = new FormData(form);

        // Convert FormData to regular object for validation and API submission
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key !== 'damage_photos') { // Handle files separately
                data[key] = value.toString().trim();
            }
        }

        // Validate required fields
        const requiredFields = [
            { field: 'asset_code', name: 'Asset Code' },
            { field: 'damage_type', name: 'Damage Type' },
            { field: 'severity_level', name: 'Severity Level' },
            { field: 'damage_date', name: 'Damage Date' },
            { field: 'reported_by', name: 'Reporter Name' }
        ];

        let isValid = true;

        // Clear previous errors
        form.querySelectorAll('.field-error').forEach(error => error.remove());
        form.querySelectorAll('.border-red-500').forEach(field => field.classList.remove('border-red-500'));

        // Validate each required field
        requiredFields.forEach(({ field, name }) => {
            const input = document.getElementById(field);
            if (!data[field] || data[field] === '') {
                this.showFieldError(input, `${name} is required`);
                isValid = false;
            }
        });

        if (!isValid) {
            this.showNotification('Please fill in all required fields', 'error');
            return;
        }

        try {
            // Handle file uploads first if any
            if (this.uploadedFiles && this.uploadedFiles.length > 0) {
                const uploadedFilenames = await this.uploadFiles();
                if (uploadedFilenames) {
                    data.damage_photos = JSON.stringify(uploadedFilenames);
                }
            }

            const response = await fetch('api/damaged_items.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            let result;
            try {
                result = await response.json();
            } catch (parseError) {
                console.error('Failed to parse response:', parseError);
                const responseText = await response.text();
                console.error('Response text:', responseText);
                throw new Error('Invalid response from server');
            }

            if (response.ok && result.success) {
                this.showNotification('Damage report submitted successfully', 'success');
                this.resetForm();
                this.loadDamagedItems();
                this.loadStats();
            } else {
                console.error('API Error:', result);
                this.showNotification(result.message || `Failed to submit damage report (Status: ${response.status})`, 'error');
            }
        } catch (error) {
            console.error('Error submitting damage report:', error);
            this.showNotification('Error submitting damage report', 'error');
        }
    }

    async uploadFiles() {
        if (!this.uploadedFiles || this.uploadedFiles.length === 0) {
            return null;
        }

        try {
            const uploadPromises = this.uploadedFiles.map(async (file, index) => {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('type', 'damage_photo');

                const response = await fetch('api/upload.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    return result.filename;
                } else {
                    throw new Error(`Failed to upload ${file.name}: ${result.message}`);
                }
            });

            const uploadedFilenames = await Promise.all(uploadPromises);
            return uploadedFilenames;

        } catch (error) {
            console.error('Error uploading files:', error);
            this.showNotification('Error uploading images', 'error');
            return null;
        }
    }

    async loadDamagedItems() {
        try {
            const response = await fetch(`api/damaged_items.php?action=list&page=${this.currentPage}&limit=${this.itemsPerPage}`);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                this.renderDamagedItemsTable(result.data);
                this.renderPagination(result.pagination);
            } else {
                console.error('API Error:', result);
                this.showNotification('Failed to load damaged items: ' + (result.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error loading damaged items:', error);
            this.showNotification('Error loading damaged items: ' + error.message, 'error');
        }
    }

    renderDamagedItemsTable(items) {
        const tbody = document.querySelector('tbody');
        if (!tbody) return;

        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="px-3 sm:px-6 py-4 text-center text-gray-500">No damaged items reported</td></tr>';
            return;
        }

        tbody.innerHTML = items.map(item => `
            <tr class="hover:bg-gray-50">
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.asset_code}</td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell">${item.asset_name || 'N/A'}</td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="capitalize">${item.damage_type.replace('_', ' ')}</span>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        ${this.getSeverityClass(item.severity_level)}">
                        ${item.severity_level.charAt(0).toUpperCase() + item.severity_level.slice(1)}
                    </span>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden lg:table-cell">${this.formatDate(item.damage_date)}</td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden md:table-cell">${item.reported_by}</td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden lg:table-cell">₱${parseFloat(item.estimated_repair_cost || 0).toFixed(2)}</td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        ${this.getStatusClass(item.status)}">
                        ${item.status.charAt(0).toUpperCase() + item.status.slice(1).replace('_', ' ')}
                    </span>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex flex-col sm:flex-row space-y-1 sm:space-y-0 sm:space-x-2">
                        <button onclick="damagedItemsManager.viewDetails(${item.id})"
                                class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm">View</button>
                        <button onclick="damagedItemsManager.updateStatus(${item.id})"
                                class="text-green-600 hover:text-green-900 text-xs sm:text-sm">Update</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    getSeverityClass(severity) {
        const classes = {
            'minor': 'bg-green-100 text-green-800',
            'moderate': 'bg-yellow-100 text-yellow-800',
            'major': 'bg-red-100 text-red-800',
            'total': 'bg-gray-100 text-gray-800'
        };
        return classes[severity] || 'bg-gray-100 text-gray-800';
    }

    getStatusClass(status) {
        const classes = {
            'reported': 'bg-yellow-100 text-yellow-800',
            'under_repair': 'bg-blue-100 text-blue-800',
            'repaired': 'bg-green-100 text-green-800',
            'write_off': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    async loadStats() {
        try {
            const response = await fetch('api/damaged_items.php?action=stats');

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                this.updateStatsDisplay(result.data);
            } else {
                console.error('Failed to load stats:', result);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    updateStatsDisplay(stats) {
        const statCards = document.querySelectorAll('.bg-white.rounded-lg.shadow-md.p-6');

        statCards.forEach((card, index) => {
            const valueElement = card.querySelector('dd');
            if (!valueElement) return;

            switch (index) {
                case 0: // Total Damaged
                    valueElement.textContent = stats.total_damaged || '0';
                    break;
                case 1: // Under Repair
                    valueElement.textContent = stats.under_repair || '0';
                    break;
                case 2: // Write-offs
                    valueElement.textContent = stats.write_offs || '0';
                    break;
                case 3: // Total Repair Cost
                    valueElement.textContent = `₱${parseFloat(stats.total_repair_cost || 0).toFixed(2)}`;
                    break;
            }
        });
    }

    resetForm() {
        const form = document.getElementById('damage-form');

        // Reset all form fields
        form.reset();

        // Clear any error styling and messages
        form.querySelectorAll('.field-error').forEach(error => error.remove());
        form.querySelectorAll('.border-red-500').forEach(field => field.classList.remove('border-red-500'));

        // Clear uploaded files
        this.uploadedFiles = [];
        const preview = document.querySelector('.mt-2');
        if (preview) preview.remove();

        // Reset date to today
        this.setDefaultDate();
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        field.classList.add('border-red-500');

        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-sm mt-1 field-error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        field.classList.remove('border-red-500');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    renderPagination(pagination) {
        const tableContainer = document.querySelector('.bg-white.rounded-lg.shadow-md.overflow-hidden');

        // Remove existing pagination
        const existingPagination = document.querySelector('.pagination-container');
        if (existingPagination) {
            existingPagination.remove();
        }

        if (pagination.total_pages <= 1) return;

        const paginationHtml = `
            <div class="pagination-container bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button ${pagination.page <= 1 ? 'disabled' : ''}
                            onclick="damagedItemsManager.changePage(${pagination.page - 1})"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 ${pagination.page <= 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                        Previous
                    </button>
                    <button ${pagination.page >= pagination.total_pages ? 'disabled' : ''}
                            onclick="damagedItemsManager.changePage(${pagination.page + 1})"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 ${pagination.page >= pagination.total_pages ? 'opacity-50 cursor-not-allowed' : ''}">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">${((pagination.page - 1) * pagination.limit) + 1}</span>
                            to <span class="font-medium">${Math.min(pagination.page * pagination.limit, pagination.total)}</span>
                            of <span class="font-medium">${pagination.total}</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button ${pagination.page <= 1 ? 'disabled' : ''}
                                    onclick="damagedItemsManager.changePage(${pagination.page - 1})"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 ${pagination.page <= 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            ${this.generatePageNumbers(pagination)}
                            <button ${pagination.page >= pagination.total_pages ? 'disabled' : ''}
                                    onclick="damagedItemsManager.changePage(${pagination.page + 1})"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 ${pagination.page >= pagination.total_pages ? 'opacity-50 cursor-not-allowed' : ''}">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        `;

        tableContainer.insertAdjacentHTML('afterend', paginationHtml);
    }

    generatePageNumbers(pagination) {
        let pages = '';
        const maxVisiblePages = 5;
        let startPage = Math.max(1, pagination.page - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(pagination.total_pages, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === pagination.page;
            pages += `
                <button onclick="damagedItemsManager.changePage(${i})"
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                            isActive
                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                        }">
                    ${i}
                </button>
            `;
        }
        return pages;
    }

    changePage(page) {
        this.currentPage = page;
        this.loadDamagedItems();
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }

    async viewDetails(id) {
        try {
            const response = await fetch(`api/damaged_items.php?action=details&id=${id}`);
            const result = await response.json();

            if (result.success) {
                this.showDetailsModal(result.data);
            } else {
                this.showNotification('Failed to load damage details', 'error');
            }
        } catch (error) {
            console.error('Error loading details:', error);
            this.showNotification('Error loading damage details', 'error');
        }
    }

    showDetailsModal(item) {
        // Create a modal to show damage details
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
        modal.innerHTML = `
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Damage Report Details</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><strong>Asset Code:</strong> ${item.asset_code}</div>
                        <div><strong>Asset Name:</strong> ${item.asset_name || 'N/A'}</div>
                        <div><strong>Damage Type:</strong> ${item.damage_type.replace('_', ' ')}</div>
                        <div><strong>Severity:</strong> ${item.severity_level}</div>
                        <div><strong>Date:</strong> ${this.formatDate(item.damage_date)}</div>
                        <div><strong>Reported By:</strong> ${item.reported_by}</div>
                        <div><strong>Location:</strong> ${item.current_location || 'N/A'}</div>
                        <div><strong>Repair Cost:</strong> ₱${parseFloat(item.estimated_repair_cost || 0).toFixed(2)}</div>
                        <div><strong>Status:</strong> ${item.status.replace('_', ' ')}</div>
                        <div><strong>Created:</strong> ${this.formatDate(item.created_at)}</div>
                    </div>
                    ${item.damage_description ? `
                        <div class="mt-4">
                            <strong>Description:</strong>
                            <p class="mt-2 text-gray-700">${item.damage_description}</p>
                        </div>
                    ` : ''}
                    ${item.damage_photos ? `
                        <div class="mt-4">
                            <strong>Photos:</strong>
                            <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                                ${JSON.parse(item.damage_photos).map(photo => `
                                    <img src="uploads/damage_photos/${photo}" class="w-full h-24 object-cover rounded" alt="Damage photo">
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    async updateStatus(id) {
        const newStatus = prompt('Enter new status (reported, under_repair, repaired, write_off):');
        if (!newStatus) return;

        try {
            const response = await fetch('api/damaged_items.php?action=update', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id, status: newStatus })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Status updated successfully', 'success');
                this.loadDamagedItems();
                this.loadStats();
            } else {
                this.showNotification(result.message || 'Failed to update status', 'error');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            this.showNotification('Error updating status', 'error');
        }
    }

    handleFileUpload(event) {
        const files = event.target.files;
        console.log('Files selected:', files.length);

        if (files.length > 0) {
            this.uploadedFiles = Array.from(files);
            this.showFilePreview(files);
        }
    }

    showFilePreview(files) {
        const previewContainer = document.querySelector('.border-dashed');

        // Create preview area
        const previewHtml = `
            <div class="mt-2">
                <div class="text-sm text-gray-600 mb-2">Selected files:</div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    ${Array.from(files).map((file, index) => `
                        <div class="relative">
                            <img src="${URL.createObjectURL(file)}" class="w-full h-20 object-cover rounded border" alt="Preview">
                            <button onclick="damagedItemsManager.removeFile(${index})"
                                    class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                ×
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        const existingPreview = previewContainer.querySelector('.mt-2');
        if (existingPreview) {
            existingPreview.remove();
        }

        previewContainer.insertAdjacentHTML('afterend', previewHtml);
    }

    removeFile(index) {
        this.uploadedFiles.splice(index, 1);
        const fileInput = document.getElementById('damage_photos');

        // Update file input
        const dt = new DataTransfer();
        this.uploadedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;

        if (this.uploadedFiles.length > 0) {
            this.showFilePreview(this.uploadedFiles);
        } else {
            const preview = document.querySelector('.mt-2');
            if (preview) preview.remove();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.damagedItemsManager = new DamagedItemsManager();
});