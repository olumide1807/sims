<?php
session_start();

include "../config/config.php";
include "../config/session_check.php";
include "../config/notification_functions.php";
include "../config/dashboard_functions.php";

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];

// Get notification data
$notification_count = getNotificationCount($user_id, $connect);
$notifications = getUserNotifications($user_id, $connect, 5); // Get 5 latest notifications
$notification_stats = getNotificationStats(getUserNotificationSettings($user_id, $connect), $connect);

// Get dashboard data
$dashboard_stats = getDashboardStats($connect);
$recent_activities = getRecentActivities($connect, 5);
$active_alerts = getActiveAlerts($connect);
$inventory_data = getInventoryOverviewData($connect, 'weekly');

/* // Check user role and redirect if necessary
if ($_SESSION['role'] !== 'sales_rep') {
    header("Location: ../unauthorized.php");
    exit();
} */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard | Inventory System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">

    <?php echo getNotificationDropdownCSS(); ?>
    <style>
        /* Additional styles for sales rep interface */
        .sales-highlight {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
        }
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .alert-card {
            transition: all 0.3s ease;
        }
        .alert-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                    SIMS Sales
                </h4>
            </div>

            <nav>
                <!-- Dashboard -->
                <a href="#" class="nav-link active">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <!-- Inventory Management -->
                <a href="inventory.php" class="nav-link">
                    <i class="fas fa-box"></i> View Inventory
                </a>

                <!-- Sales Management -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-shopping-cart"></i> Sales
                </a>
                <div class="submenu" id="sales">
                    <a href="logsales.php" class="nav-link"><i class="fas fa-cash-register"></i> New Sale</a>
                    <a href="viewsales.php" class="nav-link"><i class="fas fa-chart-bar"></i> Sales History</a>
                </div>

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
                <div>
                    <!-- <i class="fas fa-search text-muted me-2"></i>
                    <input type="text" placeholder="Search..."> -->
                </div>
                <div class="user-section">
                    <?php echo generateNotificationDropdown($user_id, $connect); ?>
                    <div class="user-info ms-3">
                        <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['firstname']); ?></span>
                        <small class="d-block text-muted"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></small>
                    </div>
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="welcome-section sales-highlight">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</h3>
                        <p class="mb-3">Ready to assist customers today? Here's what you need to know.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="quick-actions">
                            <a href="../sales/logsales.php" class="btn btn-light rounded-pill px-4">
                                <i class="fas fa-cash-register me-2"></i>New Sale
                            </a>
                            <a href="../inventory/" class="btn btn-outline-light rounded-pill px-4">
                                <i class="fas fa-search me-2"></i>Check Stock
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 class="mb-1"><?php echo formatNumber($dashboard_stats['total_products']); ?></h3>
                    <p class="text-muted mb-0">Total Products</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="mb-1"><?php echo formatCurrency($dashboard_stats['monthly_revenue']); ?></h3>
                    <p class="text-muted mb-0">Monthly Revenue</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="mb-1"><?php echo formatNumber($dashboard_stats['low_stock_items']); ?></h3>
                    <p class="text-muted mb-0">Low Stock Items</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="mb-1"><?php echo formatNumber($dashboard_stats['pending_orders']); ?></h3>
                    <p class="text-muted mb-0">Pending Orders</p>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Stock Alerts -->
                <div class="content-card alert-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Stock Alerts</h5>
                        <a href="../inventory/" class="btn btn-link text-decoration-none">View All</a>
                    </div>
                    <div id="stock-alerts-container">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Loading stock alerts...</strong>
                        </div>
                    </div>
                </div>

                <!-- Quick Sale -->
                <div class="content-card">
                    <h5 class="mb-4">Check Product Availability</h5>
                    <form id="quick-sale-form">
                        <div class="mb-3">
                            <!-- <label class="form-label">Product Search</label> -->
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Product search..." id="product-search">
                                <!-- <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button> -->
                            </div>
                        </div>
                        <div id="sale-items-container">
                            <!-- Sale items will be added here -->
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Search Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="content-card mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Recent Sales</h5>
                    <a href="../sales/viewsales.php" class="btn btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Time</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="recent-sales">
                            <tr>
                                <td colspan="5" class="text-center">Loading recent sales...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
    <script>
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            if (submenu) {
                submenu.classList.toggle('show');
            }
        }

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

        // Simulate loading data (in a real app, you'd use AJAX)
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate API calls with setTimeout
            setTimeout(() => {
                // Update stats
                document.getElementById('total-products').textContent = '1,842';
                document.getElementById('today-sales').textContent = '$2,450';
                document.getElementById('low-stock').textContent = '12';
                document.getElementById('expiring-soon').textContent = '5';
                document.getElementById('alert-count').textContent = '3';

                // Update stock alerts
                const alertsContainer = document.getElementById('stock-alerts-container');
                alertsContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Low Stock:</strong> Product A - Only 3 remaining
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Low Stock:</strong> Product B - Only 2 remaining
                    </div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Expiring Soon:</strong> Product C - Expires in 3 days
                    </div>
                `;

                // Update recent sales
                const recentSales = document.getElementById('recent-sales');
                recentSales.innerHTML = `
                    <tr>
                        <td>#TRX-1001</td>
                        <td>Today, 10:30 AM</td>
                        <td>3 items</td>
                        <td>$245.00</td>
                        <td><a href="#" class="btn btn-sm btn-outline-primary">Details</a></td>
                    </tr>
                    <tr>
                        <td>#TRX-1000</td>
                        <td>Today, 9:15 AM</td>
                        <td>1 item</td>
                        <td>$89.99</td>
                        <td><a href="#" class="btn btn-sm btn-outline-primary">Details</a></td>
                    </tr>
                    <tr>
                        <td>#TRX-999</td>
                        <td>Yesterday, 4:45 PM</td>
                        <td>5 items</td>
                        <td>$412.50</td>
                        <td><a href="#" class="btn btn-sm btn-outline-primary">Details</a></td>
                    </tr>
                `;
            }, 1000);
        });
    </script>
</body>

</html>