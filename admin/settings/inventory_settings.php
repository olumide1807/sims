<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Settings | Inventory System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* Base styles matching your existing design */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .layout-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .logo-section {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo-section h4 {
            margin: 0;
            font-weight: 600;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 0;
        }

        .header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            width: 300px;
        }

        .search-box input {
            border: none;
            background: none;
            outline: none;
            flex: 1;
        }

        .user-section {
            display: flex;
            align-items: center;
        }

        .notification-badge {
            position: relative;
        }

        .notification-badge .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.7rem;
        }

        .content-container {
            padding: 2rem;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .settings-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .setting-item {
            padding: 1.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .setting-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .setting-control {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px 0 0 8px;
            padding: 0.75rem 1rem;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
            border-left: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 1.2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .header {
                padding-left: 4rem;
            }

            .search-box {
                width: 200px;
            }

            .content-container {
                padding: 1rem;
            }

            .setting-control {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="layout-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo-section">
                <h4 class="d-flex align-items-center gap-2">
                    <i class="fas fa-cubes text-primary"></i>
                    SIMS
                </h4>
            </div>

            <nav>
                <!-- Dashboard -->
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <!-- Inventory Management -->
                <a href="inventory.php" class="nav-link">
                    <i class="fas fa-box"></i> View Inventory
                </a>

                <!-- Settings -->
                <a href="#" class="nav-link active">
                    <i class="fas fa-cog"></i> Inventory Settings
                </a>

                <!-- Sales Management -->
                <a href="sales.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Sales
                </a>

                <!-- Logout -->
                <a href="../logout/" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="search-box">
                    <i class="fas fa-search text-muted me-2"></i>
                    <input type="text" placeholder="Search settings...">
                </div>
                <div class="user-section">
                    <div class="notification-badge">
                        <i class="fas fa-bell text-muted"></i>
                        <span class="badge rounded-pill bg-danger">3</span>
                    </div>
                    <div class="user-info ms-3">
                        <span class="fw-bold">Admin User</span>
                        <small class="d-block text-muted">Administrator</small>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="content-container">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">
                                <i class="fas fa-cog text-primary me-3"></i>
                                Inventory Settings
                            </h2>
                            <p class="text-muted mb-0">Configure your inventory management preferences and thresholds</p>
                        </div>
                        <div>
                            <button class="btn btn-outline-secondary me-2" onclick="resetToDefaults()">
                                <i class="fas fa-undo me-2"></i>Reset to Defaults
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Success Alert (initially hidden) -->
                <div class="alert alert-success" id="success-alert" style="display: none;">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Settings saved successfully!</strong> Your changes have been applied.
                </div>

                <!-- Settings Form -->
                <form id="settings-form">
                    <!-- Stock Management Settings -->
                    <div class="settings-card">
                        <h4 class="mb-4">
                            <i class="fas fa-boxes text-primary me-2"></i>
                            Stock Management
                        </h4>

                        <!-- Default Reorder Level -->
                        <div class="setting-item">
                            <div class="setting-label">Default Reorder Level</div>
                            <div class="setting-description">
                                Set the default minimum stock level that triggers reorder alerts for new products.
                                This will be applied to products that don't have a specific reorder level set.
                            </div>
                            <div class="setting-control">
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text">
                                        <i class="fas fa-layer-group"></i>
                                    </span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="default-reorder-level" 
                                           value="10" 
                                           min="0" 
                                           max="1000">
                                    <span class="input-group-text">units</span>
                                </div>
                                <small class="text-muted">Recommended: 5-20 units</small>
                            </div>
                        </div>

                        <!-- Low Stock Threshold -->
                        <div class="setting-item">
                            <div class="setting-label">Low Stock Warning Multiplier</div>
                            <div class="setting-description">
                                Show low stock warnings when inventory falls below this multiplier of the reorder level.
                                For example, 1.5x means warn when stock is 1.5 times the reorder level.
                            </div>
                            <div class="setting-control">
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="low-stock-multiplier" 
                                           value="1.5" 
                                           min="1" 
                                           max="5" 
                                           step="0.1">
                                    <span class="input-group-text">×</span>
                                </div>
                                <small class="text-muted">Recommended: 1.2-2.0</small>
                            </div>
                        </div>
                    </div>

                    <!-- Expiration Management Settings -->
                    <div class="settings-card">
                        <h4 class="mb-4">
                            <i class="fas fa-calendar-alt text-warning me-2"></i>
                            Expiration Management
                        </h4>

                        <!-- Expiration Warning Period -->
                        <div class="setting-item">
                            <div class="setting-label">Expiration Warning Period</div>
                            <div class="setting-description">
                                Set how many days before expiration date to start showing warnings.
                                Products expiring within this timeframe will appear in alerts and reports.
                            </div>
                            <div class="setting-control">
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="expiration-warning-days" 
                                           value="30" 
                                           min="1" 
                                           max="365">
                                    <span class="input-group-text">days</span>
                                </div>
                                <small class="text-muted">Recommended: 15-60 days</small>
                            </div>
                        </div>

                        <!-- Critical Expiration Period -->
                        <div class="setting-item">
                            <div class="setting-label">Critical Expiration Period</div>
                            <div class="setting-description">
                                Set how many days before expiration to mark items as critically expiring.
                                These items will be highlighted with urgent alerts and priority notifications.
                            </div>
                            <div class="setting-control">
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="critical-expiration-days" 
                                           value="7" 
                                           min="1" 
                                           max="30">
                                    <span class="input-group-text">days</span>
                                </div>
                                <small class="text-muted">Recommended: 3-14 days</small>
                            </div>
                        </div>

                        <!-- Auto-Remove Expired Items -->
                        <div class="setting-item">
                            <div class="setting-label">Auto-Remove Expired Items</div>
                            <div class="setting-description">
                                Automatically remove expired items from active inventory after the specified number of days past expiration.
                                Items will be moved to expired inventory log.
                            </div>
                            <div class="setting-control">
                                <div class="form-check form-switch me-3">
                                    <input class="form-check-input" type="checkbox" id="auto-remove-expired" checked>
                                    <label class="form-check-label" for="auto-remove-expired">
                                        Enable auto-removal
                                    </label>
                                </div>
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="auto-remove-days" 
                                           value="3" 
                                           min="1" 
                                           max="30">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="settings-card">
                        <h4 class="mb-4">
                            <i class="fas fa-bell text-info me-2"></i>
                            Notification Preferences
                        </h4>

                        <!-- Email Notifications -->
                        <div class="setting-item">
                            <div class="setting-label">Email Notifications</div>
                            <div class="setting-description">
                                Receive email notifications for low stock and expiration alerts.
                            </div>
                            <div class="setting-control">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email-notifications" checked>
                                    <label class="form-check-label" for="email-notifications">
                                        Enable email notifications
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Dashboard Alerts -->
                        <div class="setting-item">
                            <div class="setting-label">Dashboard Alerts</div>
                            <div class="setting-description">
                                Show alert notifications on the dashboard for inventory issues.
                            </div>
                            <div class="setting-control">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="dashboard-alerts" checked>
                                    <label class="form-check-label" for="dashboard-alerts">
                                        Show dashboard alerts
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-3">
                        <button type="button" class="btn btn-secondary" onclick="cancelChanges()">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Form submission handler
        document.getElementById('settings-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulate saving settings
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Show success message
                const alertEl = document.getElementById('success-alert');
                alertEl.style.display = 'block';
                
                // Scroll to top to show alert
                window.scrollTo({top: 0, behavior: 'smooth'});
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Hide alert after 5 seconds
                setTimeout(() => {
                    alertEl.style.display = 'none';
                }, 5000);
                
                console.log('Settings saved:', getFormData());
            }, 1500);
        });

        // Reset to defaults function
        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all settings to their default values?')) {
                document.getElementById('default-reorder-level').value = '10';
                document.getElementById('low-stock-multiplier').value = '1.5';
                document.getElementById('expiration-warning-days').value = '30';
                document.getElementById('critical-expiration-days').value = '7';
                document.getElementById('auto-remove-days').value = '3';
                document.getElementById('auto-remove-expired').checked = true;
                document.getElementById('email-notifications').checked = true;
                document.getElementById('dashboard-alerts').checked = true;
            }
        }

        // Cancel changes function
        function cancelChanges() {
            if (confirm('Are you sure you want to cancel your changes?')) {
                // Reload the page or reset form to last saved state
                location.reload();
            }
        }

        // Get form data helper function
        function getFormData() {
            return {
                defaultReorderLevel: document.getElementById('default-reorder-level').value,
                lowStockMultiplier: document.getElementById('low-stock-multiplier').value,
                expirationWarningDays: document.getElementById('expiration-warning-days').value,
                criticalExpirationDays: document.getElementById('critical-expiration-days').value,
                autoRemoveDays: document.getElementById('auto-remove-days').value,
                autoRemoveExpired: document.getElementById('auto-remove-expired').checked,
                emailNotifications: document.getElementById('email-notifications').checked,
                dashboardAlerts: document.getElementById('dashboard-alerts').checked
            };
        }

        // Enable/disable auto-remove days input based on checkbox
        document.getElementById('auto-remove-expired').addEventListener('change', function() {
            document.getElementById('auto-remove-days').disabled = !this.checked;
        });

        // Validate critical expiration is less than warning expiration
        document.getElementById('critical-expiration-days').addEventListener('change', function() {
            const warningDays = parseInt(document.getElementById('expiration-warning-days').value);
            const criticalDays = parseInt(this.value);
            
            if (criticalDays >= warningDays) {
                alert('Critical expiration period should be less than the warning period.');
                this.value = Math.max(1, warningDays - 1);
            }
        });

        // Validate warning expiration is greater than critical expiration
        document.getElementById('expiration-warning-days').addEventListener('change', function() {
            const warningDays = parseInt(this.value);
            const criticalDays = parseInt(document.getElementById('critical-expiration-days').value);
            
            if (criticalDays >= warningDays) {
                document.getElementById('critical-expiration-days').value = Math.max(1, warningDays - 1);
            }
        });
    </script>
</body>

</html>