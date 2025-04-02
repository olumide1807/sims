<?php
session_start();
include "../config/session_check.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - SIMS</title>
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

                <!-- Reports -->
                <a href="#" class="nav-link active" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
                <div class="submenu show" id="reports">
                    <a href="#" class="nav-link active"><i class="fas fa-file-export"></i> Generate Reports</a>
                    <a href="#" class="nav-link"><i class="fas fa-file-import"></i> View Reports</a>
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
                    <input type="text" placeholder="Search reports...">
                </div>
                <div class="user-section">
                    <div class="notification-badge">
                        <i class="fas fa-bell text-muted"></i>
                        <span class="badge rounded-pill bg-danger">3</span>
                    </div>
                    <img src="/placeholder.svg?height=40&width=40" class="rounded-circle" alt="User avatar">
                </div>
            </div>

            <!-- Report Generation Section -->
            <div class="content-card mb-4">
                <h4 class="mb-4">Generate New Report</h4>
                <form action="process_report.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time Period</label>
                            <select class="form-select" name="time_period" id="timePeriod" required onchange="toggleDateRange()">
                                <option value="">Select time period...</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="last_week">Last Week</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_quarter">Last Quarter</option>
                                <option value="last_year">Last Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div id="dateRangeSection" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" id="startDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="endDate">
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Additional Options</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_charts" id="includeCharts">
                                <label class="form-check-label" for="includeCharts">Include charts and graphs</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_summary" id="includeSummary">
                                <label class="form-check-label" for="includeSummary">Include executive summary</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Export Format</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="export_format" id="pdf" value="pdf" checked>
                                <label class="btn btn-outline-primary" for="pdf">PDF</label>

                                <input type="radio" class="btn-check" name="export_format" id="excel" value="excel">
                                <label class="btn btn-outline-primary" for="excel">Excel</label>

                                <input type="radio" class="btn-check" name="export_format" id="csv" value="csv">
                                <label class="btn btn-outline-primary" for="csv">CSV</label>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-export me-2"></i>Generate Report
                        </button>
                    </div>
                </form>


            </div>

            <!-- Recent Reports -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>Recently Generated Reports</h5>
                    <button class="btn btn-link text-decoration-none">View All Reports</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Generated On</th>
                                <th>Format</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Monthly Inventory Status</td>
                                <td><span class="status-badge">Inventory</span></td>
                                <td>2024-02-18</td>
                                <td>PDF</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Q4 Sales Analysis</td>
                                <td><span class="status-badge">Sales</span></td>
                                <td>2024-02-17</td>
                                <td>Excel</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Low Stock Items Report</td>
                                <td><span class="status-badge">Low Stock</span></td>
                                <td>2024-02-16</td>
                                <td>PDF</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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

        function toggleDateRange() {
            const timePeriod = document.getElementById('timePeriod').value;
            const dateRangeSection = document.getElementById('dateRangeSection');

            if (timePeriod === 'custom') {
                dateRangeSection.style.display = 'block';
                document.getElementById('startDate').required = true;
                document.getElementById('endDate').required = true;
            } else {
                dateRangeSection.style.display = 'none';
                document.getElementById('startDate').required = false;
                document.getElementById('endDate').required = false;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleDateRange();
        });
    </script>
    <script>

    </script>
</body>

</html>