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
    <title>Sales History | Inventory System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">
    <?php echo getNotificationDropdownCSS(); ?>
    <style>
        .sales-highlight {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
        }
        .filter-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .sale-details {
            transition: all 0.2s ease;
        }
        .sale-details:hover {
            background-color: #f8f9fa;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .badge-status {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        .badge-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-refunded {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
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
                <a href="../sales/" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <!-- Inventory Management -->
                <a href="../inventory/" class="nav-link">
                    <i class="fas fa-box"></i> View Inventory
                </a>

                <!-- Sales Management -->
                <a href="#" class="nav-link active" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-shopping-cart"></i> Sales
                </a>
                <div class="submenu show" id="sales">
                    <a href="logsales.php" class="nav-link"><i class="fas fa-cash-register"></i> New Sale</a>
                    <a href="viewsales.php" class="nav-link active"><i class="fas fa-chart-bar"></i> Sales History</a>
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
                    <input type="text" placeholder="Search sales..."> -->
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
            <div class="welcome-section sales-highlight">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3>Sales History</h3>
                        <p class="mb-3">View and manage all sales transactions</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="quick-actions">
                            <button class="btn btn-light rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="fas fa-file-export me-2"></i>Export
                            </button>
                            <a href="logsales.php" class="btn btn-outline-light rounded-pill px-4">
                                <i class="fas fa-plus me-2"></i>New Sale
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card">
                <form id="sales-filter-form">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" id="date-range">
                                <option value="today">Today</option>
                                <option value="week" selected>This Week</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3" id="custom-date-start" style="display: none;">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start-date">
                        </div>
                        <div class="col-md-3 mb-3" id="custom-date-end" style="display: none;">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end-date">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status-filter">
                                <option value="all" selected>All Statuses</option>
                                <option value="completed">Completed</option>
                                <option value="refunded">Refunded</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sales Rep</label>
                            <select class="form-select" id="rep-filter">
                                <option value="all" selected>All Reps</option>
                                <option value="self">My Sales Only</option>
                                <!-- Other reps would be populated dynamically -->
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Amount Range</label>
                            <select class="form-select" id="amount-range">
                                <option value="all" selected>Any Amount</option>
                                <option value="0-100">$0 - $100</option>
                                <option value="100-500">$100 - $500</option>
                                <option value="500-1000">$500 - $1,000</option>
                                <option value="1000+">$1,000+</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sales Summary -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3 class="mb-1">142</h3>
                    <p class="text-muted mb-0">Total Transactions</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="mb-1">$12,845</h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1">128</h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3 class="mb-1">14</h3>
                    <p class="text-muted mb-0">Refunded</p>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Sales Transactions</h5>
                    <div class="d-flex align-items-center">
                        <span class="me-3 text-muted">Showing 1-10 of 142</span>
                        <select class="form-select form-select-sm w-auto me-2">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Sales Rep</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Sample data - would be populated from database in real app -->
                            <tr class="sale-details">
                                <td>#TRX-1042</td>
                                <td>May 27, 2023<br><small class="text-muted">10:45 AM</small></td>
                                <td>John Smith</td>
                                <td>3</td>
                                <td>$245.50</td>
                                <td><span class="status-badge status-completed">Completed</span></td>
                                <td><?php echo htmlspecialchars($_SESSION['firstname']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#saleDetailsModal">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <tr class="sale-details">
                                <td>#TRX-1041</td>
                                <td>May 27, 2023<br><small class="text-muted">9:30 AM</small></td>
                                <td>Jane Doe</td>
                                <td>1</td>
                                <td>$89.99</td>
                                <td><span class="status-badge status-completed">Completed</span></td>
                                <td><?php echo htmlspecialchars($_SESSION['firstname']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#saleDetailsModal">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <tr class="sale-details">
                                <td>#TRX-1040</td>
                                <td>May 26, 2023<br><small class="text-muted">4:15 PM</small></td>
                                <td>Robert Johnson</td>
                                <td>5</td>
                                <td>$412.75</td>
                                <td><span class="status-badge status-completed">Completed</span></td>
                                <td>Sarah Williams</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#saleDetailsModal">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <tr class="sale-details">
                                <td>#TRX-1039</td>
                                <td>May 26, 2023<br><small class="text-muted">2:00 PM</small></td>
                                <td>Emily Chen</td>
                                <td>2</td>
                                <td>$156.20</td>
                                <td><span class="status-badge status-completed badge-refunded">Refunded</span></td>
                                <td><?php echo htmlspecialchars($_SESSION['firstname']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#saleDetailsModal">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <tr class="sale-details">
                                <td>#TRX-1038</td>
                                <td>May 25, 2023<br><small class="text-muted">11:30 AM</small></td>
                                <td>Michael Brown</td>
                                <td>7</td>
                                <td>$587.30</td>
                                <td><span class="status-badge status-completed">Completed</span></td>
                                <td>David Lee</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#saleDetailsModal">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Sale Details Modal -->
    <div class="modal fade" id="saleDetailsModal" tabindex="-1" aria-labelledby="saleDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saleDetailsModalLabel">Sale Details - #TRX-1042</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Transaction Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Date:</strong> May 27, 2023 10:45 AM</li>
                                <li><strong>Status:</strong> <span class="badge badge-status badge-completed">Completed</span></li>
                                <li><strong>Payment Method:</strong> Credit Card (VISA ****4242)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Name:</strong> John Smith</li>
                                <li><strong>Email:</strong> john.smith@example.com</li>
                                <li><strong>Phone:</strong> (555) 123-4567</li>
                            </ul>
                        </div>
                    </div>

                    <h6 class="mb-3">Items Purchased</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Discount</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Wireless Headphones Pro</td>
                                    <td>SKU-78945</td>
                                    <td>$129.99</td>
                                    <td>1</td>
                                    <td>$0.00</td>
                                    <td>$129.99</td>
                                </tr>
                                <tr>
                                    <td>Bluetooth Speaker</td>
                                    <td>SKU-45612</td>
                                    <td>$59.99</td>
                                    <td>2</td>
                                    <td>$5.00</td>
                                    <td>$114.98</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                                    <td>$244.97</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Tax (8.25%):</strong></td>
                                    <td>$20.21</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                    <td>$265.18</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4">
                        <h6>Additional Notes</h6>
                        <p>Customer requested gift wrapping for the headphones.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                    <button type="button" class="btn btn-outline-danger">
                        <i class="fas fa-exchange-alt me-2"></i>Process Refund
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Sales Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm">
                        <div class="mb-3">
                            <label class="form-label">Export Format</label>
                            <select class="form-select" id="export-format">
                                <option value="csv">CSV (Comma Separated Values)</option>
                                <option value="excel">Excel (XLSX)</option>
                                <option value="pdf">PDF Document</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" id="export-date-range">
                                <option value="filtered">Use current filters</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                                <option value="all">All Time</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Columns to Include</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="export-id" checked>
                                <label class="form-check-label" for="export-id">
                                    Transaction ID
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="export-date" checked>
                                <label class="form-check-label" for="export-date">
                                    Date & Time
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="export-customer" checked>
                                <label class="form-check-label" for="export-customer">
                                    Customer
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="export-items" checked>
                                <label class="form-check-label" for="export-items">
                                    Items
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="export-total" checked>
                                <label class="form-check-label" for="export-total">
                                    Total
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="export-status" checked>
                                <label class="form-check-label" for="export-status">
                                    Status
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
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

        // Show/hide custom date range fields
        document.getElementById('date-range').addEventListener('change', function() {
            const showCustom = this.value === 'custom';
            document.getElementById('custom-date-start').style.display = showCustom ? 'block' : 'none';
            document.getElementById('custom-date-end').style.display = showCustom ? 'block' : 'none';
        });

        // In a real application, you would have AJAX calls to load sales data based on filters
        document.getElementById('sales-filter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Simulate loading data
            console.log('Filters applied:', {
                dateRange: document.getElementById('date-range').value,
                status: document.getElementById('status-filter').value,
                rep: document.getElementById('rep-filter').value,
                amountRange: document.getElementById('amount-range').value
            });
            // Here you would make an AJAX call to fetch filtered data
        });
    </script>
</body>

</html>