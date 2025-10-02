class PropertyAuditManager {
    constructor() {
        this.auditData = [];
        this.currentAudit = null;
        this.activeAudits = [];
        this.qrScanner = null;
        this.scanningActive = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadAuditStats();
        this.loadAuditHistory();
        this.loadActiveAudits();
    }

    bindEvents() {
        // Create audit button
        document.getElementById('createAuditBtn')?.addEventListener('click', () => {
            this.showCreateAuditModal();
        });

        // Form submissions
        document.getElementById('createAuditForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.createAudit();
        });

        document.getElementById('editAuditForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.updateAudit();
        });

        // Quick actions
        document.getElementById('scanQRBtn')?.addEventListener('click', () => {
            this.showQRScannerModal();
        });

        document.getElementById('markFoundBtn')?.addEventListener('click', () => {
            this.showMarkFoundModal();
        });

        document.getElementById('reportDiscrepancyBtn')?.addEventListener('click', () => {
            this.showDiscrepancyModal();
        });

        // QR Scanner events
        document.getElementById('startScanBtn')?.addEventListener('click', () => {
            this.startQRScanner();
        });

        document.getElementById('stopScanBtn')?.addEventListener('click', () => {
            this.stopQRScanner();
        });

        // Form submissions for quick actions
        document.getElementById('markFoundForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitMarkFound();
        });

        document.getElementById('discrepancyForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitDiscrepancy();
        });

        // Audit scope checkboxes
        document.querySelectorAll('input[name="audit_scope"]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.handleScopeChange(e);
            });
        });

        // Mobile menu toggle
        this.setupMobileMenu();
    }

    setupMobileMenu() {
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileMenuOverlay');

        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                if (overlay) {
                    overlay.classList.toggle('active');
                }
            });
        }

        // Close menu when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar?.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
    }

    async loadAuditStats() {
        try {
            const response = await fetch('api/property_audit.php?action=stats');
            const result = await response.json();

            if (result.status === 'success') {
                this.updateStatsDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading audit stats:', error);
            this.showNotification('Error loading audit statistics', 'error');
        }
    }

    updateStatsDisplay(stats) {
        document.getElementById('activeAuditsCount').textContent = stats.active_audits || 0;
        document.getElementById('completedAuditsCount').textContent = stats.completed_audits || 0;
        document.getElementById('pendingReviewCount').textContent = stats.pending_review || 0;
        document.getElementById('discrepanciesCount').textContent = stats.total_discrepancies || 0;
    }

    async loadAuditHistory() {
        try {
            const response = await fetch('api/property_audit.php?action=list');
            const result = await response.json();

            if (result.status === 'success') {
                this.auditData = result.data;
                this.renderAuditTable();
            }
        } catch (error) {
            console.error('Error loading audit history:', error);
            this.showNotification('Error loading audit history', 'error');
        }
    }

    renderAuditTable() {
        const tbody = document.getElementById('auditTableBody');
        if (!tbody) return;

        if (this.auditData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-500 py-4">No audits found</td></tr>';
            return;
        }

        tbody.innerHTML = this.auditData.map(audit => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${audit.audit_code}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="capitalize">${audit.audit_type.replace('_', ' ')}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${audit.department}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${audit.auditor_name || 'Unassigned'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${this.formatDate(audit.start_date)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${this.getStatusBadge(audit.status)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex items-center">
                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: ${this.calculateProgress(audit)}%"></div>
                        </div>
                        <span class="text-xs">${this.calculateProgress(audit)}%</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <button onclick="propertyAuditManager.viewAudit(${audit.id})"
                                class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="propertyAuditManager.editAudit(${audit.id})"
                                class="text-green-600 hover:text-green-900">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="propertyAuditManager.deleteAudit(${audit.id})"
                                class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    getStatusBadge(status) {
        const statusClasses = {
            'planned': 'bg-yellow-100 text-yellow-800',
            'in_progress': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800'
        };

        const statusText = status.replace('_', ' ').toUpperCase();
        const classes = statusClasses[status] || 'bg-gray-100 text-gray-800';

        return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${classes}">
                    ${statusText}
                </span>`;
    }

    calculateProgress(audit) {
        if (audit.status === 'completed') return 100;
        if (audit.status === 'cancelled') return 0;
        if (audit.status === 'in_progress') return 50;
        return 0;
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    showCreateAuditModal() {
        document.getElementById('createAuditModal')?.classList.remove('hidden');
    }

    hideCreateAuditModal() {
        document.getElementById('createAuditModal')?.classList.add('hidden');
        document.getElementById('createAuditForm')?.reset();
    }

    async createAudit() {
        const form = document.getElementById('createAuditForm');
        const formData = new FormData(form);

        const auditData = {
            audit_type: formData.get('audit_type'),
            start_date: formData.get('audit_date'),
            department: formData.get('department') || 'General',
            summary: formData.get('objectives')
        };

        try {
            const response = await fetch('api/property_audit.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(auditData)
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.showNotification('Audit created successfully', 'success');
                this.hideCreateAuditModal();
                this.loadAuditHistory();
                this.loadAuditStats();
            } else {
                this.showNotification(result.message || 'Error creating audit', 'error');
            }
        } catch (error) {
            console.error('Error creating audit:', error);
            this.showNotification('Error creating audit', 'error');
        }
    }

    async viewAudit(auditId) {
        try {
            const response = await fetch(`api/property_audit.php?action=details&id=${auditId}`);
            const result = await response.json();

            if (result.status === 'success') {
                this.showAuditDetails(result.data);
            }
        } catch (error) {
            console.error('Error loading audit details:', error);
            this.showNotification('Error loading audit details', 'error');
        }
    }

    showAuditDetails(audit) {
        const modal = document.getElementById('auditDetailsModal');
        if (!modal) return;

        document.getElementById('auditDetailsContent').innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Audit Code</label>
                        <p class="text-lg font-semibold">${audit.audit_code}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        ${this.getStatusBadge(audit.status)}
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <p class="capitalize">${audit.audit_type.replace('_', ' ')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Auditor</label>
                        <p>${audit.auditor_name || 'Unassigned'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <p>${this.formatDate(audit.start_date)}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <p>${this.formatDate(audit.end_date) || 'Ongoing'}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Summary</label>
                    <p class="mt-1 text-gray-600">${audit.summary || 'No summary provided'}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Assets Audited</label>
                        <p class="text-lg font-semibold text-blue-600">${audit.total_assets_audited || 0}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Discrepancies Found</label>
                        <p class="text-lg font-semibold text-red-600">${audit.discrepancies_found || 0}</p>
                    </div>
                </div>
            </div>
        `;

        modal.classList.remove('hidden');
    }

    hideAuditDetails() {
        document.getElementById('auditDetailsModal')?.classList.add('hidden');
    }

    async editAudit(auditId) {
        try {
            const response = await fetch(`api/property_audit.php?action=details&id=${auditId}`);
            const result = await response.json();

            if (result.status === 'success') {
                this.populateEditForm(result.data);
                this.showEditAuditModal();
            } else {
                this.showNotification(result.message || 'Error loading audit details', 'error');
            }
        } catch (error) {
            console.error('Error loading audit for edit:', error);
            this.showNotification('Error loading audit details', 'error');
        }
    }

    populateEditForm(audit) {
        document.getElementById('editAuditId').value = audit.id;
        document.getElementById('editAuditCode').value = audit.audit_code;
        document.getElementById('editAuditType').value = audit.audit_type;
        document.getElementById('editStartDate').value = audit.start_date;
        document.getElementById('editEndDate').value = audit.end_date || '';
        document.getElementById('editDepartment').value = audit.department || '';
        document.getElementById('editStatus').value = audit.status;
        document.getElementById('editAssetsAudited').value = audit.total_assets_audited || '';
        document.getElementById('editDiscrepancies').value = audit.discrepancies_found || '';
        document.getElementById('editSummary').value = audit.summary || '';
    }

    showEditAuditModal() {
        document.getElementById('editAuditModal')?.classList.remove('hidden');
    }

    hideEditAuditModal() {
        document.getElementById('editAuditModal')?.classList.add('hidden');
        document.getElementById('editAuditForm')?.reset();
    }

    async updateAudit() {
        const form = document.getElementById('editAuditForm');
        const formData = new FormData(form);

        const auditData = {
            id: formData.get('id'),
            audit_type: formData.get('audit_type'),
            start_date: formData.get('start_date'),
            end_date: formData.get('end_date') || null,
            department: formData.get('department'),
            status: formData.get('status'),
            total_assets_audited: parseInt(formData.get('total_assets_audited')) || null,
            discrepancies_found: parseInt(formData.get('discrepancies_found')) || null,
            summary: formData.get('summary')
        };

        try {
            const response = await fetch('api/property_audit.php?action=update', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(auditData)
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.showNotification('Audit updated successfully', 'success');
                this.hideEditAuditModal();
                this.loadAuditHistory();
                this.loadAuditStats();
            } else {
                this.showNotification(result.message || 'Error updating audit', 'error');
            }
        } catch (error) {
            console.error('Error updating audit:', error);
            this.showNotification('Error updating audit', 'error');
        }
    }

    async deleteAudit(auditId) {
        if (!confirm('Are you sure you want to delete this audit? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(`api/property_audit.php?id=${auditId}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.showNotification('Audit deleted successfully', 'success');
                this.loadAuditHistory();
                this.loadAuditStats();
            } else {
                this.showNotification(result.message || 'Error deleting audit', 'error');
            }
        } catch (error) {
            console.error('Error deleting audit:', error);
            this.showNotification('Error deleting audit', 'error');
        }
    }

    handleScopeChange(event) {
        const checkbox = event.target;
        const scopeOptions = document.getElementById('scopeOptions');

        if (checkbox.value === 'custom' && checkbox.checked) {
            scopeOptions?.classList.remove('hidden');
        } else if (checkbox.value === 'custom' && !checkbox.checked) {
            scopeOptions?.classList.add('hidden');
        }
    }

    async loadActiveAudits() {
        try {
            const response = await fetch('api/property_audit.php?action=list');
            const result = await response.json();

            if (result.status === 'success') {
                this.activeAudits = result.data.filter(audit =>
                    audit.status === 'planned' || audit.status === 'in_progress'
                );
                this.populateAuditSelects();
            }
        } catch (error) {
            console.error('Error loading active audits:', error);
        }
    }

    populateAuditSelects() {
        const foundSelect = document.getElementById('foundAuditSelect');
        const discrepancySelect = document.getElementById('discrepancyAuditSelect');

        if (foundSelect) {
            foundSelect.innerHTML = '<option value="">Select an audit</option>';
            this.activeAudits.forEach(audit => {
                foundSelect.innerHTML += `<option value="${audit.id}">${audit.audit_code} - ${audit.department}</option>`;
            });
        }

        if (discrepancySelect) {
            discrepancySelect.innerHTML = '<option value="">Select an audit</option>';
            this.activeAudits.forEach(audit => {
                discrepancySelect.innerHTML += `<option value="${audit.id}">${audit.audit_code} - ${audit.department}</option>`;
            });
        }
    }

    // QR Scanner Functions
    showQRScannerModal() {
        document.getElementById('qrScannerModal')?.classList.remove('hidden');
    }

    hideQRScannerModal() {
        this.stopQRScanner();
        document.getElementById('qrScannerModal')?.classList.add('hidden');
        document.getElementById('manualAssetCode').value = '';
        document.getElementById('assetInfoDisplay')?.classList.add('hidden');
    }

    async startQRScanner() {
        try {
            const video = document.getElementById('qrVideo');
            const startBtn = document.getElementById('startScanBtn');
            const stopBtn = document.getElementById('stopScanBtn');
            const overlay = document.getElementById('scannerOverlay');

            // Check for camera support
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.showNotification('Camera not supported on this device', 'error');
                return;
            }

            // Request camera access
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' } // Use back camera if available
            });

            video.srcObject = stream;
            video.style.display = 'block';
            video.play();

            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');
            overlay.classList.remove('hidden');

            this.scanningActive = true;
            this.scanForQRCode(video);

            this.showNotification('Camera started. Point at QR code to scan', 'info');
        } catch (error) {
            console.error('Error starting camera:', error);
            this.showNotification('Unable to access camera. Please check permissions.', 'error');
        }
    }

    stopQRScanner() {
        const video = document.getElementById('qrVideo');
        const startBtn = document.getElementById('startScanBtn');
        const stopBtn = document.getElementById('stopScanBtn');
        const overlay = document.getElementById('scannerOverlay');

        if (video.srcObject) {
            const tracks = video.srcObject.getTracks();
            tracks.forEach(track => track.stop());
            video.srcObject = null;
        }

        video.style.display = 'none';
        startBtn.classList.remove('hidden');
        stopBtn.classList.add('hidden');
        overlay.classList.add('hidden');

        this.scanningActive = false;
    }

    scanForQRCode(video) {
        if (!this.scanningActive) return;

        const canvas = document.getElementById('qrCanvas');
        const context = canvas.getContext('2d');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        try {
            // Simple QR detection simulation - in real implementation, use a QR library like jsQR
            // For now, we'll simulate QR detection and use manual input
            setTimeout(() => {
                if (this.scanningActive) {
                    this.scanForQRCode(video);
                }
            }, 100);
        } catch (error) {
            console.error('QR scanning error:', error);
        }
    }

    async processAssetCode() {
        const assetCode = document.getElementById('manualAssetCode').value.trim();

        if (!assetCode) {
            this.showNotification('Please enter an asset code', 'warning');
            return;
        }

        try {
            // Search for asset in the system
            const response = await fetch(`api/assets.php?search=${encodeURIComponent(assetCode)}`);
            const result = await response.json();

            if (result.status === 'success' && result.data.length > 0) {
                const asset = result.data[0];
                this.displayAssetInfo(asset);
            } else {
                this.showNotification('Asset not found in system', 'warning');
                this.displayAssetInfo({ asset_code: assetCode, description: 'Unknown Asset', status: 'Not Found' });
            }
        } catch (error) {
            console.error('Error searching asset:', error);
            this.showNotification('Error searching for asset', 'error');
        }
    }

    displayAssetInfo(asset) {
        const display = document.getElementById('assetInfoDisplay');
        const details = document.getElementById('assetDetails');

        details.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Asset Code</label>
                    <p class="font-semibold">${asset.asset_code || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Description</label>
                    <p>${asset.description || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Category</label>
                    <p>${asset.category || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Current Location</label>
                    <p>${asset.current_location || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Status</label>
                    <p class="font-medium ${asset.status === 'active' ? 'text-green-600' : 'text-red-600'}">${asset.status || 'Unknown'}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Custodian</label>
                    <p>${asset.custodian_name || 'Unassigned'}</p>
                </div>
            </div>
        `;

        display.classList.remove('hidden');
    }

    // Mark Asset Found Functions
    showMarkFoundModal() {
        this.loadActiveAudits(); // Refresh active audits
        document.getElementById('markFoundModal')?.classList.remove('hidden');
    }

    hideMarkFoundModal() {
        document.getElementById('markFoundModal')?.classList.add('hidden');
        document.getElementById('markFoundForm')?.reset();
    }

    async submitMarkFound() {
        const form = document.getElementById('markFoundForm');
        const formData = new FormData(form);

        const foundData = {
            audit_id: formData.get('audit_id'),
            asset_code: formData.get('asset_code'),
            current_location: formData.get('current_location'),
            condition: formData.get('condition'),
            notes: formData.get('notes'),
            found_date: new Date().toISOString().split('T')[0],
            found_by: 'Current User' // In real implementation, get from session
        };

        try {
            // For now, we'll add this as a positive finding
            const response = await fetch('api/property_audit.php?action=add_finding', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    audit_id: foundData.audit_id,
                    finding_type: 'location_verification',
                    description: `Asset ${foundData.asset_code} found in ${foundData.current_location}. Condition: ${foundData.condition}. Notes: ${foundData.notes}`,
                    severity: 'low',
                    corrective_action: 'Asset verified and location updated',
                    status: 'resolved'
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.showNotification('Asset marked as found successfully', 'success');
                this.hideMarkFoundModal();
                this.loadAuditStats();
            } else {
                this.showNotification(result.message || 'Error marking asset as found', 'error');
            }
        } catch (error) {
            console.error('Error marking asset as found:', error);
            this.showNotification('Error marking asset as found', 'error');
        }
    }

    // Report Discrepancy Functions
    showDiscrepancyModal() {
        this.loadActiveAudits(); // Refresh active audits
        document.getElementById('discrepancyModal')?.classList.remove('hidden');
    }

    hideDiscrepancyModal() {
        document.getElementById('discrepancyModal')?.classList.add('hidden');
        document.getElementById('discrepancyForm')?.reset();
    }

    async submitDiscrepancy() {
        const form = document.getElementById('discrepancyForm');
        const formData = new FormData(form);

        const discrepancyData = {
            audit_id: formData.get('audit_id'),
            asset_code: formData.get('asset_code'),
            finding_type: formData.get('finding_type'),
            severity: formData.get('severity'),
            description: formData.get('description'),
            corrective_action: formData.get('corrective_action'),
            target_date: formData.get('target_date')
        };

        try {
            const response = await fetch('api/property_audit.php?action=add_finding', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(discrepancyData)
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.showNotification('Discrepancy reported successfully', 'success');
                this.hideDiscrepancyModal();
                this.loadAuditStats();
            } else {
                this.showNotification(result.message || 'Error reporting discrepancy', 'error');
            }
        } catch (error) {
            console.error('Error reporting discrepancy:', error);
            this.showNotification('Error reporting discrepancy', 'error');
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        const typeClasses = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        };

        notification.className = `fixed top-4 right-4 ${typeClasses[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-transform transform translate-x-0`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.propertyAuditManager = new PropertyAuditManager();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PropertyAuditManager;
}