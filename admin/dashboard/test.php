<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaSys - Pharmacy Management Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
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

        /* Sales Processing */
        .sales-panel {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .prescription-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .prescription-toggle {
            cursor: pointer;
            color: #0d6efd;
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
                    <i class="fas fa-mortar-pestle text-primary"></i>
                    PharmaSys
                </h4>
            </div>
            
            <nav>
                <!-- Dashboard -->
                <a href="#" class="nav-link active">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <!-- Inventory Lookup -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('inventory')">
                    <i class="fas fa-pills"></i> Inventory Lookup
                </a>
                <div class="submenu" id="inventory">
                    <a href="#" class="nav-link"><i class="fas fa-search"></i> Quick Search</a>
                    <a href="#" class="nav-link"><i class="fas fa-list"></i> Browse Categories</a>
                    <a href="#" class="nav-link"><i class="fas fa-tags"></i> By Manufacturer</a>
                </div>

                <!-- Sales & Billing -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-cash-register"></i> Sales & Billing
                </a>
                <div class="submenu" id="sales">
                    <a href="#" class="nav-link"><i class="fas fa-shopping-cart"></i> New Sale</a>
                    <a href="#" class="nav-link"><i class="fas fa-receipt"></i> Receipt History</a>
                    <a href="#" class="nav-link"><i class="fas fa-undo"></i> Process Returns</a>
                </div>

                <!-- Prescription Validation -->
                <a href="#" class="nav-link">
                    <i class="fas fa-prescription"></i> Prescription Validation
                </a>

                <!-- Low Stock Alerts -->
                <a href="#" class="nav-link">
                    <i class="fas fa-exclamation-triangle"></i> Low Stock Alerts
                    <span class="badge rounded-pill bg-danger ms-2">18</span>
                </a>

                <!-- Expiry Tracker -->
                <a href="#" class="nav-link">
                    <i class="fas fa-calendar-times"></i> Expiry Tracker
                    <span class="badge rounded-pill bg-warning text-dark ms-2">12</span>
                </a>

                <!-- Sales Reports -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-chart-bar"></i> Sales Reports
                </a>
                <div class="submenu" id="reports">
                    <a href="#" class="nav-link"><i class="fas fa-calendar-day"></i> Daily Reports</a>
                    <a href="#" class="nav-link"><i class="fas fa-calendar-week"></i> Weekly Reports</a>
                    <a href="#" class="nav-link"><i class="fas fa-calendar-alt"></i> Monthly Reports</a>
                </div>

                <!-- Settings -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('settings')">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="submenu" id="settings">
                    <a href="#" class="nav-link"><i class="fas fa-user"></i> Profile Settings</a>
                    <a href="#" class="nav-link"><i class="fas fa-lock"></i> Change Password</a>
                    <a href="#" class="nav-link"><i class="fas fa-bell"></i> Notification Settings</a>
                </div>

                <!-- Logout -->
                <a href="#" class="nav-link">
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
                    <input type="text" placeholder="Search medications...">
                </div>
                <div class="user-section">
                    <div class="notification-badge">
                        <i class="fas fa-bell text-muted"></i>
                        <span class="badge rounded-pill bg-danger">5</span>
                    </div>
                    <img src="/api/placeholder/40/40" class="rounded-circle" alt="User avatar">
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Welcome back, Dr. Johnson!</h3>
                        <p class="mb-3">Today is Saturday, March 15, 2025. You have 5 pending alerts.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light rounded-pill px-4">
                            <i class="fas fa-shopping-cart me-2"></i>New Sale
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h3 class="mb-1">1,287</h3>
                    <p class="text-muted mb-0">Total Medications</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="mb-1">$2,846</h3>
                    <p class="text-muted mb-0">Today's Revenue</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="mb-1">18</h3>
                    <p class="text-muted mb-0">Low Stock Items</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3 class="mb-1">12</h3>
                    <p class="text-muted mb-0">Expiring Soon</p>
                </div>
            </div>

            <!-- Sales Processing Panel -->
            <div class="sales-panel">
                <h5 class="mb-4">Quick Sales Processing</h5>
                
                <!-- Prescription Toggle -->
                <div class="prescription-section mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="prescriptionRequired">
                        <label class="form-check-label" for="prescriptionRequired">Prescription Required</label>
                    </div>