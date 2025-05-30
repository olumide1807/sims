<?php
session_start();
include "../../config/session_check.php";
include "../../config/config.php";
include "../../config/notification_functions.php";

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];

// Get notification data
$notification_count = getNotificationCount($user_id, $connect);
$notifications = getUserNotifications($user_id, $connect, 5); // Get 5 latest notifications
$notification_stats = getNotificationStats(getUserNotificationSettings($user_id, $connect), $connect);

// Handle AJAX requests for report actions
if (isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] == 'download') {
        $reportId = intval($_POST['report_id']);

        // Get report file path
        $sql = "SELECT file_path, report_name, parameters FROM reports WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $reportId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (file_exists($row['file_path'])) {
                echo json_encode([
                    'success' => true,
                    'download_url' => 'download_report.php?id=' . $reportId
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Report file not found'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Report not found'
            ]);
        }
        exit;
    }

    if ($_POST['action'] == 'view') {
        $reportId = intval($_POST['report_id']);

        // Get report details
        $sql = "SELECT * FROM reports WHERE id = ? AND generated_by = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $reportId, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'success' => true,
                'report' => $row
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Report not found'
            ]);
        }
        exit;
    }

    if ($_POST['action'] == 'delete') {
        $reportId = intval($_POST['report_id']);

        // Get file path before deletion
        $sql = "SELECT file_path FROM reports WHERE id = ? AND generated_by = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $reportId, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Delete file if exists
            if (file_exists($row['file_path'])) {
                unlink($row['file_path']);
            }

            // Delete from database
            $deleteSql = "DELETE FROM reports WHERE id = ? AND generated_by = ?";
            $deleteStmt = mysqli_prepare($connect, $deleteSql);
            mysqli_stmt_bind_param($deleteStmt, "ii", $reportId, $_SESSION['user_id']);

            if (mysqli_stmt_execute($deleteStmt)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Report deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete report'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Report not found'
            ]);
        }
        exit;
    }
}

// Fetch recent reports from database
$recentReports = [];

// Get the current page from the URL, defaulting to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of rows per page
$offset = ($page - 1) * $limit;

$total_qry = "SELECT COUNT(*) as total FROM reports";
$total_res = mysqli_query($connect, $total_qry);
$total_row = mysqli_fetch_assoc($total_res);
$total_reports = $total_row['total'];
$total_pages = ceil($total_reports / $limit);

try {
    $sql = "SELECT id, report_name, report_type, generated_at, file_path, generated_by, parameters
            FROM reports 
            WHERE generated_by = ? 
            ORDER BY generated_at DESC 
            LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $_SESSION['user_id'], $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $parameters = json_decode($row['parameters'], true);
        $row['export_format'] = isset($parameters['export_format']) ? $parameters['export_format'] : 'pdf';


        if (file_exists($row['file_path'])) {
            $row['file_size'] = filesize($row['file_path']);
        } else {
            $row['file_size'] = 0;
        }

        $recentReports[] = $row;
    }

    /* echo '<pre>';
    print_r($recentReports); 
    echo '</pre>';die(); */

    error_log("Found " . count($recentReports) . " reports for user " . $_SESSION['user_id']);
} catch (Exception $e) {
    error_log("Error fetching reports: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/css/style.css">

    <?php echo getNotificationDropdownCSS(); ?>
    <style>
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .btn-loading {
            position: relative;
        }

        .btn-loading:after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 1.5rem 0 2rem 0;
            padding: 0;
            gap: 0.25rem;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.5rem;
            height: 2.5rem;
            padding: 0.5rem 0.75rem;
            margin: 0 0.1rem;
            text-decoration: none;
            border-radius: 0.25rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .pagination a {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .pagination a:hover {
            background-color: #e9ecef;
            color: #0d6efd;
            border-color: #0d6efd;
            z-index: 1;
        }

        .pagination span {
            background-color: #0d6efd;
            color: white;
            border: 1px solid #0d6efd;
        }

        /* For previous and next buttons */
        .pagination a.prev,
        .pagination a.next {
            padding: 0.5rem 1rem;
        }

        /* For mobile responsiveness */
        @media (max-width: 576px) {
            .pagination {
                flex-wrap: wrap;
            }

            .pagination a,
            .pagination span {
                min-width: 2rem;
                height: 2rem;
                padding: 0.25rem 0.5rem;
                margin-bottom: 0.5rem;
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
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

                <!-- Reports -->
                <a href="#" class="nav-link active" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
                <div class="submenu show" id="reports">
                    <a href="#" class="nav-link active"><i class="fas fa-file-export"></i> Generate Reports</a>
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

            <!-- Report Generation Section -->
            <div class="content-card mb-4">
                <h4 class="mb-4">Generate New Report</h4>
                <form action="process_report.php" method="POST">
                    <div class="row">
                        <!-- Report Type Dropdown -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Report Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="report_type" id="reportType" required>
                                <option value="">Select report type...</option>
                                <option value="sales_summary">Sales Summary</option>
                                <option value="inventory_valuation">Inventory Valuation</option>
                                <option value="low_stock">Low Stock Alert</option>
                                <option value="stock_movement">Stock Movement</option>
                                <option value="expiry_report">Expiry Report</option>
                                <option value="all">Comprehensive Report</option>
                                <option value="custom">Custom Report</option>
                            </select>
                            <div class="form-text">Choose the type of report you want to generate</div>
                        </div>

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

                        <div id="dateRangeSection" class="row" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" id="startDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="endDate">
                            </div>
                        </div>

                        <!-- Report Description Based on Type -->
                        <div class="col-12 mb-3">
                            <div id="reportDescription" class="alert alert-info" style="display: none;">
                                <small id="descriptionText"></small>
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
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="detailed_breakdown" id="detailedBreakdown">
                                <label class="form-check-label" for="detailedBreakdown">Include detailed breakdown</label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Export Format</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="export_format" id="pdf" value="pdf" checked>
                                <label class="btn btn-outline-primary" for="pdf">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </label>

                                <input type="radio" class="btn-check" name="export_format" id="excel" value="excel">
                                <label class="btn btn-outline-primary" for="excel">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </label>

                                <input type="radio" class="btn-check" name="export_format" id="csv" value="csv">
                                <label class="btn btn-outline-primary" for="csv">
                                    <i class="fas fa-file-csv me-1"></i>CSV
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
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
                    <!-- <a href="../report/viewreport.php" class="btn btn-link text-decoration-none">View All Reports</a> -->
                    <div class="search-box">
                    <!-- <i class="fas fa-search text-muted me-2"></i> -->
                    <input type="text" placeholder="Search reports..." id="searchReports" onkeyup="searchReports()">
                </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="reportsTable">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Generated On</th>
                                <th>Format</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentReports)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                        <p>No reports generated yet</p>
                                        <small>Generate your first report using the form above</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                    $serial_start = ($page - 1) * $limit + 1;
                                    $counter = $serial_start; 
                                ?>
                                <?php foreach ($recentReports as $report): ?>
                                    <tr data-report-name="<?php echo strtolower($report['report_name']); ?>">
                                        <td><?php echo $counter++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($report['report_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $report['report_type'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-badge bg-secondary">
                                                <?php echo ucfirst(str_replace('_', ' ', $report['report_type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $date = new DateTime($report['generated_at']);
                                            echo $date->format('M d, Y');
                                            ?>
                                            <br><small class="text-muted"><?php echo $date->format('h:i A'); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-badge bg-info">
                                                <?php echo strtoupper($report['export_format']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php
                                                if ($report['file_size']) {
                                                    echo number_format($report['file_size'] / 1024, 1) . ' KB';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="downloadReport(<?php echo $report['id']; ?>)"
                                                    title="Download Report">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    onclick="viewReport(<?php echo $report['id']; ?>)"
                                                    title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteReport(<?php echo $report['id']; ?>)"
                                                    title="Delete Report">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php
                    echo "<div class='pagination'>";
                    if ($page > 1) {
                        echo "<a href='?page=" . ($page - 1) . "'>&laquo; Prev</a>";
                    }

                    // Calculate range for pagination
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    // Show first page and ellipsis if needed
                    if ($start_page > 1) {
                        echo "<a href='?page=1'>1</a>";
                        if ($start_page > 2) {
                            echo "<span style='background-color: transparent; border: none; color: #495057;'>...</span>";
                        }
                    }

                    // Show the page range
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        if ($i == $page) {
                            echo "<span>$i</span>";
                        } else {
                            echo "<a href='?page=$i'>$i</a>";
                        }
                    }

                    // Show last page and ellipsis if needed
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo "<span style='background-color: transparent; border: none; color: #495057;'>...</span>";
                        }
                        echo "<a href='?page=$total_pages'>$total_pages</a>";
                    }

                    if ($page < $total_pages) {
                        echo "<a href='?page=" . ($page + 1) . "'>Next &raquo;</a>";
                    }
                    echo "</div>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Details Modal -->
    <div class="modal fade" id="reportDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Report Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reportDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this report? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Report</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
    <script>
        let reportToDelete = null;

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

        // Show report description based on selected type
        document.getElementById('reportType').addEventListener('change', function() {
            const reportType = this.value;
            const descriptionDiv = document.getElementById('reportDescription');
            const descriptionText = document.getElementById('descriptionText');

            const descriptions = {
                'sales_summary': 'Generate a comprehensive summary of sales performance including total revenue, top-selling products, and sales trends.',
                'inventory_valuation': 'Calculate the total value of current inventory, including cost analysis and valuation methods.',
                'low_stock': 'Identify products that are running low on stock and need immediate attention for restocking.',
                'stock_movement': 'Track inventory movements including purchases, sales, returns, and adjustments over the selected period.',
                'expiry_report': 'List products that are nearing expiration or have already expired to prevent losses.',
                'all': 'Generate a comprehensive report including all major aspects: sales, inventory, stock levels, and movements.',
                'custom': 'Create a customized report based on your specific requirements and selected parameters.'
            };

            if (descriptions[reportType]) {
                descriptionText.textContent = descriptions[reportType];
                descriptionDiv.style.display = 'block';
            } else {
                descriptionDiv.style.display = 'none';
            }
        });

        // Reset form function
        function resetForm() {
            document.querySelector('form').reset();
            document.getElementById('dateRangeSection').style.display = 'none';
            document.getElementById('reportDescription').style.display = 'none';
        }

        // Search reports function
        function searchReports() {
            const searchTerm = document.getElementById('searchReports').value.toLowerCase();
            const tableRows = document.querySelectorAll('#reportsTable tbody tr');

            tableRows.forEach(row => {
                const reportName = row.getAttribute('data-report-name');
                if (reportName && reportName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Download report function
        function downloadReport(reportId) {
            const btn = event.target.closest('button');
            btn.classList.add('btn-loading');
            btn.disabled = true;

            fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=download&report_id=' + reportId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.open(data.download_url, '_blank');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while downloading the report');
                })
                .finally(() => {
                    btn.classList.remove('btn-loading');
                    btn.disabled = false;
                });
        }

        // View report function - FIXED VERSION
        function viewReport(reportId) {
            const btn = event.target.closest('button');
            btn.classList.add('btn-loading');
            btn.disabled = true;

            fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=view&report_id=' + reportId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showReportDetails(data.report);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching report details');
                })
                .finally(() => {
                    btn.classList.remove('btn-loading');
                    btn.disabled = false;
                });
        }

        // Show report details in modal - FIXED VERSION
        function showReportDetails(report) {
            // Parse parameters if it's a JSON string
            let parameters = {};
            if (report.parameters) {
                try {
                    parameters = typeof report.parameters === 'string' ? JSON.parse(report.parameters) : report.parameters;
                } catch (e) {
                    console.error('Error parsing parameters:', e);
                    parameters = {};
                }
            }

            // Get export format from parameters or default to PDF
            const exportFormat = parameters.export_format || 'pdf';

            // Format date properly
            const generatedDate = new Date(report.generated_at);
            const formattedDate = generatedDate.toLocaleDateString() + ' ' + generatedDate.toLocaleTimeString();

            // Calculate file size
            const fileSize = report.file_size ? (report.file_size / 1024).toFixed(1) + ' KB' : 'N/A';

            // Format report type
            const reportType = report.report_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

            // Check for boolean values in parameters
            const includeCharts = parameters.include_charts === '1' || parameters.include_charts === true;
            const includeSummary = parameters.include_summary === '1' || parameters.include_summary === true;
            const detailedBreakdown = parameters.detailed_breakdown === '1' || parameters.detailed_breakdown === true;

            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Report Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Name:</strong></td><td>${report.report_name}</td></tr>
                            <tr><td><strong>Type:</strong></td><td>${reportType}</td></tr>
                            <tr><td><strong>Format:</strong></td><td>${exportFormat.toUpperCase()}</td></tr>
                            <tr><td><strong>Generated:</strong></td><td>${formattedDate}</td></tr>
                            <tr><td><strong>File Size:</strong></td><td>${fileSize}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Report Options</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-${includeCharts ? 'check text-success' : 'times text-danger'}"></i> Charts & Graphs</li>
                            <li><i class="fas fa-${includeSummary ? 'check text-success' : 'times text-danger'}"></i> Executive Summary</li>
                            <li><i class="fas fa-${detailedBreakdown ? 'check text-success' : 'times text-danger'}"></i> Detailed Breakdown</li>
                        </ul>
                    </div>
                </div>
                ${parameters.time_period ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Time Period</h6>
                        <p class="text-muted">${parameters.time_period.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
                        ${parameters.start_date && parameters.end_date ? `
                        <p><strong>Date Range:</strong> ${parameters.start_date} to ${parameters.end_date}</p>
                        ` : ''}
                    </div>
                </div>
                ` : ''}
            `;

            document.getElementById('reportDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('reportDetailsModal')).show();
        }

        // Delete report function
        function deleteReport(reportId) {
            reportToDelete = reportId;
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        }

        // Confirm delete
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (reportToDelete) {
                const btn = this;
                btn.classList.add('btn-loading');
                btn.disabled = true;

                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=delete&report_id=' + reportToDelete
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Refresh page to update the table
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the report');
                    })
                    .finally(() => {
                        btn.classList.remove('btn-loading');
                        btn.disabled = false;
                        bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
                    });
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleDateRange();
        });
    </script>
</body>

</html>