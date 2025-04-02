<?php
session_start();
include "../config/session_check.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Report - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">
</head>

<body>
    <!-- Keep your existing sidebar and header code here -->
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

        <div class="main-content">
            <div class="content-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2>Monthly Sales Report</h2>
                        <p class="text-muted">Generated on: February 18, 2024</p>
                    </div>
                    <div class="col-md-4 text-md-end btn-group">
                        <button class="btn btn-outline-primary" onclick="printReport()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                        <button class="btn btn-outline-primary" onclick="downloadReport('pdf')">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                        <button class="btn btn-outline-primary" onclick="downloadReport('excel')">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </button>
                        <button class="btn btn-outline-primary" onclick="shareReport()">
                            <i class="fas fa-share-alt me-2"></i>Share
                        </button>
                    </div>
                </div>

                <br>
                <!-- Report Navigation -->
                <div class="mb-4">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#summary">Summary</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#details">Detailed View</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#charts">Charts</a>
                        </li>
                    </ul>
                </div>

                <!-- Report Content -->
                <div class="report-content">
                    <!-- Summary Section -->
                    <div id="summary" class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Total Sales</h6>
                                        <h3>$45,678</h3>
                                        <p class="text-success mb-0">
                                            <i class="fas fa-arrow-up me-1"></i>12.5% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Total Orders</h6>
                                        <h3>1,234</h3>
                                        <p class="text-success mb-0">
                                            <i class="fas fa-arrow-up me-1"></i>8.3% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Average Order Value</h6>
                                        <h3>$37.02</h3>
                                        <p class="text-danger mb-0">
                                            <i class="fas fa-arrow-down me-1"></i>2.1% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Gross Profit</h6>
                                        <h3>$12,345</h3>
                                        <p class="text-success mb-0">
                                            <i class="fas fa-arrow-up me-1"></i>5.7% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Items Sold</h6>
                                        <h3>3,456</h3>
                                        <p class="text-success mb-0">
                                            <i class="fas fa-arrow-up me-1"></i>15.2% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Profit Margin</h6>
                                        <h3>27.1%</h3>
                                        <p class="text-warning mb-0">
                                            <i class="fas fa-minus me-1"></i>0.3% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Top Selling Item</h6>
                                        <h3>Product A</h3>
                                        <p class="text-muted mb-0">
                                            523 units sold
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Return Rate</h6>
                                        <h3>2.4%</h3>
                                        <p class="text-success mb-0">
                                            <i class="fas fa-arrow-down me-1"></i>0.5% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed View Section -->
                    <div id="details" class="mb-4" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Revenue</th>
                                        <th>Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Table data will be populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div id="charts" class="mb-4" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Sales Trend</h5>
                                        <canvas id="salesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Product Distribution</h5>
                                        <canvas id="productChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

    <script>
        // Tab Navigation
        document.querySelectorAll('.nav-tabs .nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                // Hide all sections
                document.querySelectorAll('.report-content > div').forEach(div => {
                    div.style.display = 'none';
                });
                // Show selected section
                const targetId = link.getAttribute('href').substring(1);
                document.getElementById(targetId).style.display = 'block';
                // Update active tab
                document.querySelectorAll('.nav-tabs .nav-link').forEach(l => {
                    l.classList.remove('active');
                });
                link.classList.add('active');
            });
        });

        // Print functionality
        function printReport() {
            window.print();
        }

        // Download functionality
        function downloadReport(format) {
            // Implementation for downloading reports
            alert(`Downloading report in ${format} format...`);
        }

        // Share functionality
        function shareReport() {
            // Implementation for sharing reports
            alert('Opening share options...');
        }

        // Initialize charts
        function initializeCharts() {
            // Sales Trend Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Sales',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                }
            });

            // Product Distribution Chart
            const productCtx = document.getElementById('productChart').getContext('2d');
            new Chart(productCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Product A', 'Product B', 'Product C'],
                    datasets: [{
                        data: [300, 50, 100],
                        backgroundColor: ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(255, 205, 86)']
                    }]
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            initializeCharts();
        });
    </script>
</body>

</html>