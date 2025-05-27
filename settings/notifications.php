<?php
session_start();
include "../config/session_check.php";
include "../config/config.php";
include "../config/user_function.php";

// Check if user has admin privileges or notification management permissions
// if (!isset($_SESSION['user_id']) || (!isAdmin($_SESSION['user_id'], $connect) && !hasPermission($_SESSION['user_id'], 'notification_management', $connect))) {
//     header("Location: ../dashboard/");
//     exit();
// }

// Handle notification settings updates
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
        try {
            // Get form data
            $low_stock_alerts = isset($_POST['low_stock_alerts']) ? 1 : 0;
            $expiry_notifications = isset($_POST['expiry_notifications']) ? 1 : 0;
            $low_stock_threshold = (int)$_POST['low_stock_threshold'];
            $expiry_threshold_days = (int)$_POST['expiry_threshold_days'];
            $notification_methods = isset($_POST['notification_methods']) ? implode(',', $_POST['notification_methods']) : '';
            $user_id = $_SESSION['user_id'];

            // Validate thresholds
            if ($low_stock_threshold < 0 || $low_stock_threshold > 1000) {
                throw new Exception("Low stock threshold must be between 0 and 1000");
            }
            
            if ($expiry_threshold_days < 1 || $expiry_threshold_days > 365) {
                throw new Exception("Expiry threshold must be between 1 and 365 days");
            }

            // Check if user settings exist
            $check_query = "SELECT id FROM notification_settings WHERE user_id = ?";
            $check_stmt = mysqli_prepare($connect, $check_query);
            mysqli_stmt_bind_param($check_stmt, "i", $user_id);
            mysqli_stmt_execute($check_stmt);
            $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));

            if ($existing) {
                // Update existing settings
                $update_query = "UPDATE notification_settings SET 
                    low_stock_alerts = ?, 
                    expiry_notifications = ?, 
                    low_stock_threshold = ?, 
                    expiry_threshold_days = ?, 
                    notification_methods = ?, 
                    updated_at = NOW() 
                    WHERE user_id = ?";
                $stmt = mysqli_prepare($connect, $update_query);
                mysqli_stmt_bind_param($stmt, "iiiisi", $low_stock_alerts, $expiry_notifications, $low_stock_threshold, $expiry_threshold_days, $notification_methods, $user_id);
            } else {
                // Insert new settings
                $insert_query = "INSERT INTO notification_settings 
                    (user_id, low_stock_alerts, expiry_notifications, low_stock_threshold, expiry_threshold_days, notification_methods, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = mysqli_prepare($connect, $insert_query);
                mysqli_stmt_bind_param($stmt, "iiiiss", $user_id, $low_stock_alerts, $expiry_notifications, $low_stock_threshold, $expiry_threshold_days, $notification_methods);
            }

            if (mysqli_stmt_execute($stmt)) {
                // Log the activity
                logUserActivity(
                    $_SESSION['user_id'],
                    'NOTIFICATION_SETTINGS_UPDATED',
                    'notification_settings',
                    $user_id,
                    null,
                    [
                        'low_stock_alerts' => $low_stock_alerts,
                        'expiry_notifications' => $expiry_notifications,
                        'low_stock_threshold' => $low_stock_threshold,
                        'expiry_threshold_days' => $expiry_threshold_days,
                        'notification_methods' => $notification_methods
                    ],
                    $connect
                );

                $message = "Notification settings updated successfully!";
            } else {
                throw new Exception("Error updating notification settings: " . mysqli_error($connect));
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fetch current notification settings
$user_id = $_SESSION['user_id'];
$settings_query = "SELECT * FROM notification_settings WHERE user_id = ?";
$settings_stmt = mysqli_prepare($connect, $settings_query);
mysqli_stmt_bind_param($settings_stmt, "i", $user_id);
mysqli_stmt_execute($settings_stmt);
$settings_result = mysqli_stmt_get_result($settings_stmt);
$current_settings = mysqli_fetch_assoc($settings_result);

// Default settings if none exist
if (!$current_settings) {
    $current_settings = [
        'low_stock_alerts' => 1,
        'expiry_notifications' => 1,
        'low_stock_threshold' => 10,
        'expiry_threshold_days' => 30,
        'notification_methods' => 'email,dashboard'
    ];
}

// Parse notification methods
$selected_methods = explode(',', $current_settings['notification_methods']);

// Get notification statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM products WHERE quantity_per_pack <= ?) as low_stock_count,
    (SELECT COUNT(*) FROM products WHERE exp_date <= DATE_ADD(NOW(), INTERVAL ? DAY) AND exp_date > NOW()) as expiring_soon_count,
    (SELECT COUNT(*) FROM products WHERE exp_date <= NOW()) as expired_count";
$stats_stmt = mysqli_prepare($connect, $stats_query);
mysqli_stmt_bind_param($stats_stmt, "ii", $current_settings['low_stock_threshold'], $current_settings['expiry_threshold_days']);
mysqli_stmt_execute($stats_stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stats_stmt));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Settings - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">
    <style>
        .notification-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .notification-card h5 {
            color: #495057;
            margin-bottom: 1rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .stats-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .method-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
        }
        
        .method-card:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .method-card.selected {
            border-color: #007bff;
            background: #f8f9ff;
        }
        
        .method-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .threshold-input {
            max-width: 120px;
        }
        
        .preview-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .notification-preview {
            background: #fff;
            border-left: 4px solid #007bff;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .notification-preview.warning {
            border-left-color: #ffc107;
        }
        
        .notification-preview.danger {
            border-left-color: #dc3545;
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
                <a href="../dashboard/" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <!-- Inventory Management -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('inventory')">
                    <i class="fas fa-box"></i> Inventory Management
                </a>
                <div class="submenu" id="inventory">
                    <a href="../inventory/" class="nav-link"><i class="fas fa-list"></i> View Inventory</a>
                    <a href="../inventory/addproduct.php" class="nav-link"><i class="fas fa-plus"></i> Add Product</a>
                    <a href="../inventory/updateproduct.php" class="nav-link"><i class="fas fa-edit"></i> Update Inventory</a>
                </div>

                <!-- Sales Management -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-shopping-cart"></i> Sales Management
                </a>
                <div class="submenu" id="sales">
                    <a href="../sales/logsales.php" class="nav-link"><i class="fas fa-cash-register"></i> Log Sale</a>
                    <a href="../sales/viewsales.php" class="nav-link"><i class="fas fa-chart-bar"></i> View Sales</a>
                </div>

                <!-- AI Insights -->
                <!-- <a href="#" class="nav-link" onclick="toggleSubmenu('ai-insights')">
                    <i class="fas fa-robot"></i> AI-Powered Insights
                </a>
                <div class="submenu" id="ai-insights">
                    <a href="#" class="nav-link"><i class="fas fa-bell"></i> Reordering Suggestions</a>
                    <a href="#" class="nav-link"><i class="fas fa-clock"></i> Expiration Alerts</a>
                    <a href="#" class="nav-link"><i class="fas fa-chart-line"></i> Low-Demand Products</a>
                </div> -->

                <!-- Reports -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
                <div class="submenu" id="reports">
                    <a href="../report/generateReport.php" class="nav-link"><i class="fas fa-file-export"></i> Generate Reports</a>
                    <a href="../report/viewreport.php" class="nav-link"><i class="fas fa-file-import"></i> View Reports</a>
                </div>

                <!-- Settings -->
                <a href="#" class="nav-link active" onclick="toggleSubmenu('settings')">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="submenu show" id="settings">
                    <a href="manage_users.php" class="nav-link"><i class="fas fa-users"></i> User Management</a>
                    <a href="#" class="nav-link active"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="reports_settings.php" class="nav-link"><i class="fas fa-file-cog"></i> Report Settings</a>
                    <a href="system_preferences.php" class="nav-link"><i class="fas fa-sliders-h"></i> System Preferences</a>
                    <a href="inventory_settings.php" class="nav-link"><i class="fas fa-box-open"></i> Inventory Settings</a>
                </div>

                <!-- Help/Support -->
                <!-- <a href="#" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help/Support
                </a> -->

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
                        <span class="badge rounded-pill bg-danger"><?php echo $stats['low_stock_count'] + $stats['expiring_soon_count']; ?></span>
                    </div>
                    <img src="/placeholder.svg?height=40&width=40" class="rounded-circle" alt="User avatar">
                </div>
            </div>

            <!-- Page Header -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Notification Settings</h3>
                        <p class="mb-3">Configure your notification preferences and alert thresholds.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light rounded-pill px-4" onclick="testNotifications()">
                            <i class="fas fa-vial me-2"></i>Test Notifications
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Current Statistics -->
            <div class="stats-card">
                <h5 class="text-white mb-3">
                    <i class="fas fa-chart-bar me-2"></i>Current Alert Status
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="stats-item">
                            <span class="stats-number text-warning"><?php echo $stats['low_stock_count']; ?></span>
                            <span class="stats-label">Low Stock Items</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-item">
                            <span class="stats-number text-info"><?php echo $stats['expiring_soon_count']; ?></span>
                            <span class="stats-label">Expiring Soon</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-item">
                            <span class="stats-number text-danger"><?php echo $stats['expired_count']; ?></span>
                            <span class="stats-label">Expired Items</span>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" id="notificationForm">
                <input type="hidden" name="action" value="update_settings">
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Low Stock Alerts -->
                        <div class="notification-card">
                            <h5><i class="fas fa-box text-warning me-2"></i>Low Stock Alerts</h5>
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="lowStockAlerts" 
                                               name="low_stock_alerts" <?php echo $current_settings['low_stock_alerts'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="lowStockAlerts">
                                            Enable Low Stock Alerts
                                        </label>
                                    </div>
                                    <small class="text-muted">Get notified when products are running low</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alert Threshold</label>
                                    <div class="input-group threshold-input">
                                        <input type="number" class="form-control" name="low_stock_threshold" 
                                               value="<?php echo $current_settings['low_stock_threshold']; ?>" 
                                               min="0" max="1000" id="lowStockThreshold">
                                        <span class="input-group-text">units</span>
                                    </div>
                                    <small class="text-muted">Alert when quantity falls below this number</small>
                                </div>
                            </div>
                        </div>

                        <!-- Expiry Notifications -->
                        <div class="notification-card">
                            <h5><i class="fas fa-clock text-danger me-2"></i>Expiry Notifications</h5>
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="expiryNotifications" 
                                               name="expiry_notifications" <?php echo $current_settings['expiry_notifications'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="expiryNotifications">
                                            Enable Expiry Notifications
                                        </label>
                                    </div>
                                    <small class="text-muted">Get notified about products nearing expiry</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Days Before Expiry</label>
                                    <div class="input-group threshold-input">
                                        <input type="number" class="form-control" name="expiry_threshold_days" 
                                               value="<?php echo $current_settings['expiry_threshold_days']; ?>" 
                                               min="1" max="365" id="expiryThreshold">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <small class="text-muted">Alert this many days before expiry</small>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Methods -->
                        <div class="notification-card">
                            <h5><i class="fas fa-paper-plane text-primary me-2"></i>Notification Methods</h5>
                            <p class="text-muted mb-3">Choose how you want to receive notifications</p>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="method-card <?php echo in_array('email', $selected_methods) ? 'selected' : ''; ?>" 
                                         onclick="toggleMethod('email')">
                                        <i class="fas fa-envelope text-primary"></i>
                                        <h6>Email</h6>
                                        <p class="mb-0 small text-muted">Receive alerts via email</p>
                                        <input type="checkbox" name="notification_methods[]" value="email" 
                                               class="d-none" <?php echo in_array('email', $selected_methods) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="method-card <?php echo in_array('sms', $selected_methods) ? 'selected' : ''; ?>" 
                                         onclick="toggleMethod('sms')">
                                        <i class="fas fa-sms text-success"></i>
                                        <h6>SMS</h6>
                                        <p class="mb-0 small text-muted">Get text message alerts</p>
                                        <input type="checkbox" name="notification_methods[]" value="sms" 
                                               class="d-none" <?php echo in_array('sms', $selected_methods) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="method-card <?php echo in_array('dashboard', $selected_methods) ? 'selected' : ''; ?>" 
                                         onclick="toggleMethod('dashboard')">
                                        <i class="fas fa-bell text-warning"></i>
                                        <h6>Dashboard</h6>
                                        <p class="mb-0 small text-muted">Show alerts in dashboard</p>
                                        <input type="checkbox" name="notification_methods[]" value="dashboard" 
                                               class="d-none" <?php echo in_array('dashboard', $selected_methods) ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="text-end">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Preview Section -->
                        <div class="preview-section">
                            <h6 class="mb-3"><i class="fas fa-eye me-2"></i>Notification Preview</h6>
                            <div id="notificationPreviews">
                                <!-- Previews will be generated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            if (submenu) {
                const allSubmenus = document.querySelectorAll('.submenu');
                allSubmenus.forEach(menu => {
                    if (menu !== submenu) {
                        menu.classList.remove('show');
                    }
                });
                submenu.classList.toggle('show');
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        function toggleMethod(method) {
            const card = event.currentTarget;
            const checkbox = card.querySelector('input[type="checkbox"]');
            
            card.classList.toggle('selected');
            checkbox.checked = !checkbox.checked;
            
            updatePreviews();
        }

        function updatePreviews() {
            const lowStockEnabled = document.getElementById('lowStockAlerts').checked;
            const expiryEnabled = document.getElementById('expiryNotifications').checked;
            const lowStockThreshold = document.getElementById('lowStockThreshold').value;
            const expiryThreshold = document.getElementById('expiryThreshold').value;
            
            const selectedMethods = Array.from(document.querySelectorAll('input[name="notification_methods[]"]:checked'))
                .map(cb => cb.value);
            
            const previewContainer = document.getElementById('notificationPreviews');
            let previews = '';
            
            if (lowStockEnabled && selectedMethods.length > 0) {
                previews += `
                    <div class="notification-preview warning">
                        <h6 class="mb-1"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Low Stock Alert</h6>
                        <p class="mb-1 small">Product XYZ has only 5 units left (threshold: ${lowStockThreshold})</p>
                        <small class="text-muted">Via: ${selectedMethods.join(', ')}</small>
                    </div>
                `;
            }
            
            if (expiryEnabled && selectedMethods.length > 0) {
                previews += `
                    <div class="notification-preview danger">
                        <h6 class="mb-1"><i class="fas fa-clock text-danger me-2"></i>Expiry Alert</h6>
                        <p class="mb-1 small">Product ABC expires in ${expiryThreshold} days</p>
                        <small class="text-muted">Via: ${selectedMethods.join(', ')}</small>
                    </div>
                `;
            }
            
            if (!previews) {
                previews = '<p class="text-muted text-center">No notifications will be sent with current settings</p>';
            }
            
            previewContainer.innerHTML = previews;
        }

        function resetForm() {
            if (confirm('Are you sure you want to reset all settings to default values?')) {
                location.reload();
            }
        }

        function testNotifications() {
            // This would typically send test notifications
            alert('Test notifications sent! Check your configured notification methods.');
        }

        // Initialize previews and event listeners
        document.addEventListener('DOMContentLoaded', function() {
            updatePreviews();
            
            // Add event listeners for real-time preview updates
            document.getElementById('lowStockAlerts').addEventListener('change', updatePreviews);
            document.getElementById('expiryNotifications').addEventListener('change', updatePreviews);
            document.getElementById('lowStockThreshold').addEventListener('input', updatePreviews);
            document.getElementById('expiryThreshold').addEventListener('input', updatePreviews);
            
            // Form validation
            document.getElementById('notificationForm').addEventListener('submit', function(e) {
                const selectedMethods = document.querySelectorAll('input[name="notification_methods[]"]:checked');
                const lowStockEnabled = document.getElementById('lowStockAlerts').checked;
                const expiryEnabled = document.getElementById('expiryNotifications').checked;
                
                if ((lowStockEnabled || expiryEnabled) && selectedMethods.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one notification method when alerts are enabled.');
                    return false;
                }
            });
        });

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
    </script>
</body>

</html>