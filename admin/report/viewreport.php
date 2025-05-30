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

// Get date range for filtering (default to current month)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Initialize variables
$total_sales = 0;
$total_orders = 0;
$items_sold = 0;
$average_order = 0;
$gross_profit = 0;
$profit_margin = 0;
$top_item_name = 'N/A';
$top_item_qty = 0;
$return_rate = 0;

// Sales growth percentages
$sales_growth = 0;
$orders_growth = 0;
$items_growth = 0;
$profit_growth = 0;

// Get Total Sales
$query = "SELECT SUM(total_amount) as total_sales FROM sales WHERE sale_date BETWEEN '$start_date' AND '$end_date'";
$result = mysqli_query($connect, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $total_sales = $row['total_sales'] ?? 0;
}

// Get Total Orders
$query = "SELECT COUNT(*) as total_orders FROM sales WHERE sale_date BETWEEN '$start_date' AND '$end_date'";
$result = mysqli_query($connect, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $total_orders = $row['total_orders'] ?? 0;
}

// Calculate Average Order Value
$average_order = $total_orders > 0 ? $total_sales / $total_orders : 0;

// Get Total Items Sold
$query = "SELECT SUM(si.quantity) as items_sold 
          FROM sale_details si 
          JOIN sales s ON si.sale_id = s.id 
          WHERE s.sale_date BETWEEN '$start_date' AND '$end_date'";
$result = mysqli_query($connect, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $items_sold = $row['items_sold'] ?? 0;
}

// Get Gross Profit (assuming you have cost_price in products table)
$query = "SELECT SUM((si.unit_price - p.cost_price) * si.quantity) as gross_profit 
          FROM sale_details si 
          JOIN sales s ON si.sale_id = s.id 
          JOIN products p ON si.product_id = p.id 
          WHERE s.sale_date BETWEEN '$start_date' AND '$end_date'";
$result = mysqli_query($connect, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $gross_profit = $row['gross_profit'] ?? 0;
}

// Calculate Profit Margin
$profit_margin = $total_sales > 0 ? ($gross_profit / $total_sales) * 100 : 0;

// Get Top Selling Item
$query = "SELECT p.product_name, SUM(si.quantity) as total_qty 
          FROM sale_details si 
          JOIN sales s ON si.sale_id = s.id 
          JOIN products p ON si.product_id = p.id 
          WHERE s.sale_date BETWEEN '$start_date' AND '$end_date' 
          GROUP BY p.id, p.product_name 
          ORDER BY total_qty DESC 
          LIMIT 1";
$result = mysqli_query($connect, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $top_item_name = $row['product_name'];
    $top_item_qty = $row['total_qty'];
}

// Get Previous Period Data for Comparison
$prev_start = date('Y-m-d', strtotime($start_date . ' -1 month'));
$prev_end = date('Y-m-d', strtotime($end_date . ' -1 month'));

// Previous Sales
$query = "SELECT SUM(total_amount) as prev_sales FROM sales WHERE sale_date BETWEEN '$prev_start' AND '$prev_end'";
$result = mysqli_query($connect, $query);
$prev_sales = 0;
if ($result && $row = mysqli_fetch_assoc($result)) {
    $prev_sales = $row['prev_sales'] ?? 0;
}

// Previous Orders
$query = "SELECT COUNT(*) as prev_orders FROM sales WHERE sale_date BETWEEN '$prev_start' AND '$prev_end'";
$result = mysqli_query($connect, $query);
$prev_orders = 0;
if ($result && $row = mysqli_fetch_assoc($result)) {
    $prev_orders = $row['prev_orders'] ?? 0;
}

// Previous Items Sold
$query = "SELECT SUM(si.quantity) as prev_items 
          FROM sale_details si 
          JOIN sales s ON si.sale_id = s.id 
          WHERE s.sale_date BETWEEN '$prev_start' AND '$prev_end'";
$result = mysqli_query($connect, $query);
$prev_items = 0;
if ($result && $row = mysqli_fetch_assoc($result)) {
    $prev_items = $row['prev_items'] ?? 0;
}

// Previous Profit
$query = "SELECT SUM((si.unit_price - p.cost_price) * si.quantity) as prev_profit 
          FROM sale_details si 
          JOIN sales s ON si.sale_id = s.id 
          JOIN products p ON si.product_id = p.id 
          WHERE s.sale_date BETWEEN '$prev_start' AND '$prev_end'";
$result = mysqli_query($connect, $query);
$prev_profit = 0;
if ($result && $row = mysqli_fetch_assoc($result)) {
    $prev_profit = $row['prev_profit'] ?? 0;
}

// Calculate Growth Percentages
$sales_growth = $prev_sales > 0 ? (($total_sales - $prev_sales) / $prev_sales) * 100 : 0;
$orders_growth = $prev_orders > 0 ? (($total_orders - $prev_orders) / $prev_orders) * 100 : 0;
$items_growth = $prev_items > 0 ? (($items_sold - $prev_items) / $prev_items) * 100 : 0;
$profit_growth = $prev_profit > 0 ? (($gross_profit - $prev_profit) / $prev_profit) * 100 : 0;

// ===== INVENTORY MANAGEMENT REPORTS DATA =====

// Enhanced Low Stock Alert Report with more details
$low_stock_products = [];
$query = "SELECT p.product_name, p.quantity_per_pack, p.low_stock_alert, 
          'Not specified' as supplier,
          CASE 
              WHEN p.quantity_per_pack <= 0 THEN 'Critical'
              WHEN p.quantity_per_pack <= 5 THEN 'Very Low'
              WHEN p.quantity_per_pack <= p.low_stock_alert THEN 'Low'
          END as stock_status,
          -- Calculate projected stockout date based on recent sales
          CASE 
              WHEN recent_sales.daily_avg > 0 AND p.quantity_per_pack > 0 
              THEN DATE_ADD(CURDATE(), INTERVAL FLOOR(p.quantity_per_pack / recent_sales.daily_avg) DAY)
              ELSE NULL 
          END as projected_stockout,
          COALESCE(recent_sales.daily_avg, 0) as daily_consumption
          FROM products p 
          
          LEFT JOIN (
              SELECT si.product_id, 
                     SUM(si.quantity) / 30 as daily_avg
              FROM sale_details si
              JOIN sales s ON si.sale_id = s.id
              WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              GROUP BY si.product_id
          ) recent_sales ON p.id = recent_sales.product_id
          WHERE p.quantity_per_pack <= GREATEST(p.low_stock_alert, 5)
          ORDER BY 
              CASE 
                  WHEN p.quantity_per_pack <= 0 THEN 1
                  WHEN p.quantity_per_pack <= 5 THEN 2
                  ELSE 3
              END,
              p.quantity_per_pack ASC";
$result = mysqli_query($connect, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $low_stock_products[] = $row;
    }
}

// Enhanced Inventory Valuation Report with movement classification
$inventory_valuation = [];
$total_inventory_cost = 0;
$total_inventory_selling = 0;
$total_potential_profit = 0;

$query = "SELECT p.product_name, p.quantity_per_pack, p.cost_price, p.price_per_sachet,
          (p.quantity_per_pack * p.cost_price) as cost_value,
          (p.quantity_per_pack * p.price_per_sachet) as selling_value,
          ((p.quantity_per_pack * p.price_per_sachet) - (p.quantity_per_pack * p.cost_price)) as potential_profit,
          COALESCE(recent_sales.total_sold, 0) as units_sold_90_days,
          COALESCE(recent_sales.last_sale_date, '1900-01-01') as last_sale_date,
          CASE 
              WHEN COALESCE(recent_sales.last_sale_date, '1900-01-01') < DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 'Dead Stock'
              WHEN COALESCE(recent_sales.total_sold, 0) >= 100 THEN 'Fast Moving'
              WHEN COALESCE(recent_sales.total_sold, 0) >= 30 THEN 'Medium Moving'
              WHEN COALESCE(recent_sales.total_sold, 0) >= 10 THEN 'Slow Moving'
              ELSE 'Very Slow Moving'
          END as movement_category,
          -- Calculate turnover ratio
          CASE 
              WHEN p.quantity_per_pack > 0 
              THEN ROUND(COALESCE(recent_sales.total_sold, 0) / p.quantity_per_pack, 2)
              ELSE 0 
          END as turnover_ratio
          FROM products p
          LEFT JOIN (
              SELECT si.product_id, 
                     MAX(s.sale_date) as last_sale_date, 
                     SUM(si.quantity) as total_sold
              FROM sale_details si
              JOIN sales s ON si.sale_id = s.id
              WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
              GROUP BY si.product_id
          ) recent_sales ON p.id = recent_sales.product_id
          WHERE p.quantity_per_pack > 0
          ORDER BY cost_value DESC";
$result = mysqli_query($connect, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $inventory_valuation[] = $row;
        $total_inventory_cost += $row['cost_value'];
        $total_inventory_selling += $row['selling_value'];
        $total_potential_profit += $row['potential_profit'];
    }
}

// Enhanced Stock Movement Report with trend analysis
$stock_movement = [];
$query = "SELECT p.product_name, 
          SUM(si.quantity) as total_sold,
          AVG(si.quantity) as avg_per_sale,
          COUNT(DISTINCT s.sale_date) as days_sold,
          COUNT(si.id) as number_of_transactions,
          MIN(s.sale_date) as first_sale,
          MAX(s.sale_date) as last_sale,
          ROUND(SUM(si.quantity) / NULLIF(DATEDIFF(MAX(s.sale_date), MIN(s.sale_date)) + 1, 0), 2) as daily_avg,
          SUM(si.quantity * si.unit_price) as total_revenue,
          -- Calculate weekly trend
          ROUND(SUM(si.quantity) / NULLIF(CEIL(DATEDIFF(MAX(s.sale_date), MIN(s.sale_date)) / 7), 0), 2) as weekly_avg,
          -- Movement velocity
          CASE 
              WHEN SUM(si.quantity) / NULLIF(DATEDIFF(MAX(s.sale_date), MIN(s.sale_date)) + 1, 0) >= 5 THEN 'High Velocity'
              WHEN SUM(si.quantity) / NULLIF(DATEDIFF(MAX(s.sale_date), MIN(s.sale_date)) + 1, 0) >= 2 THEN 'Medium Velocity'
              ELSE 'Low Velocity'
          END as velocity_rating
          FROM sale_details si 
          JOIN sales s ON si.sale_id = s.id 
          JOIN products p ON si.product_id = p.id 
          WHERE s.sale_date BETWEEN '$start_date' AND '$end_date'
          GROUP BY p.id, p.product_name 
          ORDER BY total_sold DESC
          LIMIT 20";
$result = mysqli_query($connect, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $stock_movement[] = $row;
    }
}

// Enhanced Expiry/Perishable Goods Report with loss calculations
$expiry_products = [];
$total_expiry_loss = 0;
$critical_expiry_count = 0;

$query = "SELECT p.product_name, p.quantity_per_pack, 
          COALESCE(p.exp_date, 'No expiry date') as expiry_date,
          p.exp_date,
          (p.quantity_per_pack * p.cost_price) as loss_value,
          (p.quantity_per_pack * p.price_per_sachet) as revenue_loss,
          pc.category,
          CASE 
              WHEN p.exp_date IS NULL THEN 'No Expiry'
              WHEN p.exp_date < CURDATE() THEN 'Expired'
              WHEN p.exp_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'Critical - 3 days'
              WHEN p.exp_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'Urgent - 7 days'
              WHEN p.exp_date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY) THEN 'Warning - 15 days'
              WHEN p.exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Monitor - 30 days'
              ELSE 'Safe'
          END as expiry_status,
          CASE 
              WHEN p.exp_date IS NULL THEN 999
              WHEN p.exp_date < CURDATE() THEN 0
              ELSE DATEDIFF(p.exp_date, CURDATE())
          END as days_to_expiry
          FROM products p 
          LEFT JOIN category pc ON p.category = pc.cat_id
          WHERE p.exp_date IS NULL OR p.exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
          ORDER BY 
              CASE 
                  WHEN p.exp_date IS NULL THEN 999
                  ELSE DATEDIFF(p.exp_date, CURDATE())
              END ASC";
$result = mysqli_query($connect, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $expiry_products[] = $row;
        if ($row['expiry_status'] == 'Expired' || strpos($row['expiry_status'], 'Critical') !== false) {
            $total_expiry_loss += $row['loss_value'];
            $critical_expiry_count++;
        }
    }
}

// Category-wise expiry analysis
$expiry_by_category = [];
$query = "SELECT 
              COALESCE(pc.category, 'Uncategorized') as category,
              COUNT(*) as total_products,
              SUM(CASE WHEN p.exp_date < CURDATE() THEN 1 ELSE 0 END) as expired_count,
              SUM(CASE WHEN p.exp_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as expiring_soon,
              SUM(CASE WHEN p.exp_date < CURDATE() THEN (p.quantity_per_pack * p.cost_price) ELSE 0 END) as expired_value
          FROM products p 
          LEFT JOIN category pc ON p.category = pc.cat_id
          WHERE p.exp_date IS NOT NULL AND p.exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
          GROUP BY pc.category
          ORDER BY expired_value DESC";
$result = mysqli_query($connect, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $expiry_by_category[] = $row;
    }
}

// Get Chart Data - Daily Sales for the month
$chart_data = [];
$query = "SELECT DATE(sale_date) as date, SUM(total_amount) as daily_sales 
          FROM sales 
          WHERE sale_date BETWEEN '$start_date' AND '$end_date' 
          GROUP BY DATE(sale_date) 
          ORDER BY DATE(sale_date)";
$result = mysqli_query($connect, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $chart_data[] = $row;
    }
}

// Get Product Distribution Data
$product_data = [];
$query = "SELECT p.product_name, SUM(si.quantity) as quantity_sold 
          FROM sale_details si 
          JOIN sales s ON si.sale_id = s.id 
          JOIN products p ON si.product_id = p.id 
          WHERE s.sale_date BETWEEN '$start_date' AND '$end_date' 
          GROUP BY p.id, p.product_name 
          ORDER BY quantity_sold DESC 
          LIMIT 10";
$result = mysqli_query($connect, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $product_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Report - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/css/style.css">

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
                <a href="../dashboard/index.php" class="nav-link" onclick="toggleSubmenu('dashboard')">
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
                <a href="#" class="nav-link active" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
                <div class="submenu show" id="reports">
                    <a href="../report/generateReport.php" class="nav-link"><i class="fas fa-file-export"></i> Generate Reports</a>
                    <a href="#" class="nav-link active"><i class="fas fa-file-import"></i> View Reports</a>
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

            <div class="content-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2>Monthly Sales Report</h2>
                        <p class="text-muted">Generated on: <?php echo date('F d, Y'); ?></p>
                    </div>
                    <!-- <div class="col-md-4 text-md-end btn-group">
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
                    </div> -->
                </div>

                <div class="col-md-6">
                    <br>
                    <p class="text-muted">Select date range</p>
                    <!-- Date Range Filter -->
                    <form method="GET" class="row g-2">
                        <div class="col-auto">
                            <input type="date" class="form-control form-control-sm" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-auto">
                            <input type="date" class="form-control form-control-sm" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary">Generate</button>
                        </div>
                    </form>
                </div>

                <br>
                <!-- Report Navigation -->
                <div class="mb-4">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-target="summary">Sales Summary</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-target="inventory-section">Inventory Reports</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-target="charts">Charts</button>
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
                                        <h3>₵<?php echo number_format($total_sales, 2); ?></h3>
                                        <p class="<?php echo $sales_growth >= 0 ? 'text-success' : 'text-danger'; ?> mb-0">
                                            <i class="fas fa-arrow-<?php echo $sales_growth >= 0 ? 'up' : 'down'; ?> me-1"></i>
                                            <?php echo number_format(abs($sales_growth), 1); ?>% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Total Orders</h6>
                                        <h3><?php echo number_format($total_orders); ?></h3>
                                        <p class="<?php echo $orders_growth >= 0 ? 'text-success' : 'text-danger'; ?> mb-0">
                                            <i class="fas fa-arrow-<?php echo $orders_growth >= 0 ? 'up' : 'down'; ?> me-1"></i>
                                            <?php echo number_format(abs($orders_growth), 1); ?>% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Average Order Value</h6>
                                        <h3>₵<?php echo number_format($average_order, 2); ?></h3>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-info-circle me-1"></i>Per transaction
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Gross Profit</h6>
                                        <h3>₵<?php echo number_format($gross_profit, 2); ?></h3>
                                        <p class="<?php echo $profit_growth >= 0 ? 'text-success' : 'text-danger'; ?> mb-0">
                                            <i class="fas fa-arrow-<?php echo $profit_growth >= 0 ? 'up' : 'down'; ?> me-1"></i>
                                            <?php echo number_format(abs($profit_growth), 1); ?>% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Items Sold</h6>
                                        <h3><?php echo number_format($items_sold); ?></h3>
                                        <p class="<?php echo $items_growth >= 0 ? 'text-success' : 'text-danger'; ?> mb-0">
                                            <i class="fas fa-arrow-<?php echo $items_growth >= 0 ? 'up' : 'down'; ?> me-1"></i>
                                            <?php echo number_format(abs($items_growth), 1); ?>% vs last period
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Profit Margin</h6>
                                        <h3><?php echo number_format($profit_margin, 1); ?>%</h3>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-percentage me-1"></i>Gross margin
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Top Selling Item</h6>
                                        <h3><?php echo $top_item_name; ?></h3>
                                        <p class="text-muted mb-0">
                                            <?php echo number_format($top_item_qty); ?> units sold
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Return Rate</h6>
                                        <h3><?php echo number_format($return_rate, 1); ?>%</h3>
                                        <p class="text-muted mb-0">
                                            Currently tracked
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Management Reports Section -->
                    <div id="inventory-section" class="mb-4" style="display: none;">
                        <!-- Sub-navigation for inventory reports -->
                        <div class="mb-3">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active inventory-tab" data-target="low-stock" href="#">Low Stock Alert</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link inventory-tab" data-target="inventory-value" href="#">Inventory Valuation</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link inventory-tab" data-target="stock-movement" href="#">Stock Movement</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link inventory-tab" data-target="expiry-alerts" href="#">Expiry Report</a>
                                </li>
                            </ul>
                        </div>

                        <!-- Enhanced Low Stock Alert Report -->
                        <div id="low-stock" class="inventory-report">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5><i class="fas fa-exclamation-triangle text-warning me-2"></i>Low Stock Alert Report</h5>
                                <div class="alert alert-info py-1 px-2 mb-0">
                                    <small><strong><?php echo count($low_stock_products); ?></strong> products require attention</small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="text-center">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Current Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Daily Usage</th>
                                            <th>Stockout Date</th>
                                            <th>Status</th>
                                            <!-- <th>Supplier</th> -->
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <?php if (empty($low_stock_products)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-success">
                                                    <i class="fas fa-check-circle me-2"></i>All products are adequately stocked
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($low_stock_products as $product): ?>
                                                <tr class="<?php echo $product['stock_status'] == 'Critical' ? 'table-danger' : ($product['stock_status'] == 'Very Low' ? 'table-warning' : ''); ?>">
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge bg-<?php echo $product['quantity_per_pack'] <= 0 ? 'danger' : 'secondary'; ?>">
                                                            <?php echo number_format($product['quantity_per_pack']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo number_format($product['low_stock_alert']); ?></td>
                                                    <td>
                                                        <?php if ($product['daily_consumption'] > 0): ?>
                                                            <?php echo number_format($product['daily_consumption'], 1); ?>/day
                                                        <?php else: ?>
                                                            <small class="text-muted">No data</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($product['projected_stockout']): ?>
                                                            <small class="text-danger">
                                                                <?php echo date('M d, Y', strtotime($product['projected_stockout'])); ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <small class="text-muted">Unknown</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge bg-<?php echo $product['stock_status'] == 'Critical' ? 'danger' : ($product['stock_status'] == 'Very Low' ? 'warning' : 'info'); ?>">
                                                            <?php echo $product['stock_status']; ?>
                                                        </span>
                                                    </td>
                                                    <!-- <td>
                                                        <small><?php echo htmlspecialchars($product['supplier']); ?></small>
                                                    </td> -->
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-sm" onclick="reorderProduct('<?php echo $product['product_name']; ?>')">
                                                            <i class="fas fa-shopping-cart"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Enhanced Inventory Valuation Report -->
                        <div id="inventory-value" class="inventory-report" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-dollar-sign text-success me-2"></i>Inventory Valuation Report</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <small class="text-muted d-block">Total Cost</small>
                                            <strong class="text-primary">₵<?php echo number_format($total_inventory_cost, 2); ?></strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Selling Value</small>
                                            <strong class="text-info">₵<?php echo number_format($total_inventory_selling, 2); ?></strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Potential Profit</small>
                                            <strong class="text-success">₵<?php echo number_format($total_potential_profit, 2); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="text-center">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Stock Qty</th>
                                            <th>Cost Value</th>
                                            <th>Selling Value</th>
                                            <th>Potential Profit</th>
                                            <th>Turnover</th>
                                            <th>Movement</th>
                                            <th>Last Sale</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <?php if (empty($inventory_valuation)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No inventory data found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($inventory_valuation as $item): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                                                    <td><?php echo number_format($item['quantity_per_pack']); ?></td>
                                                    <td>₵<?php echo number_format($item['cost_value'], 2); ?></td>
                                                    <td>₵<?php echo number_format($item['selling_value'], 2); ?></td>
                                                    <td class="text-success">₵<?php echo number_format($item['potential_profit'], 2); ?></td>
                                                    <td>
                                                        <span class="status-badge bg-<?php echo $item['turnover_ratio'] >= 2 ? 'success' : ($item['turnover_ratio'] >= 1 ? 'warning' : 'secondary'); ?>">
                                                            <?php echo $item['turnover_ratio']; ?>x
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge bg-<?php
                                                                                echo $item['movement_category'] == 'Dead Stock' ? 'danger' : ($item['movement_category'] == 'Fast Moving' ? 'success' : ($item['movement_category'] == 'Medium Moving' ? 'warning' : 'secondary')); ?>">
                                                            <?php echo $item['movement_category']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php
                                                            if ($item['last_sale_date'] != '1900-01-01') {
                                                                echo date('M d', strtotime($item['last_sale_date']));
                                                            } else {
                                                                echo 'Never';
                                                            }
                                                            ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Enhanced Stock Movement Report -->
                        <div id="stock-movement" class="inventory-report" style="display: none;">
                            <h5><i class="fas fa-exchange-alt text-info me-2"></i>Stock Movement Report</h5>
                            <br>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="text-center">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Total Sold</th>
                                            <th>Transactions</th>
                                            <th>Avg per Sale</th>
                                            <th>Daily Avg</th>
                                            <th>Weekly Avg</th>
                                            <th>Revenue</th>
                                            <th>Velocity</th>
                                            <th>Period</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <?php if (empty($stock_movement)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">No stock movement data found for this period</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($stock_movement as $movement): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($movement['product_name']); ?></strong></td>
                                                    <td>
                                                        <span class="status-badge bg-primary"><?php echo number_format($movement['total_sold']); ?></span>
                                                    </td>
                                                    <td><?php echo $movement['number_of_transactions']; ?></td>
                                                    <td><?php echo number_format($movement['avg_per_sale'], 1); ?></td>
                                                    <td><?php echo number_format($movement['daily_avg'], 1); ?></td>
                                                    <td><?php echo number_format($movement['weekly_avg'], 1); ?></td>
                                                    <td class="text-success">₵<?php echo number_format($movement['total_revenue'], 2); ?></td>
                                                    <td>
                                                        <span class="status-badge bg-<?php
                                                                                echo $movement['velocity_rating'] == 'High Velocity' ? 'success' : ($movement['velocity_rating'] == 'Medium Velocity' ? 'warning' : 'secondary'); ?>">
                                                            <?php echo str_replace(' Velocity', '', $movement['velocity_rating']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('M d', strtotime($movement['first_sale'])); ?> -
                                                            <?php echo date('M d', strtotime($movement['last_sale'])); ?>
                                                            <br>
                                                            <span class="status-badge bg-light text-dark"><?php echo $movement['days_sold']; ?> days</span>
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Expiry/Perishable Goods Report -->
                        <div id="expiry-alerts" class="inventory-report" style="display: none;">
                            <h5><i class="fas fa-clock text-danger me-2"></i>Expiry/Perishable Goods Report</h5>
                            <br>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="text-center">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Stock Qty</th>
                                            <th>Expiry Date</th>
                                            <th>Status</th>
                                            <th>Loss Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <?php if (empty($expiry_products)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No products approaching expiry</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($expiry_products as $product): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                    <td><?php echo number_format($product['quantity_per_pack']); ?></td>
                                                    <td>
                                                        <?php
                                                        if ($product['exp_date'] != 'No expiry date') {
                                                            echo date('M d, Y', strtotime($product['exp_date']));
                                                        } else {
                                                            echo $product['exp_date'];
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge bg-<?php
                                                                                echo $product['expiry_status'] == 'Expired' ? 'danger' : ($product['expiry_status'] == 'Expires in 7 days' ? 'danger' : ($product['expiry_status'] == 'Expires in 15 days' ? 'warning' : ($product['expiry_status'] == 'Expires in 30 days' ? 'info' : 'secondary')));
                                                                                ?>">
                                                            <?php echo $product['expiry_status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-danger">₵<?php echo number_format($product['loss_value'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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
                                        <h5 class="card-title">Top Products</h5>
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

        document.addEventListener('DOMContentLoaded', () => {
            // Main tab navigation (Sales Summary, Inventory Reports, Charts)
            document.querySelectorAll('.nav-tabs .nav-link').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Remove active class from all main tabs
                    document.querySelectorAll('.nav-tabs .nav-link').forEach(b => {
                        b.classList.remove('active');
                    });

                    // Hide all main sections
                    document.getElementById('summary').style.display = 'none';
                    const inventorySection = document.getElementById('inventory-section'); // Fixed ID
                    if (inventorySection) {
                        inventorySection.style.display = 'none';
                    }
                    document.getElementById('charts').style.display = 'none';

                    // Add active class to clicked tab
                    button.classList.add('active');

                    // Show the selected section based on data-target
                    const targetId = button.getAttribute('data-target');
                    if (targetId === 'summary') {
                        document.getElementById('summary').style.display = 'block';
                    } else if (targetId === 'inventory-section') { // Fixed target check
                        if (inventorySection) {
                            inventorySection.style.display = 'block';
                            // Show the first inventory report by default
                            showInventoryReport('low-stock');
                        }
                    } else if (targetId === 'charts') {
                        document.getElementById('charts').style.display = 'block';
                        // Initialize charts when charts section is shown
                        setTimeout(initializeCharts, 100); // Small delay to ensure canvas is visible
                    }
                });
            });

            // Inventory sub-tab navigation
            document.querySelectorAll('.inventory-tab').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Remove active class from all inventory tabs
                    document.querySelectorAll('.inventory-tab').forEach(l => {
                        l.classList.remove('active');
                    });

                    // Add active class to clicked tab
                    link.classList.add('active');

                    // Show the selected inventory report
                    const targetId = link.getAttribute('data-target');
                    showInventoryReport(targetId);
                });
            });

            // Function to show specific inventory report
            function showInventoryReport(reportId) {
                // Hide all inventory reports
                document.querySelectorAll('.inventory-report').forEach(report => {
                    report.style.display = 'none';
                });

                // Show selected report
                const targetReport = document.getElementById(reportId);
                if (targetReport) {
                    targetReport.style.display = 'block';
                }
            }

            // Initialize default view - show summary section
            document.getElementById('summary').style.display = 'block';
            const inventorySection = document.getElementById('inventory-section');
            if (inventorySection) {
                inventorySection.style.display = 'none';
            }
            document.getElementById('charts').style.display = 'none';

            // Initialize inventory section - show first report by default when inventory section is loaded
            const firstInventoryReport = document.getElementById('low-stock');
            if (firstInventoryReport) {
                firstInventoryReport.style.display = 'block';
                // Hide other inventory reports initially
                document.querySelectorAll('.inventory-report').forEach(report => {
                    if (report.id !== 'low-stock') {
                        report.style.display = 'none';
                    }
                });
            }

            // Initialize charts if charts section is visible
            setTimeout(initializeCharts, 100);
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

        // Reorder product functionality
        function reorderProduct(productName) {
            alert(`Initiating reorder for: ${productName}`);
            // Add your reorder logic here
        }

        // Initialize charts
        function initializeCharts() {
            const salesCanvas = document.getElementById('salesChart');
            const productCanvas = document.getElementById('productChart');

            if (!salesCanvas || !productCanvas) {
                return; // Exit if canvases don't exist yet
            }

            // Check if charts already exist and destroy them
            if (window.salesChart instanceof Chart) {
                window.salesChart.destroy();
            }
            if (window.productChart instanceof Chart) {
                window.productChart.destroy();
            }

            // Sales Trend Chart
            const salesCtx = salesCanvas.getContext('2d');
            window.salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($chart_data, 'date')); ?>,
                    datasets: [{
                        label: 'Daily Sales (₵)',
                        data: <?php echo json_encode(array_column($chart_data, 'daily_sales')); ?>,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₵' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Sales: ₵' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Product Distribution Chart
            const productCtx = productCanvas.getContext('2d');
            window.productChart = new Chart(productCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($product_data, 'product_name')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($product_data, 'quantity_sold')); ?>,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)',
                            'rgb(255, 159, 64)',
                            'rgb(201, 203, 207)',
                            'rgb(255, 99, 255)',
                            'rgb(99, 255, 132)',
                            'rgb(132, 99, 255)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + ' units';
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>