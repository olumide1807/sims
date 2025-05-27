<?php
session_start();
include "../config/session_check.php";
include "../config/config.php";
include "../config/user_function.php";

/* // Check if user has admin privileges or system management permissions
if (!isset($_SESSION['user_id']) || (!isAdmin($_SESSION['user_id'], $connect) && !hasPermission($_SESSION['user_id'], 'system_management', $connect))) {
    header("Location: ../dashboard/");
    exit();
} */

// Handle system preference updates
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_preferences') {
        $currency = sanitizeInput($_POST['currency']);
        $timezone = sanitizeInput($_POST['timezone']);
        $date_format = sanitizeInput($_POST['date_format']);
        $currency_symbol = sanitizeInput($_POST['currency_symbol']);
        $currency_position = sanitizeInput($_POST['currency_position']);
        $decimal_places = (int)$_POST['decimal_places'];
        $thousand_separator = sanitizeInput($_POST['thousand_separator']);
        $decimal_separator = sanitizeInput($_POST['decimal_separator']);

        // Validate inputs
        if (empty($currency) || empty($timezone) || empty($date_format)) {
            $error = "Please fill in all required fields!";
        } else {
            // Check if system preferences table exists, if not create it
            $check_table = "SHOW TABLES LIKE 'system_preferences'";
            $table_exists = mysqli_query($connect, $check_table);

            if (mysqli_num_rows($table_exists) == 0) {
                echo $table_exists; die();
                $create_table = "CREATE TABLE system_preferences (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_name VARCHAR(50) UNIQUE NOT NULL,
                    setting_value TEXT NOT NULL,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    updated_by INT,
                    FOREIGN KEY (updated_by) REFERENCES users(user_id)
                )";
                mysqli_query($connect, $create_table);
            }

            // Update or insert system preferences
            $preferences = [
                'currency' => $currency,
                'currency_symbol' => $currency_symbol,
                'currency_position' => $currency_position,
                'timezone' => $timezone,
                'date_format' => $date_format,
                'decimal_places' => $decimal_places,
                'thousand_separator' => $thousand_separator,
                'decimal_separator' => $decimal_separator
            ];

            $success = true;
            foreach ($preferences as $setting_name => $setting_value) {
                $stmt = mysqli_prepare($connect, "INSERT INTO system_preferences (setting_name, setting_value, updated_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?, updated_at = NOW()");
                mysqli_stmt_bind_param($stmt, "ssisi", $setting_name, $setting_value, $_SESSION['user_id'], $setting_value, $_SESSION['user_id']);

                if (!mysqli_stmt_execute($stmt)) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                // Log the activity
                logUserActivity(
                    $_SESSION['user_id'],
                    'SYSTEM_PREFERENCES_UPDATED',
                    'system_preferences',
                    null,
                    null,
                    $preferences,
                    $connect
                );

                $message = "System preferences updated successfully!";
            } else {
                $error = "Error updating system preferences: " . mysqli_error($connect);
            }
        }
    }
}

// Fetch current system preferences
function getSystemPreference($setting_name, $default_value, $connect)
{
    $stmt = mysqli_prepare($connect, "SELECT setting_value FROM system_preferences WHERE setting_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $setting_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        return $row['setting_value'];
    }
    return $default_value;
}

$current_currency = getSystemPreference('currency', 'USD', $connect);
$current_currency_symbol = getSystemPreference('currency_symbol', '$', $connect);
$current_currency_position = getSystemPreference('currency_position', 'before', $connect);
$current_timezone = getSystemPreference('timezone', 'UTC', $connect);
$current_date_format = getSystemPreference('date_format', 'Y-m-d', $connect);
$current_decimal_places = getSystemPreference('decimal_places', '2', $connect);
$current_thousand_separator = getSystemPreference('thousand_separator', ',', $connect);
$current_decimal_separator = getSystemPreference('decimal_separator', '.', $connect);

// Currency options
$currencies = [
    'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
    'EUR' => ['name' => 'Euro', 'symbol' => '€'],
    'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
    'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
    'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$'],
    'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$'],
    'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF'],
    'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
    'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦'],
    'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
    'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
    'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$']
];

// Timezone options (major timezones)
$timezones = [
    'UTC' => 'UTC (Coordinated Universal Time)',
    'America/New_York' => 'Eastern Time (US & Canada)',
    'America/Chicago' => 'Central Time (US & Canada)',
    'America/Denver' => 'Mountain Time (US & Canada)',
    'America/Los_Angeles' => 'Pacific Time (US & Canada)',
    'Europe/London' => 'London (GMT/BST)',
    'Europe/Paris' => 'Paris (CET/CEST)',
    'Europe/Berlin' => 'Berlin (CET/CEST)',
    'Asia/Tokyo' => 'Tokyo (JST)',
    'Asia/Shanghai' => 'Shanghai (CST)',
    'Asia/Kolkata' => 'Kolkata (IST)',
    'Australia/Sydney' => 'Sydney (AEST/AEDT)',
    'Africa/Lagos' => 'Lagos (WAT)',
    'Africa/Cairo' => 'Cairo (EET)',
    'America/Sao_Paulo' => 'São Paulo (BRT/BRST)'
];

// Date format options
$date_formats = [
    'Y-m-d' => 'YYYY-MM-DD (2024-01-15)',
    'm/d/Y' => 'MM/DD/YYYY (01/15/2024)',
    'd/m/Y' => 'DD/MM/YYYY (15/01/2024)',
    'd-m-Y' => 'DD-MM-YYYY (15-01-2024)',
    'M d, Y' => 'Mon DD, YYYY (Jan 15, 2024)',
    'F j, Y' => 'Month DD, YYYY (January 15, 2024)',
    'j F Y' => 'DD Month YYYY (15 January 2024)'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Preferences - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">
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
                    <a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="reports_settings.php" class="nav-link"><i class="fas fa-file-cog"></i> Report Settings</a>
                    <a href="#" class="nav-link active"><i class="fas fa-sliders-h"></i> System Preferences</a>
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
                        <span class="badge rounded-pill bg-danger">3</span>
                    </div>
                    <img src="/placeholder.svg?height=40&width=40" class="rounded-circle" alt="User avatar">
                </div>
            </div>

            <!-- Page Header -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>System Preferences</h3>
                        <p class="mb-3">Configure global system settings including currency, timezone, and date formats.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light rounded-pill px-4" onclick="resetToDefaults()">
                            <i class="fas fa-undo me-2"></i>Reset to Defaults
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

            <!-- System Preferences Form -->
            <form method="POST" id="preferencesForm">
                <input type="hidden" name="action" value="update_preferences">

                <!-- Currency Settings -->
                <div class="content-card mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="setting-icon me-3">
                            <i class="fas fa-dollar-sign text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Currency Settings</h5>
                            <p class="text-muted mb-0">Configure how currency is displayed throughout the system</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Primary Currency</label>
                            <select class="form-select" name="currency" id="currencySelect" onchange="updateCurrencySymbol()">
                                <?php foreach ($currencies as $code => $info): ?>
                                    <option value="<?php echo $code; ?>" <?php echo $current_currency === $code ? 'selected' : ''; ?>>
                                        <?php echo $code . ' - ' . $info['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" class="form-control" name="currency_symbol" id="currencySymbol"
                                value="<?php echo htmlspecialchars($current_currency_symbol); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Symbol Position</label>
                            <select class="form-select" name="currency_position">
                                <option value="before" <?php echo $current_currency_position === 'before' ? 'selected' : ''; ?>>Before amount ($100.00)</option>
                                <option value="after" <?php echo $current_currency_position === 'after' ? 'selected' : ''; ?>>After amount (100.00$)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Decimal Places</label>
                            <select class="form-select" name="decimal_places">
                                <option value="0" <?php echo $current_decimal_places === '0' ? 'selected' : ''; ?>>0 (100)</option>
                                <option value="1" <?php echo $current_decimal_places === '1' ? 'selected' : ''; ?>>1 (100.0)</option>
                                <option value="2" <?php echo $current_decimal_places === '2' ? 'selected' : ''; ?>>2 (100.00)</option>
                                <option value="3" <?php echo $current_decimal_places === '3' ? 'selected' : ''; ?>>3 (100.000)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Thousand Separator</label>
                            <select class="form-select" name="thousand_separator">
                                <option value="," <?php echo $current_thousand_separator === ',' ? 'selected' : ''; ?>>Comma (1,000)</option>
                                <option value="." <?php echo $current_thousand_separator === '.' ? 'selected' : ''; ?>>Period (1.000)</option>
                                <option value=" " <?php echo $current_thousand_separator === ' ' ? 'selected' : ''; ?>>Space (1 000)</option>
                                <option value="" <?php echo $current_thousand_separator === '' ? 'selected' : ''; ?>>None (1000)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Decimal Separator</label>
                            <select class="form-select" name="decimal_separator">
                                <option value="." <?php echo $current_decimal_separator === '.' ? 'selected' : ''; ?>>Period (100.50)</option>
                                <option value="," <?php echo $current_decimal_separator === ',' ? 'selected' : ''; ?>>Comma (100,50)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Currency Preview -->
                    <div class="mt-3">
                        <label class="form-label">Preview</label>
                        <div class="form-control bg-light" id="currencyPreview">$1,234.56</div>
                    </div>
                </div>

                <!-- Timezone Settings -->
                <div class="content-card mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="setting-icon me-3">
                            <i class="fas fa-globe text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Timezone Settings</h5>
                            <p class="text-muted mb-0">Set the default timezone for the system</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">System Timezone</label>
                            <select class="form-select" name="timezone" id="timezoneSelect" onchange="updateTimePreview()">
                                <?php foreach ($timezones as $tz => $label): ?>
                                    <option value="<?php echo $tz; ?>" <?php echo $current_timezone === $tz ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Time</label>
                            <div class="form-control bg-light" id="timePreview">
                                <?php
                                date_default_timezone_set($current_timezone);
                                echo date('H:i:s T');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Format Settings -->
                <div class="content-card mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="setting-icon me-3">
                            <i class="fas fa-calendar text-warning"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Date Format Settings</h5>
                            <p class="text-muted mb-0">Configure how dates are displayed throughout the system</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Date Format</label>
                            <select class="form-select" name="date_format" id="dateFormatSelect" onchange="updateDatePreview()">
                                <?php foreach ($date_formats as $format => $label): ?>
                                    <option value="<?php echo $format; ?>" <?php echo $current_date_format === $format ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Preview</label>
                            <div class="form-control bg-light" id="datePreview">
                                <?php echo date($current_date_format); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Changes will take effect immediately and apply to all users.
                            </small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                <i class="fas fa-undo me-1"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Preferences
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Currency data for JavaScript
        const currencies = <?php echo json_encode($currencies); ?>;

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

        function updateCurrencySymbol() {
            const currencySelect = document.getElementById('currencySelect');
            const currencySymbol = document.getElementById('currencySymbol');
            const selectedCurrency = currencySelect.value;

            if (currencies[selectedCurrency]) {
                currencySymbol.value = currencies[selectedCurrency].symbol;
            }
            updateCurrencyPreview();
        }

        function updateCurrencyPreview() {
            const symbol = document.querySelector('input[name="currency_symbol"]').value;
            const position = document.querySelector('select[name="currency_position"]').value;
            const decimalPlaces = parseInt(document.querySelector('select[name="decimal_places"]').value);
            const thousandSeparator = document.querySelector('select[name="thousand_separator"]').value;
            const decimalSeparator = document.querySelector('select[name="decimal_separator"]').value;

            let amount = '1234.56';

            // Format the number based on settings
            let parts = amount.split('.');
            let wholePart = parts[0];
            let decimalPart = parts[1] || '';

            // Add thousand separators
            if (thousandSeparator) {
                wholePart = wholePart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
            }

            // Handle decimal places
            if (decimalPlaces > 0) {
                decimalPart = decimalPart.padEnd(decimalPlaces, '0').substring(0, decimalPlaces);
                amount = wholePart + decimalSeparator + decimalPart;
            } else {
                amount = wholePart;
            }

            // Add currency symbol
            const preview = position === 'before' ? symbol + amount : amount + symbol;
            document.getElementById('currencyPreview').textContent = preview;
        }

        function updateTimePreview() {
            // This would require server-side update for accurate timezone display
            // For now, we'll show a placeholder
            const timezone = document.getElementById('timezoneSelect').value;
            document.getElementById('timePreview').textContent = 'Updating...';

            // You could make an AJAX call here to get current time in selected timezone
        }

        function updateDatePreview() {
            const format = document.getElementById('dateFormatSelect').value;
            const today = new Date();

            let preview = '';
            switch (format) {
                case 'Y-m-d':
                    preview = today.getFullYear() + '-' +
                        String(today.getMonth() + 1).padStart(2, '0') + '-' +
                        String(today.getDate()).padStart(2, '0');
                    break;
                case 'm/d/Y':
                    preview = String(today.getMonth() + 1).padStart(2, '0') + '/' +
                        String(today.getDate()).padStart(2, '0') + '/' +
                        today.getFullYear();
                    break;
                case 'd/m/Y':
                    preview = String(today.getDate()).padStart(2, '0') + '/' +
                        String(today.getMonth() + 1).padStart(2, '0') + '/' +
                        today.getFullYear();
                    break;
                case 'd-m-Y':
                    preview = String(today.getDate()).padStart(2, '0') + '-' +
                        String(today.getMonth() + 1).padStart(2, '0') + '-' +
                        today.getFullYear();
                    break;
                case 'M d, Y':
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                    ];
                    preview = months[today.getMonth()] + ' ' + today.getDate() + ', ' + today.getFullYear();
                    break;
                case 'F j, Y':
                    const fullMonths = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];
                    preview = fullMonths[today.getMonth()] + ' ' + today.getDate() + ', ' + today.getFullYear();
                    break;
                case 'j F Y':
                    const fullMonths2 = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];
                    preview = today.getDate() + ' ' + fullMonths2[today.getMonth()] + ' ' + today.getFullYear();
                    break;
                default:
                    preview = today.toLocaleDateString();
            }

            document.getElementById('datePreview').textContent = preview;
        }

        function resetForm() {
            if (confirm('Are you sure you want to reset all changes? This will restore the form to its current saved state.')) {
                location.reload();
            }
        }

        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
                // Set default values
                document.querySelector('select[name="currency"]').value = 'USD';
                document.querySelector('input[name="currency_symbol"]').value = '$';
                document.querySelector('select[name="currency_position"]').value = 'before';
                document.querySelector('select[name="decimal_places"]').value = '2';
                document.querySelector('select[name="thousand_separator"]').value = ',';
                document.querySelector('select[name="decimal_separator"]').value = '.';
                document.querySelector('select[name="timezone"]').value = 'UTC';
                document.querySelector('select[name="date_format"]').value = 'Y-m-d';

                // Update previews
                updateCurrencySymbol();
                updateCurrencyPreview();
                updateDatePreview();

                // Show confirmation
                alert('Settings have been reset to default values. Click "Save Preferences" to apply these changes.');
            }
        }

        // Initialize previews on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCurrencyPreview();
            updateDatePreview();

            // Auto-save notification
            let formChanged = false;
            const form = document.getElementById('preferencesForm');
            const inputs = form.querySelectorAll('input, select');

            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    formChanged = true;
                });
            });

            // Warn user before leaving if form has unsaved changes
            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return e.returnValue;
                }
            });

            // Reset form change flag when form is submitted
            form.addEventListener('submit', function() {
                formChanged = false;
            });
        });

        // Search functionality for settings
        document.querySelector('.search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const contentCards = document.querySelectorAll('.content-card');

            contentCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                if (cardText.includes(searchTerm) || searchTerm === '') {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('preferencesForm').submit();
            }

            // Ctrl/Cmd + R to reset
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                resetForm();
            }
        });

        // Form validation
        document.getElementById('preferencesForm').addEventListener('submit', function(e) {
            const requiredFields = ['currency', 'timezone', 'date_format', 'currency_symbol'];
            let isValid = true;

            requiredFields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');

                    // Remove invalid class after user starts typing
                    field.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, {
                        once: true
                    });
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        // Real-time currency preview updates
        document.querySelector('input[name="currency_symbol"]').addEventListener('input', updateCurrencyPreview);
        document.querySelector('select[name="currency_position"]').addEventListener('change', updateCurrencyPreview);
        document.querySelector('select[name="decimal_places"]').addEventListener('change', updateCurrencyPreview);
        document.querySelector('select[name="thousand_separator"]').addEventListener('change', updateCurrencyPreview);
        document.querySelector('select[name="decimal_separator"]').addEventListener('change', updateCurrencyPreview);

        // Success animation for form submission
        function showSuccessAnimation() {
            const button = document.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;

            button.innerHTML = '<i class="fas fa-check me-1"></i>Saved!';
            button.classList.add('btn-success');
            button.classList.remove('btn-primary');

            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
            }, 2000);
        }

        // Handle form submission success (if page doesn't reload)
        <?php if ($message): ?>
            showSuccessAnimation();
        <?php endif; ?>
    </script>

    <style>
        .setting-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(var(--bs-primary-rgb), 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
        }

        .is-invalid {
            border-color: var(--bs-danger) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-danger-rgb), 0.25) !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--bs-primary), #0056b3);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.4);
        }

        .search-box input:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
        }

        @media (max-width: 768px) {
            .content-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }

            .row>div {
                margin-bottom: 1rem;
            }
        }
    </style>
</body>

</html>