<?php
session_start();
include "../../config/session_check.php";
include "../../config/config.php";
include "../../config/user_function.php";
include "../../config/notification_functions.php";

// Check if user has admin privileges or settings management permissions
/* if (!isset($_SESSION['user_id']) || (!isAdmin($_SESSION['user_id'], $connect) && !hasPermission($_SESSION['user_id'], 'settings_management', $connect))) {
    header("Location: ../dashboard/");
    exit();
} */

// Handle settings updates
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
        $default_date_range = sanitizeInput($_POST['default_date_range']);
        $auto_generation = isset($_POST['auto_generation']) ? 1 : 0;
        $auto_frequency = sanitizeInput($_POST['auto_frequency']);
        $default_format = sanitizeInput($_POST['default_format']);
        $email_delivery = isset($_POST['email_delivery']) ? 1 : 0;
        $delivery_emails = sanitizeInput($_POST['delivery_emails']);
        $include_charts = isset($_POST['include_charts']) ? 1 : 0;
        $include_summary = isset($_POST['include_summary']) ? 1 : 0;
        $low_stock_threshold = intval($_POST['low_stock_threshold']);
        $expiry_alert_days = intval($_POST['expiry_alert_days']);

        try {
            // Check if settings exist
            $check_query = "SELECT COUNT(*) as count FROM report_settings WHERE user_id = ?";
            $stmt = mysqli_prepare($connect, $check_query);
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $exists = mysqli_fetch_assoc($result)['count'] > 0;

            if ($exists) {
                // Update existing settings
                $update_query = "UPDATE report_settings SET 
                    default_date_range = ?, 
                    auto_generation = ?, 
                    auto_frequency = ?, 
                    default_format = ?, 
                    email_delivery = ?, 
                    delivery_emails = ?, 
                    include_charts = ?, 
                    include_summary = ?, 
                    low_stock_threshold = ?, 
                    expiry_alert_days = ?, 
                    updated_at = NOW() 
                    WHERE user_id = ?";
                
                $stmt = mysqli_prepare($connect, $update_query);
                mysqli_stmt_bind_param($stmt, "siississiii", 
                    $default_date_range, $auto_generation, $auto_frequency, $default_format, 
                    $email_delivery, $delivery_emails, $include_charts, $include_summary, 
                    $low_stock_threshold, $expiry_alert_days, $_SESSION['user_id']);
            } else {
                // Insert new settings
                $insert_query = "INSERT INTO report_settings 
                    (user_id, default_date_range, auto_generation, auto_frequency, default_format, 
                     email_delivery, delivery_emails, include_charts, include_summary, 
                     low_stock_threshold, expiry_alert_days, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = mysqli_prepare($connect, $insert_query);
                mysqli_stmt_bind_param($stmt, "isisissiiii", 
                    $_SESSION['user_id'], $default_date_range, $auto_generation, $auto_frequency, 
                    $default_format, $email_delivery, $delivery_emails, $include_charts, 
                    $include_summary, $low_stock_threshold, $expiry_alert_days);
            }

            if (mysqli_stmt_execute($stmt)) {
                // Log the activity
                /* logUserActivity(
                    $_SESSION['user_id'],
                    'REPORT_SETTINGS_UPDATED',
                    'report_settings',
                    $_SESSION['user_id'],
                    null,
                    [
                        'default_date_range' => $default_date_range,
                        'auto_generation' => $auto_generation,
                        'default_format' => $default_format
                    ],
                    $connect
                ); */

                $message = "Report settings updated successfully!";
            } else {
                $error = "Error updating settings: " . mysqli_error($connect);
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Fetch current settings
$user_id = $_SESSION['user_id'];
$settings_query = "SELECT * FROM report_settings WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($connect, $settings_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$settings_result = mysqli_stmt_get_result($stmt);
$current_settings = mysqli_fetch_assoc($settings_result);

// Default values if no settings exist
if (!$current_settings) {
    $current_settings = [
        'default_date_range' => 'last_7_days',
        'auto_generation' => 0,
        'auto_frequency' => 'weekly',
        'default_format' => 'PDF',
        'email_delivery' => 0,
        'delivery_emails' => '',
        'include_charts' => 1,
        'include_summary' => 1,
        'low_stock_threshold' => 10,
        'expiry_alert_days' => 30
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Settings - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/css/style.css">

    <?php echo getNotificationDropdownCSS(); ?>

        <style>
        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .form-check-input:checked {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }

        .content-card {
            transition: all 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        #frequencySection, #emailSection {
            transition: opacity 0.3s ease;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .icon-box {
                width: 40px;
                height: 40px;
                font-size: 1rem;
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
                    <!-- <a href="../inventory/updateproduct.php" class="nav-link"><i class="fas fa-edit"></i> Update Inventory</a> -->
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
                    <a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="#" class="nav-link active"><i class="fas fa-file-cog"></i> Report Settings</a>
                    <a href="system_preferences.php" class="nav-link"><i class="fas fa-sliders-h"></i> System Preferences</a>
                    <!-- <a href="inventory_settings.php" class="nav-link"><i class="fas fa-box-open"></i> Inventory Settings</a> -->
                </div>

                <!-- Help/Support -->
                <!-- <a href="#" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help/Support
                </a> -->

                <!-- Logout -->
                <a href="../../logout/" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div>
                    
                </div>
                <div class="user-section">
                    <?php echo generateNotificationDropdown($user_id, $connect); ?>
                    <div class="user-info ms-3">
                        <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['firstname']); ?></span>
                        <small class="d-block text-muted"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></small>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Report Settings</h3>
                        <p class="mb-3">Configure default report preferences and automation settings.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light rounded-pill px-4" onclick="resetToDefaults()">
                            <i class="fas fa-refresh me-2"></i>Reset to Defaults
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

            <form method="POST" id="settingsForm">
                <input type="hidden" name="action" value="update_settings">

                <!-- General Report Settings -->
                <div class="content-card mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-primary text-white me-3">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">General Report Settings</h5>
                            <p class="text-muted mb-0">Configure default report preferences</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Date Range</label>
                            <select class="form-select" name="default_date_range" required>
                                <option value="last_7_days" <?php echo $current_settings['default_date_range'] === 'last_7_days' ? 'selected' : ''; ?>>Last 7 Days</option>
                                <option value="last_30_days" <?php echo $current_settings['default_date_range'] === 'last_30_days' ? 'selected' : ''; ?>>Last 30 Days</option>
                                <option value="this_month" <?php echo $current_settings['default_date_range'] === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                                <option value="last_month" <?php echo $current_settings['default_date_range'] === 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                                <option value="this_quarter" <?php echo $current_settings['default_date_range'] === 'this_quarter' ? 'selected' : ''; ?>>This Quarter</option>
                                <option value="this_year" <?php echo $current_settings['default_date_range'] === 'this_year' ? 'selected' : ''; ?>>This Year</option>
                            </select>
                            <div class="form-text">Default time period for new reports</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Report Format</label>
                            <select class="form-select" name="default_format" required>
                                <option value="PDF" <?php echo $current_settings['default_format'] === 'PDF' ? 'selected' : ''; ?>>PDF</option>
                                <option value="Excel" <?php echo $current_settings['default_format'] === 'Excel' ? 'selected' : ''; ?>>Excel</option>
                                <option value="CSV" <?php echo $current_settings['default_format'] === 'CSV' ? 'selected' : ''; ?>>CSV</option>
                            </select>
                            <div class="form-text">Preferred format for generated reports</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="include_charts" id="includeCharts" <?php echo $current_settings['include_charts'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="includeCharts">
                                    Include Charts and Graphs
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="include_summary" id="includeSummary" <?php echo $current_settings['include_summary'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="includeSummary">
                                    Include Executive Summary
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Automatic Report Generation -->
                <div class="content-card mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-success text-white me-3">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Automatic Report Generation</h5>
                            <p class="text-muted mb-0">Configure automated report scheduling</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="auto_generation" id="autoGeneration" <?php echo $current_settings['auto_generation'] ? 'checked' : ''; ?> onchange="toggleAutoSettings()">
                                <label class="form-check-label" for="autoGeneration">
                                    Enable Automatic Report Generation
                                </label>
                            </div>
                            <div class="form-text">Automatically generate reports at scheduled intervals</div>
                        </div>

                        <div class="col-md-6 mb-3" id="frequencySection">
                            <label class="form-label">Generation Frequency</label>
                            <select class="form-select" name="auto_frequency">
                                <option value="daily" <?php echo $current_settings['auto_frequency'] === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="weekly" <?php echo $current_settings['auto_frequency'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                <option value="monthly" <?php echo $current_settings['auto_frequency'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                <option value="quarterly" <?php echo $current_settings['auto_frequency'] === 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Email Delivery Settings -->
                <div class="content-card mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-info text-white me-3">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Email Delivery Settings</h5>
                            <p class="text-muted mb-0">Configure automatic email delivery of reports</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="email_delivery" id="emailDelivery" <?php echo $current_settings['email_delivery'] ? 'checked' : ''; ?> onchange="toggleEmailSettings()">
                                <label class="form-check-label" for="emailDelivery">
                                    Enable Email Delivery
                                </label>
                            </div>
                            <div class="form-text">Automatically email reports to specified recipients</div>
                        </div>

                        <div class="col-md-6 mb-3" id="emailSection">
                            <label class="form-label">Delivery Email Addresses</label>
                            <textarea class="form-control" name="delivery_emails" rows="3" placeholder="Enter email addresses separated by commas"><?php echo htmlspecialchars($current_settings['delivery_emails']); ?></textarea>
                            <div class="form-text">Separate multiple emails with commas</div>
                        </div>
                    </div>
                </div>

                <!-- Alert Thresholds -->
                <div class="content-card mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box bg-warning text-white me-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Alert Thresholds</h5>
                            <p class="text-muted mb-0">Configure alert thresholds for reports</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Low Stock Threshold</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="low_stock_threshold" value="<?php echo $current_settings['low_stock_threshold']; ?>" min="1" required>
                                <span class="input-group-text">units</span>
                            </div>
                            <div class="form-text">Alert when product quantity falls below this number</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Alert Period</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="expiry_alert_days" value="<?php echo $current_settings['expiry_alert_days']; ?>" min="1" required>
                                <span class="input-group-text">days</span>
                            </div>
                            <div class="form-text">Alert for products expiring within this timeframe</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-0">Changes will be applied immediately and affect future report generation.</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="window.location.reload()">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
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

        function toggleAutoSettings() {
            const checkbox = document.getElementById('autoGeneration');
            const frequencySection = document.getElementById('frequencySection');
            
            if (checkbox.checked) {
                frequencySection.style.opacity = '1';
                frequencySection.querySelector('select').required = true;
            } else {
                frequencySection.style.opacity = '0.5';
                frequencySection.querySelector('select').required = false;
            }
        }

        function toggleEmailSettings() {
            const checkbox = document.getElementById('emailDelivery');
            const emailSection = document.getElementById('emailSection');
            
            if (checkbox.checked) {
                emailSection.style.opacity = '1';
                emailSection.querySelector('textarea').required = true;
            } else {
                emailSection.style.opacity = '0.5';
                emailSection.querySelector('textarea').required = false;
            }
        }

        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all settings to their default values?')) {
                // Reset form to default values
                document.querySelector('select[name="default_date_range"]').value = 'last_7_days';
                document.querySelector('select[name="default_format"]').value = 'PDF';
                document.querySelector('select[name="auto_frequency"]').value = 'weekly';
                document.getElementById('autoGeneration').checked = false;
                document.getElementById('emailDelivery').checked = false;
                document.getElementById('includeCharts').checked = true;
                document.getElementById('includeSummary').checked = true;
                document.querySelector('input[name="low_stock_threshold"]').value = 10;
                document.querySelector('input[name="expiry_alert_days"]').value = 30;
                document.querySelector('textarea[name="delivery_emails"]').value = '';
                
                // Toggle dependent sections
                toggleAutoSettings();
                toggleEmailSettings();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleAutoSettings();
            toggleEmailSettings();
        });

        // Form validation
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const emailDelivery = document.getElementById('emailDelivery').checked;
            const deliveryEmails = document.querySelector('textarea[name="delivery_emails"]').value.trim();
            
            if (emailDelivery && !deliveryEmails) {
                e.preventDefault();
                alert('Please enter at least one email address for delivery.');
                return false;
            }
            
            // Validate email format if emails are provided
            if (deliveryEmails) {
                const emails = deliveryEmails.split(',').map(email => email.trim());
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                for (let email of emails) {
                    if (!emailRegex.test(email)) {
                        e.preventDefault();
                        alert('Please enter valid email addresses.');
                        return false;
                    }
                }
            }
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