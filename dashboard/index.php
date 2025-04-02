<?php
    session_start();

    include "../config/session_check.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Inventory Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">
    <!-- <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --sidebar-width: 295px;
        }

        body {
            background-color: #f3f4f6;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
        }

        .layout-container {
            display: flex;
            gap: 24px;
            padding: 24px;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            border-radius: 16px;
            padding: 24px;
            position: fixed;
            height: calc(100vh - 48px);
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .logo-section {
            padding-bottom: 24px;
            margin-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .nav-link {
            color: #4b5563;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 4px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(4px);
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
        }

        /* Submenu Styles */
        .submenu {
            margin-left: 32px;
            display: none;
        }

        .submenu.show {
            display: block;
        }

        .submenu .nav-link {
            font-size: 0.9rem;
            padding: 8px 16px;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 1001;
            background: white;
            border: none;
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding-left: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .search-box {
            background: white;
            border-radius: 12px;
            padding: 8px 16px;
            width: 300px;
            border: 1px solid #e5e7eb;
        }

        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            padding: 4px;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .notification-badge {
            background: white;
            padding: 8px;
            border-radius: 12px;
            position: relative;
        }

        .badge {
            position: absolute;
            top: -5px;
            right: -5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 1.5rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e5e7eb;
        }

        .activity-item {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .activity-item:hover {
            background-color: #f9fafb;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 12px;
            border: none;
        }

        .chart-container {
            margin-top: 24px;
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e5e7eb;
        }

        .welcome-section {
            background: var(--primary-gradient);
            border-radius: 16px;
            padding: 32px;
            color: white;
            margin-bottom: 24px;
        }

        /* Responsive Styles */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .mobile-menu-toggle {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding-left: 0;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .welcome-section {
                text-align: center;
            }

            .welcome-section .col-md-4 {
                text-align: center;
                margin-top: 16px;
            }
        }
    </style> -->
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
                <a href="#" class="nav-link" onclick="toggleSubmenu('ai-insights')">
                    <i class="fas fa-robot"></i> AI-Powered Insights
                </a>
                <div class="submenu" id="ai-insights">
                    <a href="#" class="nav-link"><i class="fas fa-bell"></i> Reordering Suggestions</a>
                    <a href="#" class="nav-link"><i class="fas fa-clock"></i> Expiration Alerts</a>
                    <a href="#" class="nav-link"><i class="fas fa-chart-line"></i> Low-Demand Products</a>
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
                    <a href="#" class="nav-link"><i class="fas fa-user"></i> User Profile</a>
                    <a href="#" class="nav-link"><i class="fas fa-users-cog"></i> Manage Users</a>
                    <a href="#" class="nav-link"><i class="fas fa-sliders-h"></i> System Settings</a>
                    <a href="#" class="nav-link"><i class="fas fa-bell"></i> Notifications</a>
                </div>

                <!-- Help/Support -->
                <a href="#" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help/Support
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
                    <input type="text" placeholder="Search...">
                </div>
                <div class="user-section">
                    <div class="notification-badge">
                        <i class="fas fa-bell text-muted"></i>
                        <span class="badge rounded-pill bg-danger">3</span>
                    </div>
                    <img src="/placeholder.svg?height=40&width=40" class="rounded-circle" alt="User avatar">
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Welcome back, <?php print_r($_SESSION['firstname']); ?>!</h3>
                        <p class="mb-3">Here's what's happening with your inventory today.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light rounded-pill px-4 mb-2 mb-md-0">
                            <i class="fas fa-plus me-2"></i>Add Product
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 class="mb-1">2,547</h3>
                    <p class="text-muted mb-0">Total Products</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="mb-1">$84,686</h3>
                    <p class="text-muted mb-0">Monthly Revenue</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="mb-1">18</h3>
                    <p class="text-muted mb-0">Low Stock Items</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="mb-1">156</h3>
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
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">New Shipment Arrived</h6>
                                <p class="text-muted mb-0 small">iPhone 15 Pro - 24 units</p>
                            </div>
                            <!-- <span class="badge bg-primary-subtle text-primary">Just Now</span> -->
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Low Stock Alert</h6>
                                <p class="text-muted mb-0 small">Samsung Galaxy S23 - 5 units left</p>
                            </div>
                            <!-- <span class="badge bg-warning-subtle text-warning">2h ago</span> -->
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Bulk Order Completed</h6>
                                <p class="text-muted mb-0 small">MacBook Pro - 50 units</p>
                            </div>
                            <!-- <span class="badge bg-success-subtle text-success">5h ago</span> -->
                        </div>
                    </div>
                </div>

                <!-- Alerts Section -->
                <div class="content-card">
                    <h5 class="mb-4">Active Alerts</h5>
                    <div class="alert bg-warning-subtle">
                        <i class="fas fa-exclamation-circle text-warning"></i>
                        <div>
                            <strong>Low Stock Alert</strong>
                            <p class="mb-0 small">5 products need reordering</p>
                        </div>
                    </div>
                    <div class="alert bg-danger-subtle">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <div>
                            <strong>Expiring Soon</strong>
                            <p class="mb-0 small">3 products expiring in 7 days</p>
                        </div>
                    </div>
                    <div class="alert bg-success-subtle">
                        <i class="fas fa-check-circle text-success"></i>
                        <div>
                            <strong>AI Recommendation</strong>
                            <p class="mb-0 small">Optimal restock quantities calculated</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Inventory Overview</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary active">Weekly</button>
                        <button class="btn btn-outline-primary">Monthly</button>
                        <button class="btn btn-outline-primary">Yearly</button>
                    </div>
                </div>
                <div style="height: 300px; background: #f8fafc; border-radius: 12px;"></div>
            </div>
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