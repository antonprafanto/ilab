/**
 * Admin Interface JavaScript for ILab UNMUL
 * Enhanced functionality and user experience
 */

// Global Admin Object
const ILabAdmin = {
    // Configuration
    config: {
        ajaxTimeout: 30000,
        refreshInterval: 300000, // 5 minutes
        animationDuration: 300,
        notificationTimeout: 5000
    },

    // Initialize admin interface
    init() {
        console.log('ILab Admin Interface Initializing...');
        
        this.setupEventListeners();
        this.initializeTooltips();
        this.initializeModals();
        this.setupAjaxDefaults();
        this.startAutoRefresh();
        this.setupSearchFilters();
        this.initializeCharts();
        this.setupNotifications();
        
        console.log('ILab Admin Interface Ready');
    },

    // Setup global event listeners
    setupEventListeners() {
        // Sidebar navigation
        this.setupSidebarNavigation();
        
        // Table actions
        this.setupTableActions();
        
        // Form submissions
        this.setupFormSubmissions();
        
        // Search and filters
        this.setupSearchAndFilters();
        
        // File uploads
        this.setupFileUploads();
        
        // Keyboard shortcuts
        this.setupKeyboardShortcuts();
    },

    // Sidebar navigation enhancements
    setupSidebarNavigation() {
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
        
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Remove active class from all links
                sidebarLinks.forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Store active link in localStorage
                localStorage.setItem('activeNavLink', this.getAttribute('href'));
            });
        });
        
        // Restore active link from localStorage
        const activeLink = localStorage.getItem('activeNavLink');
        if (activeLink) {
            const link = document.querySelector(`[href="${activeLink}"]`);
            if (link) {
                sidebarLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        }
    },

    // Enhanced table functionality
    setupTableActions() {
        // Row hover effects
        document.querySelectorAll('.table tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.01)';
                this.style.transition = 'all 0.2s ease';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Bulk actions
        this.setupBulkActions();
        
        // Sortable columns
        this.setupSortableColumns();
        
        // Row selection
        this.setupRowSelection();
    },

    // Bulk actions for tables
    setupBulkActions() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionBtn = document.getElementById('bulkActionBtn');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                ILabAdmin.updateBulkActionBtn();
            });
        }
        
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                ILabAdmin.updateBulkActionBtn();
            });
        });
    },

    // Update bulk action button state
    updateBulkActionBtn() {
        const selectedRows = document.querySelectorAll('.row-checkbox:checked').length;
        const bulkActionBtn = document.getElementById('bulkActionBtn');
        
        if (bulkActionBtn) {
            if (selectedRows > 0) {
                bulkActionBtn.style.display = 'block';
                bulkActionBtn.textContent = `Actions (${selectedRows} selected)`;
            } else {
                bulkActionBtn.style.display = 'none';
            }
        }
    },

    // Row selection highlighting
    setupRowSelection() {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const row = this.closest('tr');
                if (this.checked) {
                    row.classList.add('table-warning');
                } else {
                    row.classList.remove('table-warning');
                }
            });
        });
    },

    // Sortable table columns
    setupSortableColumns() {
        document.querySelectorAll('.sortable').forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const table = this.closest('table');
                const column = Array.from(this.parentNode.children).indexOf(this);
                ILabAdmin.sortTable(table, column);
            });
        });
    },

    // Sort table by column
    sortTable(table, column) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = table.dataset.sortDirection !== 'asc';
        
        rows.sort((a, b) => {
            const aText = a.children[column].textContent.trim();
            const bText = b.children[column].textContent.trim();
            
            // Check if numeric
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            return isAscending ? 
                aText.localeCompare(bText) : 
                bText.localeCompare(aText);
        });
        
        // Update table
        rows.forEach(row => tbody.appendChild(row));
        table.dataset.sortDirection = isAscending ? 'asc' : 'desc';
        
        // Update sort indicators
        const headers = table.querySelectorAll('th');
        headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
        headers[column].classList.add(isAscending ? 'sort-asc' : 'sort-desc');
    },

    // Enhanced form submissions
    setupFormSubmissions() {
        document.querySelectorAll('form[data-ajax="true"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                ILabAdmin.submitFormAjax(this);
            });
        });
        
        // Auto-save functionality
        document.querySelectorAll('[data-autosave="true"]').forEach(input => {
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    ILabAdmin.autoSaveField(this);
                }, 1000);
            });
        });
    },

    // AJAX form submission
    submitFormAjax(form) {
        const formData = new FormData(form);
        const button = form.querySelector('[type="submit"]');
        const originalText = button.textContent;
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        fetch(form.action || window.location.href, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Success!', data.message, 'success');
                if (data.redirect) {
                    setTimeout(() => window.location.href = data.redirect, 1000);
                }
            } else {
                this.showNotification('Error!', data.error || 'Something went wrong', 'error');
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            this.showNotification('Error!', 'Network error occurred', 'error');
        })
        .finally(() => {
            // Restore button state
            button.disabled = false;
            button.textContent = originalText;
        });
    },

    // Auto-save form fields
    autoSaveField(input) {
        const formData = new FormData();
        formData.append(input.name, input.value);
        formData.append('action', 'autosave');
        formData.append('field', input.name);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.classList.add('is-valid');
                setTimeout(() => input.classList.remove('is-valid'), 2000);
            }
        })
        .catch(error => {
            console.error('Auto-save error:', error);
        });
    },

    // Search and filter functionality
    setupSearchAndFilters() {
        // Real-time search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    ILabAdmin.performSearch(this.value);
                }, 300);
            });
        }
        
        // Filter dropdowns
        document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                ILabAdmin.applyFilters();
            });
        });
        
        // Date range filters
        this.setupDateRangeFilters();
    },

    // Perform search
    performSearch(query) {
        const searchParams = new URLSearchParams(window.location.search);
        if (query) {
            searchParams.set('search', query);
        } else {
            searchParams.delete('search');
        }
        
        // Update URL without reload
        const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.pushState({}, '', newUrl);
        
        // Reload table content
        this.reloadTableContent();
    },

    // Apply all filters
    applyFilters() {
        const searchParams = new URLSearchParams(window.location.search);
        
        // Collect all filter values
        document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
            if (dropdown.value) {
                searchParams.set(dropdown.name, dropdown.value);
            } else {
                searchParams.delete(dropdown.name);
            }
        });
        
        // Update URL and reload
        const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.pushState({}, '', newUrl);
        this.reloadTableContent();
    },

    // Setup date range filters
    setupDateRangeFilters() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.applyFilters();
            });
        });
        
        // Quick date range buttons
        document.querySelectorAll('.quick-date-range').forEach(button => {
            button.addEventListener('click', function() {
                const range = this.dataset.range;
                const { start, end } = ILabAdmin.getDateRange(range);
                
                const startInput = document.getElementById('date_from');
                const endInput = document.getElementById('date_to');
                
                if (startInput) startInput.value = start;
                if (endInput) endInput.value = end;
                
                ILabAdmin.applyFilters();
            });
        });
    },

    // Get date range based on preset
    getDateRange(range) {
        const today = new Date();
        const start = new Date();
        
        switch (range) {
            case 'today':
                break;
            case 'week':
                start.setDate(today.getDate() - 7);
                break;
            case 'month':
                start.setMonth(today.getMonth() - 1);
                break;
            case 'quarter':
                start.setMonth(today.getMonth() - 3);
                break;
            case 'year':
                start.setFullYear(today.getFullYear() - 1);
                break;
        }
        
        return {
            start: start.toISOString().split('T')[0],
            end: today.toISOString().split('T')[0]
        };
    },

    // Reload table content via AJAX
    reloadTableContent() {
        const tableContainer = document.querySelector('.table-responsive');
        if (!tableContainer) return;
        
        // Show loading indicator
        const loader = document.createElement('div');
        loader.className = 'text-center p-4';
        loader.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i>';
        tableContainer.appendChild(loader);
        
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('.table-responsive');
            
            if (newTable) {
                tableContainer.innerHTML = newTable.innerHTML;
                // Re-initialize table functionality
                this.setupTableActions();
            }
        })
        .catch(error => {
            console.error('Table reload error:', error);
            this.showNotification('Error!', 'Failed to reload data', 'error');
        })
        .finally(() => {
            // Remove loader
            const currentLoader = tableContainer.querySelector('.text-center');
            if (currentLoader) currentLoader.remove();
        });
    },

    // File upload enhancements
    setupFileUploads() {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                ILabAdmin.validateFileUpload(this);
            });
        });
        
        // Drag and drop
        this.setupDragAndDrop();
    },

    // Validate file uploads
    validateFileUpload(input) {
        const files = input.files;
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword'];
        
        for (let file of files) {
            if (file.size > maxSize) {
                this.showNotification('Error!', `File ${file.name} is too large. Maximum size is 10MB.`, 'error');
                input.value = '';
                return false;
            }
            
            if (!allowedTypes.includes(file.type)) {
                this.showNotification('Error!', `File type ${file.type} is not allowed.`, 'error');
                input.value = '';
                return false;
            }
        }
        
        // Show preview for images
        if (files[0] && files[0].type.startsWith('image/')) {
            this.showImagePreview(input, files[0]);
        }
        
        return true;
    },

    // Show image preview
    showImagePreview(input, file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = input.parentNode.querySelector('.image-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'image-preview mt-2';
                input.parentNode.appendChild(preview);
            }
            
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" 
                     style="max-width: 200px; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            `;
        };
        reader.readAsDataURL(file);
    },

    // Drag and drop file upload
    setupDragAndDrop() {
        document.querySelectorAll('.drag-drop-area').forEach(area => {
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            
            area.addEventListener('dragleave', function() {
                this.classList.remove('drag-over');
            });
            
            area.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                
                const files = e.dataTransfer.files;
                const input = this.querySelector('input[type="file"]');
                
                if (input && files.length > 0) {
                    input.files = files;
                    ILabAdmin.validateFileUpload(input);
                }
            });
        });
    },

    // Keyboard shortcuts
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S for save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const saveBtn = document.querySelector('[type="submit"], .btn-save');
                if (saveBtn) saveBtn.click();
            }
            
            // Ctrl/Cmd + F for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) searchInput.focus();
            }
            
            // Escape to close modals
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    const modal = bootstrap.Modal.getInstance(openModal);
                    if (modal) modal.hide();
                }
            }
        });
    },

    // Initialize tooltips
    initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                delay: { show: 500, hide: 100 }
            });
        });
    },

    // Initialize modals
    initializeModals() {
        // Auto-focus first input in modals
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                const firstInput = this.querySelector('input:not([type="hidden"]), textarea, select');
                if (firstInput) firstInput.focus();
            });
        });
        
        // Confirm before closing unsaved forms
        document.querySelectorAll('.modal form').forEach(form => {
            let formChanged = false;
            
            form.addEventListener('input', () => formChanged = true);
            
            const modal = form.closest('.modal');
            modal.addEventListener('hide.bs.modal', function(e) {
                if (formChanged && !confirm('You have unsaved changes. Are you sure you want to close?')) {
                    e.preventDefault();
                }
            });
        });
    },

    // Setup AJAX defaults
    setupAjaxDefaults() {
        // Add CSRF token to all AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            // Set default headers for fetch requests
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                options.headers = options.headers || {};
                options.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
                return originalFetch(url, options);
            };
        }
    },

    // Auto-refresh functionality
    startAutoRefresh() {
        // Only refresh if user is active
        let lastActivity = Date.now();
        
        document.addEventListener('mousemove', () => lastActivity = Date.now());
        document.addEventListener('keypress', () => lastActivity = Date.now());
        
        setInterval(() => {
            const inactiveTime = Date.now() - lastActivity;
            if (inactiveTime < this.config.refreshInterval) {
                this.refreshDashboardData();
            }
        }, this.config.refreshInterval);
    },

    // Refresh dashboard data
    refreshDashboardData() {
        const dashboardCards = document.querySelectorAll('[data-refresh="true"]');
        
        dashboardCards.forEach(card => {
            const endpoint = card.dataset.endpoint;
            if (endpoint) {
                fetch(endpoint, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    this.updateDashboardCard(card, data);
                })
                .catch(error => {
                    console.error('Dashboard refresh error:', error);
                });
            }
        });
    },

    // Update dashboard card with new data
    updateDashboardCard(card, data) {
        const valueElement = card.querySelector('.card-value, .h5, h5');
        if (valueElement && data.value !== undefined) {
            const currentValue = parseInt(valueElement.textContent.replace(/[^\d]/g, ''));
            const newValue = parseInt(data.value);
            
            // Animate value change
            this.animateNumber(valueElement, currentValue, newValue);
            
            // Add pulse effect if value changed
            if (currentValue !== newValue) {
                card.classList.add('pulse');
                setTimeout(() => card.classList.remove('pulse'), 2000);
            }
        }
    },

    // Animate number changes
    animateNumber(element, start, end) {
        const duration = 1000;
        const increment = (end - start) / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = Math.round(current).toLocaleString();
        }, 16);
    },

    // Initialize charts
    initializeCharts() {
        // Chart.js initialization would go here
        // This is a placeholder for future chart implementations
        const chartContainers = document.querySelectorAll('.chart-container');
        chartContainers.forEach(container => {
            // Initialize specific chart based on container data attributes
            const chartType = container.dataset.chartType;
            if (chartType) {
                this.createChart(container, chartType);
            }
        });
    },

    // Create chart (placeholder)
    createChart(container, type) {
        // This would integrate with Chart.js or similar library
        console.log(`Creating ${type} chart in container:`, container);
    },

    // Notification system
    setupNotifications() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
    },

    // Show notification
    showNotification(title, message, type = 'info') {
        const container = document.getElementById('notification-container');
        const id = 'notification-' + Date.now();
        
        const typeClasses = {
            success: 'alert-success',
            error: 'alert-danger',
            warning: 'alert-warning',
            info: 'alert-info'
        };
        
        const typeIcons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        
        const notification = document.createElement('div');
        notification.id = id;
        notification.className = `alert ${typeClasses[type]} alert-dismissible fade show shadow-strong`;
        notification.style.cssText = 'margin-bottom: 10px; border-radius: 10px;';
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="${typeIcons[type]} me-2"></i>
                <div class="flex-grow-1">
                    <strong>${title}</strong>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        container.appendChild(notification);
        
        // Auto-remove after timeout
        setTimeout(() => {
            const notificationElement = document.getElementById(id);
            if (notificationElement) {
                notificationElement.classList.remove('show');
                setTimeout(() => notificationElement.remove(), 150);
            }
        }, this.config.notificationTimeout);
    },

    // Utility functions
    utils: {
        // Format currency
        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        },
        
        // Format date
        formatDate(date) {
            return new Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }).format(new Date(date));
        },
        
        // Debounce function
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Throttle function
        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    ILabAdmin.init();
});

// Export for global access
window.ILabAdmin = ILabAdmin;