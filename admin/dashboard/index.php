<?php
session_start();

include "../../config/session_check.php";
include "../../config/config.php";
include "../../config/notification_functions.php";
include "dashboard_functions.php";

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];

// Get notification data
$notification_count = getNotificationCount($user_id, $connect);
$notifications = getUserNotifications($user_id, $connect, 5);
$notification_stats = getNotificationStats(getUserNotificationSettings($user_id, $connect), $connect);

// Get dashboard data
$dashboard_stats = getDashboardStats($connect);
$recent_activities = getRecentActivities($connect, 5);
$active_alerts = getActiveAlerts($connect);
$inventory_data = getInventoryOverviewData($connect, 'weekly');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Inventory Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <?php echo getNotificationDropdownCSS(); ?>
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
                <a href="#" class="nav-link active" onclick="toggleSubmenu('dashboard')">
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

                <!-- Reports -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
                <div class="submenu" id="reports">
                    <a href="../report/generateReport.php" class="nav-link"><i class="fas fa-file-export"></i> Generate Reports</a>
                    <a href="../report/viewreport.php" class="nav-link"><i class="fas fa-file-import"></i> View Reports</a>
                </div>

                <!-- Settings -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('settings')">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="submenu" id="settings">
                    <a href="../settings/manage_users.php" class="nav-link"><i class="fas fa-users"></i> User Management</a>
                    <a href="../settings/notifications.php" class="nav-link"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="../settings/reports_settings.php" class="nav-link"><i class="fas fa-file-cog"></i> Report Settings</a>
                    <a href="../settings/system_preferences.php" class="nav-link"><i class="fas fa-sliders-h"></i> System Preferences</a>
                    <!-- <a href="../settings/inventory_settings.php" class="nav-link"><i class="fas fa-box-open"></i> Inventory Settings</a> -->
                </div>

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
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Welcome back, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</h3>
                        <p class="mb-3">Here's what's happening with your inventory today.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="../inventory/addproduct.php">
                            <button class="btn btn-light rounded-pill px-4 mb-2 mb-md-0">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </button>
                        </a>
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
                <!-- Recent Activities -->
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Recent Activities</h5>
                        <button class="btn btn-link text-decoration-none">View All</button>
                    </div>
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['activity_type']); ?></h6>
                                    <p class="text-muted mb-0 small"><?php echo htmlspecialchars($activity['description']); ?></p>
                                </div>
                                <span class="badge bg-<?php echo $activity['badge_type']; ?>-subtle text-<?php echo $activity['badge_type']; ?>">
                                    <?php echo getTimeAgo($activity['activity_date']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-3"></i>
                            <p>No recent activities</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Alerts Section -->
                <div class="content-card">
                    <h5 class="mb-4">Active Alerts</h5>
                    <?php foreach ($active_alerts as $alert): ?>
                    <div class="alert bg-<?php echo $alert['type']; ?>-subtle">
                        <i class="<?php echo $alert['icon']; ?> text-<?php echo $alert['type']; ?>"></i>
                        <div>
                            <strong><?php echo htmlspecialchars($alert['title']); ?></strong>
                            <p class="mb-0 small"><?php echo htmlspecialchars($alert['message']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Sales Overview</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary chart-period-btn active" data-period="weekly">Weekly</button>
                        <button class="btn btn-outline-primary chart-period-btn" data-period="monthly">Monthly</button>
                        <button class="btn btn-outline-primary chart-period-btn" data-period="yearly">Yearly</button>
                    </div>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
    <script>
        let salesChart;
        
        // Initialize chart with data from PHP
        const initialData = <?php echo json_encode($inventory_data); ?>;
        
        function initChart(data) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            if (salesChart) {
                salesChart.destroy();
            }
            
            const labels = data.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric' 
                });
            });
            
            const revenues = data.map(item => item.revenue);
            const salesCounts = data.map(item => item.sales_count);
            
            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: revenues,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Sales Count',
                        data: salesCounts,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Revenue ($)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Sales Count'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        }
        
        // Initialize chart on page load
        document.addEventListener('DOMContentLoaded', function() {
            initChart(initialData);
        });
        
        // Handle period change
        document.querySelectorAll('.chart-period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const period = this.dataset.period;
                
                // Update active button
                document.querySelectorAll('.chart-period-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Fetch new data
                fetch(`get_chart_data.php?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        initChart(data);
                    })
                    .catch(error => {
                        console.error('Error fetching chart data:', error);
                    });
            });
        });
        
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