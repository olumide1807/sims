<?php
session_start();
include "../config/session_check.php";
include "../config/config.php";

// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add after session_start() and includes
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("POST data received: " . print_r($_POST, true));
}

// Function to calculate date ranges
function getDateRange($period)
{
    $today = date('Y-m-d');
    $start_date = '';
    $end_date = $today;

    switch ($period) {
        case 'yesterday':
            $start_date = date('Y-m-d', strtotime('-1 day'));
            $end_date = date('Y-m-d', strtotime('-1 day'));
            break;
        case 'last_week':
            $start_date = date('Y-m-d', strtotime('-1 week'));
            break;
        case 'last_month':
            $start_date = date('Y-m-d', strtotime('-1 month'));
            break;
        case 'last_quarter':
            $start_date = date('Y-m-d', strtotime('-3 months'));
            break;
        case 'last_year':
            $start_date = date('Y-m-d', strtotime('-1 year'));
            break;
        default:
            $start_date = $today;
            break;
    }

    return array('start' => $start_date, 'end' => $end_date);
}

// Add this function to generate chart data and HTML
function generateChartHTML($report_data, $report_type, $chart_id)
{
    $chart_html = '';

    switch ($report_type) {
        case 'sales_summary':
            if (isset($report_data['top_products']) && !empty($report_data['top_products'])) {
                // Revenue Chart
                $labels = array();
                $revenues = array();
                $quantities = array();

                foreach (array_slice($report_data['top_products'], 0, 10) as $product) {
                    $labels[] = $product['product_name'];
                    $revenues[] = $product['total_revenue'];
                    $quantities[] = $product['total_sold'];
                }

                $chart_html .= "
                    <div class='chart-grid'>
                        <div class='chart-container'>
                            <h4>Top Products by Revenue</h4>
                            <canvas id='revenueChart{$chart_id}'></canvas>
                        </div>
                        <div class='chart-container'>
                            <h4>Top Products by Quantity Sold</h4>
                            <canvas id='quantityChart{$chart_id}'></canvas>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Revenue Chart
                            new Chart(document.getElementById('revenueChart{$chart_id}'), {
                                type: 'bar',
                                data: {
                                    labels: " . json_encode($labels) . ",
                                    datasets: [{
                                        label: 'Revenue ($)',
                                        data: " . json_encode($revenues) . ",
                                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                            
                            // Quantity Chart
                            new Chart(document.getElementById('quantityChart{$chart_id}'), {
                                type: 'bar',
                                data: {
                                    labels: " . json_encode($labels) . ",
                                    datasets: [{
                                        label: 'Quantity Sold',
                                        data: " . json_encode($quantities) . ",
                                        backgroundColor: 'rgba(255, 99, 132, 0.8)',
                                        borderColor: 'rgba(255, 99, 132, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    indexAxis: 'y',
                                    responsive: true,
                                    scales: {
                                        x: { beginAtZero: true }
                                    }
                                }
                            });
                        });
                    </script>
                ";
            }
            break;

        case 'inventory_valuation':
            if (isset($report_data['inventory_details']) && !empty($report_data['inventory_details'])) {
                // Category distribution
                $categories = array();
                $category_values = array();
                $category_counts = array();

                foreach ($report_data['inventory_details'] as $item) {
                    $cat = $item['category'];
                    if (!isset($categories[$cat])) {
                        $categories[$cat] = 0;
                        $category_counts[$cat] = 0;
                    }
                    $categories[$cat] += $item['inventory_value'];
                    $category_counts[$cat]++;
                }

                $chart_html .= "
                    <div class='chart-grid'>
                        <div class='chart-container'>
                            <h4>Inventory Value by Category</h4>
                            <canvas id='categoryValueChart{$chart_id}'></canvas>
                        </div>
                        <div class='chart-container'>
                            <h4>Product Count by Category</h4>
                            <canvas id='categoryCountChart{$chart_id}'></canvas>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Category Value Chart
                            new Chart(document.getElementById('categoryValueChart{$chart_id}'), {
                                type: 'doughnut',
                                data: {
                                    labels: " . json_encode(array_keys($categories)) . ",
                                    datasets: [{
                                        data: " . json_encode(array_values($categories)) . ",
                                        backgroundColor: [
                                            'rgba(255, 99, 132, 0.8)',
                                            'rgba(54, 162, 235, 0.8)',
                                            'rgba(255, 205, 86, 0.8)',
                                            'rgba(75, 192, 192, 0.8)',
                                            'rgba(153, 102, 255, 0.8)'
                                        ]
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: { position: 'bottom' }
                                    }
                                }
                            });
                            
                            // Category Count Chart
                            new Chart(document.getElementById('categoryCountChart{$chart_id}'), {
                                type: 'pie',
                                data: {
                                    labels: " . json_encode(array_keys($category_counts)) . ",
                                    datasets: [{
                                        data: " . json_encode(array_values($category_counts)) . ",
                                        backgroundColor: [
                                            'rgba(255, 99, 132, 0.8)',
                                            'rgba(54, 162, 235, 0.8)',
                                            'rgba(255, 205, 86, 0.8)',
                                            'rgba(75, 192, 192, 0.8)',
                                            'rgba(153, 102, 255, 0.8)'
                                        ]
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: { position: 'bottom' }
                                    }
                                }
                            });
                        });
                    </script>
                ";
            }
            break;

        case 'low_stock':
            // Stock levels chart
            $low_stock_items = isset($report_data['low_stock_items']) ? $report_data['low_stock_items'] : array();
            $out_of_stock_items = isset($report_data['out_of_stock']) ? $report_data['out_of_stock'] : array();

            // Start the flex container for side-by-side layout
            $chart_html .= "<div class='charts-row' style='display: flex; gap: 20px; flex-wrap: wrap;'>";

            if (!empty($low_stock_items)) {
                $product_names = array();
                $current_stocks = array();
                $min_stocks = array();

                foreach (array_slice($low_stock_items, 0, 15) as $item) {
                    $product_names[] = $item['product_name'];
                    $current_stocks[] = $item['quantity_per_pack'];
                    $min_stocks[] = $item['minimum_stock'];
                }

                $chart_html .= "
                    <div class='chart-container' style='flex: 1; min-width: 400px;'>
                        <h4>Current vs Minimum Stock Levels</h4>
                        <canvas id='stockLevelsChart{$chart_id}'></canvas>
                    </div>
                ";
            }

            // Stock status overview
            $summary = isset($report_data['summary']) ? $report_data['summary'] : array();
            if (!empty($summary)) {
                $chart_html .= "
                    <div class='chart-container' style='flex: 1; min-width: 300px;'>
                        <h4>Stock Status Overview</h4>
                        <canvas id='stockStatusChart{$chart_id}'></canvas>
                    </div>
                ";
            }

            // Close the flex container
            $chart_html .= "</div>";

            // Add the JavaScript for both charts
            $chart_html .= "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
            ";

            // Stock levels chart script
            if (!empty($low_stock_items)) {
                $chart_html .= "
                    new Chart(document.getElementById('stockLevelsChart{$chart_id}'), {
                        type: 'bar',
                        data: {
                            labels: " . json_encode($product_names) . ",
                            datasets: [{
                                label: 'Current Stock',
                                data: " . json_encode($current_stocks) . ",
                                backgroundColor: 'rgba(255, 99, 132, 0.8)'
                            }, {
                                label: 'Minimum Stock',
                                data: " . json_encode($min_stocks) . ",
                                backgroundColor: 'rgba(54, 162, 235, 0.8)'
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                ";
            }

            // Stock status chart script
            if (!empty($summary)) {
                $chart_html .= "
                    new Chart(document.getElementById('stockStatusChart{$chart_id}'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Low Stock Items', 'Out of Stock Items'],
                            datasets: [{
                                data: [" . $summary['low_stock_count'] . ", " . $summary['out_of_stock_count'] . "],
                                backgroundColor: ['rgba(255, 205, 86, 0.8)', 'rgba(255, 99, 132, 0.8)']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                ";
            }

            $chart_html .= "
                });
                </script>
            ";
            break;

        case 'stock_movement':
            if (isset($report_data['movement_summary']) && !empty($report_data['movement_summary'])) {
                $products = array();
                $sold_quantities = array();
                $revenues = array();

                foreach (array_slice($report_data['movement_summary'], 0, 10) as $item) {
                    $products[] = $item['product_name'];
                    $sold_quantities[] = $item['total_sold'];
                    $revenues[] = $item['total_revenue'];
                }

                $chart_html .= "
                    <div class='chart-grid'>
                        <div class='chart-container'>
                            <h4>Products Sold (Quantity)</h4>
                            <canvas id='soldQuantityChart{$chart_id}'></canvas>
                        </div>
                        <div class='chart-container'>
                            <h4>Revenue by Product</h4>
                            <canvas id='movementRevenueChart{$chart_id}'></canvas>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            new Chart(document.getElementById('soldQuantityChart{$chart_id}'), {
                                type: 'bar',
                                data: {
                                    labels: " . json_encode($products) . ",
                                    datasets: [{
                                        label: 'Quantity Sold',
                                        data: " . json_encode($sold_quantities) . ",
                                        backgroundColor: 'rgba(75, 192, 192, 0.8)'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                            
                            new Chart(document.getElementById('movementRevenueChart{$chart_id}'), {
                                type: 'line',
                                data: {
                                    labels: " . json_encode($products) . ",
                                    datasets: [{
                                        label: 'Revenue ($)',
                                        data: " . json_encode($revenues) . ",
                                        borderColor: 'rgba(153, 102, 255, 1)',
                                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                        fill: true
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                        });
                    </script>
                ";
            }
            break;

        case 'expiry_report':
            $expiring_items = isset($report_data['expiring_items']) ? $report_data['expiring_items'] : array();
            $expired_items = isset($report_data['expired_items']) ? $report_data['expired_items'] : array();

            // Start the flex container for side-by-side layout
            $chart_html .= "<div class='charts-row' style='display: flex; gap: 20px; flex-wrap: wrap;'>";

            if (!empty($expiring_items) || !empty($expired_items)) {
                $chart_html .= "
                    <div class='chart-container' style='flex: 1; min-width: 300px;'>
                        <h4>Expiry Status Overview</h4>
                        <canvas id='expiryStatusChart{$chart_id}'></canvas>
                    </div>
                ";
            }

            // Days to expiry timeline for expiring items
            if (!empty($expiring_items)) {
                $products = array();
                $days_to_expiry = array();

                foreach (array_slice($expiring_items, 0, 10) as $item) {
                    $products[] = $item['product_name'];
                    $days_to_expiry[] = $item['days_to_expiry'];
                }

                $chart_html .= "
                    <div class='chart-container' style='flex: 1; min-width: 400px;'>
                        <h4>Days Until Expiry</h4>
                        <canvas id='expiryTimelineChart{$chart_id}'></canvas>
                    </div>
                ";
            }

            // Close the flex container
            $chart_html .= "</div>";

            // Add the JavaScript for both charts
            $chart_html .= "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
            ";

            // Expiry status chart script
            if (!empty($expiring_items) || !empty($expired_items)) {
                $chart_html .= "
                    new Chart(document.getElementById('expiryStatusChart{$chart_id}'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Expiring Soon', 'Already Expired'],
                            datasets: [{
                                data: [" . count($expiring_items) . ", " . count($expired_items) . "],
                                backgroundColor: ['rgba(255, 205, 86, 0.8)', 'rgba(255, 99, 132, 0.8)']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                ";
            }

            // Days to expiry timeline chart script
            if (!empty($expiring_items)) {
                $chart_html .= "
                    new Chart(document.getElementById('expiryTimelineChart{$chart_id}'), {
                        type: 'bar',
                        data: {
                            labels: " . json_encode($products) . ",
                            datasets: [{
                                label: 'Days to Expiry',
                                data: " . json_encode($days_to_expiry) . ",
                                backgroundColor: 'rgba(255, 159, 64, 0.8)'
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                ";
            }

            $chart_html .= "
                    });
                </script>
            ";
            break;
    }

    return $chart_html;
}

// Function to generate sales summary report
function generateSalesSummaryReport($connect, $start_date, $end_date)
{
    $data = array();

    // Total sales - Check if sales table exists and has data
    $sql = "SELECT 
                COUNT(*) as total_transactions,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(AVG(total_amount), 0) as average_transaction
            FROM sales 
            WHERE sale_date BETWEEN ? AND ?";

    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && $row = mysqli_fetch_assoc($result)) {
            $data['summary'] = $row;
        } else {
            $data['summary'] = array('total_transactions' => 0, 'total_revenue' => 0, 'average_transaction' => 0);
        }
        mysqli_stmt_close($stmt);
    } else {
        // Fallback if sales table doesn't exist
        $data['summary'] = array('total_transactions' => 0, 'total_revenue' => 0, 'average_transaction' => 0);
    }

    // Top selling products - Try to get from sales_items or similar table
    $sql = "SELECT 
                p.product_name,
                p.category,
                COUNT(si.id) as total_sold,
                SUM(si.quantity * si.unit_price) as total_revenue
            FROM products p
            LEFT JOIN sale_details si ON p.id = si.product_id
            LEFT JOIN sales s ON si.sale_id = s.id
            WHERE s.sale_date BETWEEN ? AND ?
            GROUP BY p.id, p.product_name, p.category
            ORDER BY total_revenue DESC
            LIMIT 10";

    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $data['top_products'] = array();
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['total_revenue'] > 0) {
                $data['top_products'][] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }

    // If no sales data found, show message instead of mock data
    if (empty($data['top_products'])) {
        $data['top_products'] = array();
        $data['no_sales_data'] = true;
    }

    return $data;
}

// Function to generate inventory valuation report
function generateInventoryValuationReport($connect)
{
    $data = array();

    // Get actual inventory data
    $sql = "SELECT 
                product_name,
                category,
                quantity_per_pack,
                COALESCE(cost_price, 0) as cost_price,
                COALESCE(price_per_sachet, 0) as selling_price,
                (COALESCE(quantity_per_pack, 0) * COALESCE(cost_price, 0)) as inventory_value,
                (COALESCE(quantity_per_pack, 0) * COALESCE(price_per_sachet, 0)) as potential_revenue
            FROM products
            WHERE quantity_per_pack >= 0
            ORDER BY inventory_value DESC";

    $result = mysqli_query($connect, $sql);
    $data['inventory_details'] = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data['inventory_details'][] = $row;
        }
    }

    // Calculate summary from actual data
    if (!empty($data['inventory_details'])) {
        $total_inventory_value = 0;
        $total_potential_revenue = 0;
        $total_products = count($data['inventory_details']);
        $total_units = 0;

        foreach ($data['inventory_details'] as $item) {
            $total_inventory_value += $item['inventory_value'];
            $total_potential_revenue += $item['potential_revenue'];
            $total_units += $item['quantity_per_pack'];
        }

        $data['summary'] = array(
            'total_inventory_value' => $total_inventory_value,
            'total_potential_revenue' => $total_potential_revenue,
            'total_products' => $total_products,
            'total_units' => $total_units
        );
    } else {
        $data['summary'] = array(
            'total_inventory_value' => 0,
            'total_potential_revenue' => 0,
            'total_products' => 0,
            'total_units' => 0
        );
        $data['no_inventory_data'] = true;
    }

    return $data;
}

// Function to generate low stock report
function generateLowStockReport($connect)
{
    $data = array();

    // Get products with low stock (using reorder_level if available, otherwise default to 10)
    $sql = "SELECT 
                product_name,
                category,
                quantity_per_pack,
                COALESCE(low_stock_alert, 10) as minimum_stock,
                COALESCE(cost_price, 0) as cost_price,
                COALESCE(price_per_sachet, 0) as selling_price,
                'N/A' as supplier
            FROM products 
            WHERE quantity_per_pack <= COALESCE(low_stock_alert, 10) 
                AND quantity_per_pack > 0
            ORDER BY quantity_per_pack ASC";

    $result = mysqli_query($connect, $sql);
    $data['low_stock_items'] = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data['low_stock_items'][] = $row;
        }
    }

    // Get out of stock items
    $sql = "SELECT 
                product_name,
                category,
                COALESCE(cost_price, 0) as cost_price,
                COALESCE(price_per_sachet, 0) as selling_price,
                'N/A' as supplier
            FROM products 
            WHERE quantity_per_pack = 0
            ORDER BY product_name";

    $result = mysqli_query($connect, $sql);
    $data['out_of_stock'] = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data['out_of_stock'][] = $row;
        }
    }

    // Add summary counts
    $data['summary'] = array(
        'low_stock_count' => count($data['low_stock_items']),
        'out_of_stock_count' => count($data['out_of_stock'])
    );

    return $data;
}

// Function to generate stock movement report
function generateStockMovementReport($connect, $start_date, $end_date)
{
    $data = array();

    // Get sales movements (stock going out)
    $sql = "SELECT 
                p.product_name,
                p.category,
                'SALE' as movement_type,
                SUM(sd.quantity) as total_quantity,
                SUM(sd.quantity * sd.unit_price) as total_value,
                COUNT(s.id) as transaction_count,
                s.sale_date as movement_date
            FROM products p
            INNER JOIN sale_details sd ON p.id = sd.product_id
            INNER JOIN sales s ON sd.sale_id = s.id
            WHERE s.sale_date BETWEEN ? AND ?
            GROUP BY p.id, p.product_name, p.category, s.sale_date
            ORDER BY s.sale_date DESC";

    $stmt = mysqli_prepare($connect, $sql);
    $data['movements'] = array();

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $data['movements'][] = array(
                'product_name' => $row['product_name'],
                'category' => $row['category'],
                'movement_type' => $row['movement_type'],
                'quantity' => $row['total_quantity'],
                'value' => $row['total_value'],
                'movement_date' => $row['movement_date'],
                'transaction_count' => $row['transaction_count']
            );
        }
        mysqli_stmt_close($stmt);
    }

    // Get current stock levels for comparison
    $sql = "SELECT 
                product_name,
                category,
                quantity_per_pack as current_stock,
                COALESCE(cost_price, 0) as cost_price,
                COALESCE(price_per_sachet, 0) as selling_price
            FROM products
            ORDER BY product_name";

    $result = mysqli_query($connect, $sql);
    $data['current_stock'] = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data['current_stock'][] = $row;
        }
    }

    // Movement summary by product
    $sql = "SELECT 
                p.product_name,
                p.category,
                p.quantity_per_pack as current_stock,
                COALESCE(SUM(sd.quantity), 0) as total_sold,
                COUNT(DISTINCT s.id) as transaction_count,
                COALESCE(SUM(sd.quantity * sd.unit_price), 0) as total_revenue
            FROM products p
            LEFT JOIN sale_details sd ON p.id = sd.product_id
            LEFT JOIN sales s ON sd.sale_id = s.id AND s.sale_date BETWEEN ? AND ?
            GROUP BY p.id, p.product_name, p.category, p.quantity_per_pack
            HAVING total_sold > 0
            ORDER BY total_sold DESC";

    $stmt = mysqli_prepare($connect, $sql);
    $data['movement_summary'] = array();

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $data['movement_summary'][] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    // Add no data flags if empty
    if (empty($data['movements']) && empty($data['movement_summary'])) {
        $data['no_movement_data'] = true;
    }

    return $data;
}

// Function to generate expiry report
function generateExpiryReport($connect)
{
    $data = array();

    // Check if expiry_date column exists in products table
    $expiry_column_exists = false;
    $columns_query = "SHOW COLUMNS FROM products LIKE 'exp_date'";
    $columns_result = mysqli_query($connect, $columns_query);
    if ($columns_result && mysqli_num_rows($columns_result) > 0) {
        $expiry_column_exists = true;
    }

    if ($expiry_column_exists) {
        // Get items expiring within next 30 days
        $sql = "SELECT 
                    product_name,
                    category,
                    quantity_per_pack,
                    exp_date,
                    DATEDIFF(exp_date, CURDATE()) as days_to_expiry,
                    (quantity_per_pack * COALESCE(cost_price, 0)) as potential_loss
                FROM products 
                WHERE exp_date IS NOT NULL 
                    AND exp_date > CURDATE() 
                    AND exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                    AND quantity_per_pack > 0
                ORDER BY days_to_expiry ASC";

        $result = mysqli_query($connect, $sql);
        $data['expiring_items'] = array();

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data['expiring_items'][] = $row;
            }
        }

        // Get already expired items with stock
        $sql = "SELECT 
                    product_name,
                    category,
                    quantity_per_pack,
                    exp_date,
                    ABS(DATEDIFF(CURDATE(), exp_date)) as days_expired,
                    (quantity_per_pack * COALESCE(cost_price, 0)) as actual_loss
                FROM products 
                WHERE exp_date IS NOT NULL 
                    AND exp_date < CURDATE()
                    AND quantity_per_pack > 0
                ORDER BY days_expired DESC";

        $result = mysqli_query($connect, $sql);
        $data['expired_items'] = array();

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data['expired_items'][] = $row;
            }
        }
    } else {
        // If no expiry_date column, create mock data with warning
        $data['no_expiry_column'] = true;
        $data['expiring_items'] = array();
        $data['expired_items'] = array();
    }

    // Calculate summary
    $total_expiring = count($data['expiring_items']);
    $total_expired = count($data['expired_items']);
    $potential_loss = 0;
    $actual_loss = 0;

    foreach ($data['expiring_items'] as $item) {
        $potential_loss += $item['potential_loss'];
    }

    foreach ($data['expired_items'] as $item) {
        $actual_loss += $item['actual_loss'];
    }

    $data['summary'] = array(
        'total_expiring' => $total_expiring,
        'total_expired' => $total_expired,
        'potential_loss' => $potential_loss,
        'actual_loss' => $actual_loss
    );

    return $data;
}

function generateComprehensiveReport($connect, $start_date, $end_date)
{
    $data = array();

    // Get all report data
    $data['sales'] = generateSalesSummaryReport($connect, $start_date, $end_date);
    $data['inventory'] = generateInventoryValuationReport($connect);
    $data['low_stock'] = generateLowStockReport($connect);
    $data['stock_movement'] = generateStockMovementReport($connect, $start_date, $end_date);
    $data['expiry'] = generateExpiryReport($connect);

    // Create executive summary
    $data['executive_summary'] = array(
        'total_products' => $data['inventory']['summary']['total_products'],
        'total_inventory_value' => $data['inventory']['summary']['total_inventory_value'],
        'total_sales' => $data['sales']['summary']['total_revenue'],
        'total_transactions' => $data['sales']['summary']['total_transactions'],
        'low_stock_alerts' => $data['low_stock']['summary']['low_stock_count'],
        'out_of_stock_alerts' => $data['low_stock']['summary']['out_of_stock_count'],
        'expiring_items' => $data['expiry']['summary']['total_expiring'],
        'expired_items' => $data['expiry']['summary']['total_expired']
    );

    return $data;
}

// Function to generate HTML report
function generateHTMLReport($report_data, $report_type, $include_charts = true, $include_summary = true)
{
    $html = "<!DOCTYPE html>
    <html>
    <head>
        <title>" . ucwords(str_replace('_', ' ', $report_type)) . " Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }
            .summary-box { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .no-data { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 20px 0; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .number { text-align: right; }
            
            .chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
            .chart-container { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .chart-container h4 { margin-top: 0; text-align: center; }
            .chart-container canvas { max-height: 400px; }
            
            @media print { body { margin: 0; } }
        </style>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js'></script>
    </head>
    <body>
        <div class='header'>
            <h1>" . ucwords(str_replace('_', ' ', $report_type)) . " Report</h1>
            <p>Generated on: " . date('Y-m-d H:i:s') . "</p>
        </div>";

    // Generate content based on report type
    switch ($report_type) {
        case 'sales_summary':
            if ($include_summary && isset($report_data['summary'])) {
                $summary = $report_data['summary'];
                $html .= "<div class='summary-box'>
                    <h3>Sales Summary</h3>
                    <p><strong>Total Transactions:</strong> " . number_format($summary['total_transactions']) . "</p>
                    <p><strong>Total Revenue:</strong> $" . number_format($summary['total_revenue'], 2) . "</p>
                    <p><strong>Average Transaction:</strong> $" . number_format($summary['average_transaction'], 2) . "</p>
                </div>";
            }

            if (isset($report_data['no_sales_data'])) {
                $html .= "<div class='no-data'>
                    <strong>Notice:</strong> No sales data found for the selected period.
                </div>";
            } elseif (isset($report_data['top_products']) && !empty($report_data['top_products'])) {
                $html .= "<h3>Top Selling Products</h3>
                <table>
                    <tr><th>Product Name</th><th>Category</th><th>Quantity Sold</th><th>Revenue</th></tr>";
                foreach ($report_data['top_products'] as $product) {
                    $html .= "<tr>
                        <td>" . htmlspecialchars($product['product_name']) . "</td>
                        <td>" . htmlspecialchars($product['category']) . "</td>
                        <td class='number'>" . number_format($product['total_sold']) . "</td>
                        <td class='number'>$" . number_format($product['total_revenue'], 2) . "</td>
                    </tr>";
                }
                $html .= "</table>";
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data, 'sales_summary', 'sales');
            }
            break;

        case 'inventory_valuation':
            if ($include_summary && isset($report_data['summary'])) {
                $summary = $report_data['summary'];
                $html .= "<div class='summary-box'>
                    <h3>Inventory Summary</h3>
                    <p><strong>Total Products:</strong> " . number_format($summary['total_products']) . "</p>
                    <p><strong>Total Units:</strong> " . number_format($summary['total_units']) . "</p>
                    <p><strong>Total Inventory Value:</strong> $" . number_format($summary['total_inventory_value'], 2) . "</p>
                    <p><strong>Potential Revenue:</strong> $" . number_format($summary['total_potential_revenue'], 2) . "</p>
                </div>";
            }

            if (isset($report_data['no_inventory_data'])) {
                $html .= "<div class='no-data'>
                    <strong>Notice:</strong> No inventory data found.
                </div>";
            } elseif (isset($report_data['inventory_details']) && !empty($report_data['inventory_details'])) {
                $html .= "<h3>Inventory Details</h3>
                <table>
                    <tr><th>Product</th><th>Category</th><th>Stock</th><th>Cost Price</th><th>Selling Price</th><th>Inventory Value</th></tr>";
                foreach ($report_data['inventory_details'] as $item) {
                    $html .= "<tr>
                        <td>" . htmlspecialchars($item['product_name']) . "</td>
                        <td>" . htmlspecialchars($item['category']) . "</td>
                        <td class='number'>" . number_format($item['quantity_per_pack']) . "</td>
                        <td class='number'>$" . number_format($item['cost_price'], 2) . "</td>
                        <td class='number'>$" . number_format($item['selling_price'], 2) . "</td>
                        <td class='number'>$" . number_format($item['inventory_value'], 2) . "</td>
                    </tr>";
                }
                $html .= "</table>";
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data, 'inventory_valuation', 'inventory');
            }
            break;

        case 'low_stock':
            if (isset($report_data['summary'])) {
                $summary = $report_data['summary'];
                $html .= "<div class='summary-box'>
                    <h3>Stock Alert Summary</h3>
                    <p><strong>Low Stock Items:</strong> " . $summary['low_stock_count'] . "</p>
                    <p><strong>Out of Stock Items:</strong> " . $summary['out_of_stock_count'] . "</p>
                </div>";
            }

            if (isset($report_data['low_stock_items']) && !empty($report_data['low_stock_items'])) {
                $html .= "<h3>Low Stock Items</h3>
                <table>
                    <tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Minimum Stock</th><th>Supplier</th></tr>";
                foreach ($report_data['low_stock_items'] as $item) {
                    $html .= "<tr>
                        <td>" . htmlspecialchars($item['product_name']) . "</td>
                        <td>" . htmlspecialchars($item['category']) . "</td>
                        <td class='number'>" . number_format($item['quantity_per_pack']) . "</td>
                        <td class='number'>" . number_format($item['minimum_stock']) . "</td>
                        <td>" . htmlspecialchars($item['supplier']) . "</td>
                    </tr>";
                }
                $html .= "</table>";
            } else {
                $html .= "<div class='no-data'>
                    <strong>Good News:</strong> No low stock items found.
                </div>";
            }

            if (isset($report_data['out_of_stock']) && !empty($report_data['out_of_stock'])) {
                $html .= "<h3>Out of Stock Items</h3>
                <table>
                    <tr><th>Product</th><th>Category</th><th>Supplier</th></tr>";
                foreach ($report_data['out_of_stock'] as $item) {
                    $html .= "<tr>
                        <td>" . htmlspecialchars($item['product_name']) . "</td>
                        <td>" . htmlspecialchars($item['category']) . "</td>
                        <td>" . htmlspecialchars($item['supplier']) . "</td>
                    </tr>";
                }
                $html .= "</table>";
            } else {
                $html .= "<div class='no-data'>
                    <strong>Good News:</strong> No out of stock items found.
                </div>";
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data, 'low_stock', 'lowstock');
            }
            break;

        case 'stock_movement':
            if (isset($report_data['no_movement_data'])) {
                $html .= "<div class='no-data'>
            <strong>Notice:</strong> No stock movement data found for the selected period.
        </div>";
            } else {
                // Movement Summary
                if (isset($report_data['movement_summary']) && !empty($report_data['movement_summary'])) {
                    $html .= "<h3>Stock Movement Summary</h3>
            <table>
                <tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Total Sold</th><th>Transactions</th><th>Revenue</th></tr>";
                    foreach ($report_data['movement_summary'] as $item) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . htmlspecialchars($item['category']) . "</td>
                    <td class='number'>" . number_format($item['current_stock']) . "</td>
                    <td class='number'>" . number_format($item['total_sold']) . "</td>
                    <td class='number'>" . number_format($item['transaction_count']) . "</td>
                    <td class='number'>$" . number_format($item['total_revenue'], 2) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                }

                // Detailed Movements
                if (isset($report_data['movements']) && !empty($report_data['movements'])) {
                    $html .= "<h3>Detailed Stock Movements</h3>
            <table>
                <tr><th>Product</th><th>Category</th><th>Type</th><th>Quantity</th><th>Value</th><th>Date</th></tr>";
                    foreach ($report_data['movements'] as $movement) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($movement['product_name']) . "</td>
                    <td>" . htmlspecialchars($movement['category']) . "</td>
                    <td>" . htmlspecialchars($movement['movement_type']) . "</td>
                    <td class='number'>" . number_format($movement['quantity']) . "</td>
                    <td class='number'>$" . number_format($movement['value'], 2) . "</td>
                    <td>" . htmlspecialchars($movement['movement_date']) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                }

                // Current Stock Levels
                if (isset($report_data['current_stock']) && !empty($report_data['current_stock'])) {
                    $html .= "<h3>Current Stock Levels</h3>
            <table>
                <tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Cost Price</th><th>Selling Price</th></tr>";
                    foreach ($report_data['current_stock'] as $stock) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($stock['product_name']) . "</td>
                    <td>" . htmlspecialchars($stock['category']) . "</td>
                    <td class='number'>" . number_format($stock['current_stock']) . "</td>
                    <td class='number'>$" . number_format($stock['cost_price'], 2) . "</td>
                    <td class='number'>$" . number_format($stock['selling_price'], 2) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                }
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data, 'stock_movement', 'movement');
            }
            break;

        case 'expiry_report':
            if (isset($report_data['no_expiry_column'])) {
                $html .= "<div class='no-data'>
            <strong>Notice:</strong> Expiry date tracking is not configured in the system. Please add an 'expiry_date' column to the products table to enable expiry reports.
        </div>";
            } else {
                // Summary
                if (isset($report_data['summary'])) {
                    $summary = $report_data['summary'];
                    $html .= "<div class='summary-box'>
                <h3>Expiry Summary</h3>
                <p><strong>Items Expiring Soon (30 days):</strong> " . $summary['total_expiring'] . "</p>
                <p><strong>Already Expired Items:</strong> " . $summary['total_expired'] . "</p>
                <p><strong>Potential Loss:</strong> $" . number_format($summary['potential_loss'], 2) . "</p>
                <p><strong>Actual Loss:</strong> $" . number_format($summary['actual_loss'], 2) . "</p>
            </div>";
                }

                // Expiring items
                if (isset($report_data['expiring_items']) && !empty($report_data['expiring_items'])) {
                    $html .= "<h3>Items Expiring Within 30 Days</h3>
            <table>
                <tr><th>Product</th><th>Category</th><th>Stock</th><th>Expiry Date</th><th>Days to Expiry</th><th>Potential Loss</th></tr>";
                    foreach ($report_data['expiring_items'] as $item) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . htmlspecialchars($item['category']) . "</td>
                    <td class='number'>" . number_format($item['quantity_per_pack']) . "</td>
                    <td>" . htmlspecialchars($item['exp_date']) . "</td>
                    <td class='number'>" . $item['days_to_expiry'] . "</td>
                    <td class='number'>$" . number_format($item['potential_loss'], 2) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                }

                // Expired items
                if (isset($report_data['expired_items']) && !empty($report_data['expired_items'])) {
                    $html .= "<h3>Already Expired Items</h3>
            <table>
                <tr><th>Product</th><th>Category</th><th>Stock</th><th>Expiry Date</th><th>Days Expired</th><th>Actual Loss</th></tr>";
                    foreach ($report_data['expired_items'] as $item) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . htmlspecialchars($item['category']) . "</td>
                    <td class='number'>" . number_format($item['quantity_per_pack']) . "</td>
                    <td>" . htmlspecialchars($item['exp_date']) . "</td>
                    <td class='number'>" . $item['days_expired'] . "</td>
                    <td class='number'>$" . number_format($item['actual_loss'], 2) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                }

                if (empty($report_data['expiring_items']) && empty($report_data['expired_items'])) {
                    $html .= "<div class='no-data'>
                <strong>Good News:</strong> No expiring or expired items found.
            </div>";
                }
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data, 'expiry_report', 'expiry');
            }
            break;

        case 'all':
            // Executive Summary (existing code remains)
            if (isset($report_data['executive_summary'])) {
                $summary = $report_data['executive_summary'];
                $html .= "<div class='summary-box'>
                            <h2>Executive Summary</h2>
                            <div style='display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;'>
                                <div>
                                    <h4>Inventory Overview</h4>
                                    <p><strong>Total Products:</strong> " . number_format($summary['total_products']) . "</p>
                                    <p><strong>Total Inventory Value:</strong> $" . number_format($summary['total_inventory_value'], 2) . "</p>
                                    <p><strong>Low Stock Alerts:</strong> " . $summary['low_stock_alerts'] . "</p>
                                    <p><strong>Out of Stock Alerts:</strong> " . $summary['out_of_stock_alerts'] . "</p>
                                </div>
                                <div>
                                    <h4>Sales Performance</h4>
                                    <p><strong>Total Sales:</strong> $" . number_format($summary['total_sales'], 2) . "</p>
                                    <p><strong>Total Transactions:</strong> " . number_format($summary['total_transactions']) . "</p>
                                    <p><strong>Expiring Items:</strong> " . $summary['expiring_items'] . "</p>
                                    <p><strong>Expired Items:</strong> " . $summary['expired_items'] . "</p>
                                </div>
                            </div>
                        </div>
                ";
            }

            // 1. SALES SECTION - Include full sales report
            $html .= "<div style='page-break-before: always;'><h2>1. Sales Performance Report</h2>";
            if (isset($report_data['sales'])) {
                if (isset($report_data['sales']['summary'])) {
                    $sales_summary = $report_data['sales']['summary'];
                    $html .= "<div class='summary-box'>
                <h3>Sales Summary</h3>
                <p><strong>Total Transactions:</strong> " . number_format($sales_summary['total_transactions']) . "</p>
                <p><strong>Total Revenue:</strong> $" . number_format($sales_summary['total_revenue'], 2) . "</p>
                <p><strong>Average Transaction:</strong> $" . number_format($sales_summary['average_transaction'], 2) . "</p>
            </div>";
                }

                if (isset($report_data['sales']['no_sales_data'])) {
                    $html .= "<div class='no-data'>
                <strong>Notice:</strong> No sales data found for the selected period.
            </div>";
                } elseif (isset($report_data['sales']['top_products']) && !empty($report_data['sales']['top_products'])) {
                    $html .= "<h3>Top Selling Products</h3>
            <table>
                <tr><th>Product Name</th><th>Category</th><th>Quantity Sold</th><th>Revenue</th></tr>";
                    foreach ($report_data['sales']['top_products'] as $product) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($product['product_name']) . "</td>
                    <td>" . htmlspecialchars($product['category']) . "</td>
                    <td class='number'>" . number_format($product['total_sold']) . "</td>
                    <td class='number'>$" . number_format($product['total_revenue'], 2) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                }
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data['sales'], 'sales_summary', 'comp_sales');
            }
            $html .= "</div>";

            // 2. INVENTORY SECTION - Include full inventory report
            $html .= "<div style='page-break-before: always;'><h2>2. Inventory Valuation Report</h2>";
            if (isset($report_data['inventory'])) {
                if (isset($report_data['inventory']['summary'])) {
                    $inv_summary = $report_data['inventory']['summary'];
                    $html .= "<div class='summary-box'>
                <h3>Inventory Summary</h3>
                <p><strong>Total Products:</strong> " . number_format($inv_summary['total_products']) . "</p>
                <p><strong>Total Units:</strong> " . number_format($inv_summary['total_units']) . "</p>
                <p><strong>Total Inventory Value:</strong> $" . number_format($inv_summary['total_inventory_value'], 2) . "</p>
                <p><strong>Potential Revenue:</strong> $" . number_format($inv_summary['total_potential_revenue'], 2) . "</p>
            </div>";
                }

                if (isset($report_data['inventory']['no_inventory_data'])) {
                    $html .= "<div class='no-data'>
                <strong>Notice:</strong> No inventory data found.
            </div>";
                } elseif (isset($report_data['inventory']['inventory_details']) && !empty($report_data['inventory']['inventory_details'])) {
                    $html .= "<h3>Inventory Details</h3>
            <table>
                <tr><th>Product</th><th>Category</th><th>Stock</th><th>Cost Price</th><th>Selling Price</th><th>Inventory Value</th></tr>";
                    foreach ($report_data['inventory']['inventory_details'] as $item) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . htmlspecialchars($item['category']) . "</td>
                    <td class='number'>" . number_format($item['quantity_per_pack']) . "</td>
                    <td class='number'>$" . number_format($item['cost_price'], 2) . "</td>
                    <td class='number'>$" . number_format($item['selling_price'], 2) . "</td>
                    <td class='number'>$" . number_format($item['inventory_value'], 2) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                }
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data['inventory'], 'inventory_valuation', 'comp_inventory');
            }
            $html .= "</div>";

            // 3. LOW STOCK SECTION - Include full low stock report
            $html .= "<div style='page-break-before: always;'><h2>3. Stock Alert Report</h2>";
            if (isset($report_data['low_stock'])) {
                if (isset($report_data['low_stock']['summary'])) {
                    $stock_summary = $report_data['low_stock']['summary'];
                    $html .= "<div class='summary-box'>
                <h3>Alert Summary</h3>
                <p><strong>Low Stock Items:</strong> " . $stock_summary['low_stock_count'] . "</p>
                <p><strong>Out of Stock Items:</strong> " . $stock_summary['out_of_stock_count'] . "</p>
            </div>";
                }

                if (isset($report_data['low_stock']['low_stock_items']) && !empty($report_data['low_stock']['low_stock_items'])) {
                    $html .= "<h3>Low Stock Items</h3>
            <table>
                <tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Minimum Stock</th><th>Supplier</th></tr>";
                    foreach ($report_data['low_stock']['low_stock_items'] as $item) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . htmlspecialchars($item['category']) . "</td>
                    <td class='number'>" . number_format($item['quantity_per_pack']) . "</td>
                    <td class='number'>" . number_format($item['minimum_stock']) . "</td>
                    <td>" . htmlspecialchars($item['supplier']) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                } else {
                    $html .= "<div class='no-data'>
                <strong>Good News:</strong> No low stock items found.
            </div>";
                }

                if (isset($report_data['low_stock']['out_of_stock']) && !empty($report_data['low_stock']['out_of_stock'])) {
                    $html .= "<h3>Critical: Out of Stock Items</h3>
            <table>
                <tr><th>Product</th><th>Category</th><th>Supplier</th></tr>";
                    foreach ($report_data['low_stock']['out_of_stock'] as $item) {
                        $html .= "<tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . htmlspecialchars($item['category']) . "</td>
                    <td>" . htmlspecialchars($item['supplier']) . "</td>
                </tr>";
                    }
                    $html .= "</table>";
                } else {
                    $html .= "<div class='no-data'>
                <strong>Good News:</strong> No out of stock items found.
            </div>";
                }
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data['low_stock'], 'low_stock', 'comp_lowstock');
            }

            $html .= "</div>";

            // 4. STOCK MOVEMENT SECTION - Include full stock movement report
            $html .= "<div style='page-break-before: always;'><h2>4. Stock Movement Report</h2>";
            if (isset($report_data['stock_movement'])) {
                if (isset($report_data['stock_movement']['no_movement_data'])) {
                    $html .= "<div class='no-data'>
                        <strong>Notice:</strong> No stock movement data found for the selected period.
                        </div>";
                } else {
                    // Movement Summary
                    if (isset($report_data['stock_movement']['movement_summary']) && !empty($report_data['stock_movement']['movement_summary'])) {
                        $html .= "<h3>Stock Movement Summary</h3>
                        <table>
                            <tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Total Sold</th><th>Transactions</th><th>Revenue</th></tr>";
                        foreach ($report_data['stock_movement']['movement_summary'] as $item) {
                            $html .= "<tr>
                                            <td>" . htmlspecialchars($item['product_name']) . "</td>
                                            <td>" . htmlspecialchars($item['category']) . "</td>
                                            <td class='number'>" . number_format($item['current_stock']) . "</td>
                                            <td class='number'>" . number_format($item['total_sold']) . "</td>
                                            <td class='number'>" . number_format($item['transaction_count']) . "</td>
                                            <td class='number'>$" . number_format($item['total_revenue'], 2) . "</td>
                                        </tr>";
                        }
                        $html .= "</table>";
                    }

                    // Current Stock Levels
                    if (isset($report_data['stock_movement']['current_stock']) && !empty($report_data['stock_movement']['current_stock'])) {
                        $html .= "<h3>Current Stock Levels</h3>
                        <table>
                            <tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Cost Price</th><th>Selling Price</th></tr>";
                        foreach ($report_data['stock_movement']['current_stock'] as $stock) {
                            $html .= "<tr>
                                            <td>" . htmlspecialchars($stock['product_name']) . "</td>
                                            <td>" . htmlspecialchars($stock['category']) . "</td>
                                            <td class='number'>" . number_format($stock['current_stock']) . "</td>
                                            <td class='number'>$" . number_format($stock['cost_price'], 2) . "</td>
                                            <td class='number'>$" . number_format($stock['selling_price'], 2) . "</td>
                                        </tr>";
                        }
                        $html .= "</table>";
                    }
                }
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data['stock_movement'], 'stock_movement', 'comp_movement');
            }

            $html .= "</div>";

            // 5. EXPIRY REPORT SECTION - Include full expiry report
            $html .= "<div style='page-break-before: always;'><h2>5. Product Expiry Report</h2>";
            if (isset($report_data['expiry'])) {
                if (isset($report_data['expiry']['no_expiry_column'])) {
                    $html .= "<div class='no-data'>
                        <strong>Notice:</strong> Expiry date tracking is not configured in the system. Please add an 'exp_date' column to the products table to enable expiry reports.
                    </div>";
                } else {
                    // Summary
                    if (isset($report_data['expiry']['summary'])) {
                        $summary = $report_data['expiry']['summary'];
                        $html .= "<div class='summary-box'>
                                    <h3>Expiry Summary</h3>
                                    <p><strong>Items Expiring Soon (30 days):</strong> " . $summary['total_expiring'] . "</p>
                                    <p><strong>Already Expired Items:</strong> " . $summary['total_expired'] . "</p>
                                    <p><strong>Potential Loss:</strong> $" . number_format($summary['potential_loss'], 2) . "</p>
                                    <p><strong>Actual Loss:</strong> $" . number_format($summary['actual_loss'], 2) . "</p>
                                </div>";
                    }

                    // Expiring items
                    if (isset($report_data['expiry']['expiring_items']) && !empty($report_data['expiry']['expiring_items'])) {
                        $html .= "<h3>Items Expiring Within 30 Days</h3>
                        <table>
                            <tr><th>Product</th><th>Category</th><th>Stock</th><th>Expiry Date</th><th>Days to Expiry</th><th>Potential Loss</th></tr>";
                        foreach ($report_data['expiry']['expiring_items'] as $item) {
                            $html .= "<tr>
                                        <td>" . htmlspecialchars($item['product_name']) . "</td>
                                        <td>" . htmlspecialchars($item['category']) . "</td>
                                        <td class='number'>" . number_format($item['quantity_per_pack']) . "</td>
                                        <td>" . htmlspecialchars($item['exp_date']) . "</td>
                                        <td class='number'>" . $item['days_to_expiry'] . "</td>
                                        <td class='number'>$" . number_format($item['potential_loss'], 2) . "</td>
                                    </tr>";
                        }
                        $html .= "</table>";
                    }

                    // Expired items
                    if (isset($report_data['expiry']['expired_items']) && !empty($report_data['expiry']['expired_items'])) {
                        $html .= "<h3>Already Expired Items</h3>
                        <table>
                            <tr><th>Product</th><th>Category</th><th>Stock</th><th>Expiry Date</th><th>Days Expired</th><th>Actual Loss</th></tr>";
                        foreach ($report_data['expiry']['expired_items'] as $item) {
                            $html .= "<tr>
                                        <td>" . htmlspecialchars($item['product_name']) . "</td>
                                        <td>" . htmlspecialchars($item['category']) . "</td>
                                        <td class='number'>" . number_format($item['quantity_per_pack']) . "</td>
                                        <td>" . htmlspecialchars($item['exp_date']) . "</td>
                                        <td class='number'>" . $item['days_expired'] . "</td>
                                        <td class='number'>$" . number_format($item['actual_loss'], 2) . "</td>
                                    </tr>";
                        }
                        $html .= "</table>";
                    }

                    if (empty($report_data['expiry']['expiring_items']) && empty($report_data['expiry']['expired_items'])) {
                        $html .= "<div class='no-data'>
                                    <strong>Good News:</strong> No expiring or expired items found.
                                </div>";
                    }
                }
            }

            if ($include_charts) {
                $html .= generateChartHTML($report_data['expiry'], 'expiry_report', 'comp_expiry');
            }

            $html .= "</div>";
            break;
    }

    $html .= "</body></html>";
    return $html;
}

// Function to convert data to CSV
function generateCSVFromData($report_data, $report_type)
{
    $csv = '';
    $csv .= ucwords(str_replace('_', ' ', $report_type)) . " Report\n";
    $csv .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

    switch ($report_type) {
        case 'sales_summary':
            if (isset($report_data['top_products'])) {
                $csv .= "Top Selling Products\n";
                $csv .= "Product Name,Category,Quantity Sold,Revenue\n";
                foreach ($report_data['top_products'] as $product) {
                    $csv .= '"' . $product['product_name'] . '","' . $product['category'] . '",' .
                        $product['total_sold'] . ',' . $product['total_revenue'] . "\n";
                }
            }
            break;

        case 'inventory_valuation':
            if (isset($report_data['inventory_details'])) {
                $csv .= "Inventory Details\n";
                $csv .= "Product,Category,Stock,Cost Price,Selling Price,Inventory Value\n";
                foreach ($report_data['inventory_details'] as $item) {
                    $csv .= '"' . $item['product_name'] . '","' . $item['category'] . '",' .
                        $item['quantity_per_pack'] . ',' . $item['cost_price'] . ',' .
                        $item['price_per_sachet'] . ',' . $item['inventory_value'] . "\n";
                }
            }
            break;

        case 'low_stock':
            if (isset($report_data['low_stock_items'])) {
                $csv .= "Low Stock Items\n";
                $csv .= "Product,Category,quantity_per_pack Stock,Minimum Stock,Supplier\n";
                foreach ($report_data['low_stock_items'] as $item) {
                    $csv .= '"' . $item['product_name'] . '","' . $item['category'] . '",' .
                        $item['quantity_per_pack'] . ',' . $item['low_stock_alert'] . ',"' .
                        $item['supplier'] . "\"\n";
                }
            }
            break;

        case 'stock_movement':
            if (isset($report_data['movement_summary'])) {
                $csv .= "Stock Movement Summary\n";
                $csv .= "Product,Category,Current Stock,Total Sold,Transactions,Revenue\n";
                foreach ($report_data['movement_summary'] as $item) {
                    $csv .= '"' . $item['product_name'] . '","' . $item['category'] . '",' .
                        $item['current_stock'] . ',' . $item['total_sold'] . ',' .
                        $item['transaction_count'] . ',' . $item['total_revenue'] . "\n";
                }
            }
            break;

        case 'expiry_report':
            if (isset($report_data['expiring_items'])) {
                $csv .= "Items Expiring Within 30 Days\n";
                $csv .= "Product,Category,Stock,Expiry Date,Days to Expiry,Potential Loss\n";
                foreach ($report_data['expiring_items'] as $item) {
                    $csv .= '"' . $item['product_name'] . '","' . $item['category'] . '",' .
                        $item['quantity_per_pack'] . ',' . $item['exp_date'] . ',' .
                        $item['days_to_expiry'] . ',' . $item['potential_loss'] . "\n";
                }
            }
            if (isset($report_data['expired_items'])) {
                $csv .= "\nAlready Expired Items\n";
                $csv .= "Product,Category,Stock,Expiry Date,Days Expired,Actual Loss\n";
                foreach ($report_data['expired_items'] as $item) {
                    $csv .= '"' . $item['product_name'] . '","' . $item['category'] . '",' .
                        $item['quantity_per_pack'] . ',' . $item['exp_date'] . ',' .
                        $item['days_expired'] . ',' . $item['actual_loss'] . "\n";
                }
            }
            break;

        case 'all':
            // Comprehensive report CSV
            $csv .= "COMPREHENSIVE BUSINESS REPORT\n\n";

            // Executive Summary
            if (isset($report_data['executive_summary'])) {
                $csv .= "EXECUTIVE SUMMARY\n";
                $csv .= "Total Products," . $report_data['executive_summary']['total_products'] . "\n";
                $csv .= "Total Inventory Value," . $report_data['executive_summary']['total_inventory_value'] . "\n";
                $csv .= "Total Sales," . $report_data['executive_summary']['total_sales'] . "\n";
                $csv .= "Total Transactions," . $report_data['executive_summary']['total_transactions'] . "\n";
                $csv .= "Low Stock Alerts," . $report_data['executive_summary']['low_stock_alerts'] . "\n";
                $csv .= "Out of Stock Alerts," . $report_data['executive_summary']['out_of_stock_alerts'] . "\n\n";
            }

            // Sales Data
            if (isset($report_data['sales']['top_products']) && !empty($report_data['sales']['top_products'])) {
                $csv .= "TOP SELLING PRODUCTS\n";
                $csv .= "Product Name,Category,Quantity Sold,Revenue\n";
                foreach ($report_data['sales']['top_products'] as $product) {
                    $csv .= '"' . $product['product_name'] . '","' . $product['category'] . '",' .
                        $product['total_sold'] . ',' . $product['total_revenue'] . "\n";
                }
                $csv .= "\n";
            }

            // Inventory Data
            if (isset($report_data['inventory']['inventory_details']) && !empty($report_data['inventory']['inventory_details'])) {
                $csv .= "INVENTORY DETAILS\n";
                $csv .= "Product,Category,Stock,Cost Price,Selling Price,Inventory Value\n";
                foreach ($report_data['inventory']['inventory_details'] as $item) {
                    $csv .= '"' . $item['product_name'] . '","' . $item['category'] . '",' .
                        $item['quantity_per_pack'] . ',' . $item['cost_price'] . ',' .
                        $item['selling_price'] . ',' . $item['inventory_value'] . "\n";
                }
                $csv .= "\n";
            }

            // Low Stock Items
            if (isset($report_data['low_stock']['low_stock_items']) && !empty($report_data['low_stock']['low_stock_items'])) {
                $csv .= "LOW STOCK ITEMS\n";
                $csv .= "Product,Category,Current Stock,Minimum Stock,Supplier\n";
                foreach ($report_data['low_stock']['low_stock_items'] as $item) {
                    $csv .= '"' . $item['product_name'] . '","' . $item['category'] . '",' .
                        $item['quantity_per_pack'] . ',' . $item['minimum_stock'] . ',"' .
                        $item['supplier'] . "\"\n";
                }
                $csv .= "\n";
            }

            // Out of Stock Items
            if (isset($report_data['low_stock']['out_of_stock']) && !empty($report_data['low_stock']['out_of_stock'])) {
                $csv .= "OUT OF STOCK ITEMS\n";
                $csv .= "Product,Category,Supplier\n";
                foreach ($report_data['low_stock']['out_of_stock'] as $item) {
                    $csv .= '"' . $item['product_name'] . '","' . $item['category'] . '","' .
                        $item['supplier'] . "\"\n";
                }
                $csv .= "\n";
            }
            break;
    }

    return $csv;
}

// Main processing logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Debug: Log all POST data
        error_log("Processing report generation...");
        error_log("POST data: " . print_r($_POST, true));

        // Validate required fields
        if (empty($_POST['report_type'])) {
            throw new Exception("Please select a report type.");
        }

        if (empty($_POST['time_period'])) {
            throw new Exception("Please select a time period.");
        }

        if (empty($_POST['export_format'])) {
            throw new Exception("Please select an export format.");
        }

        $report_type = $_POST['report_type'];
        $time_period = $_POST['time_period'];
        $export_format = $_POST['export_format'];
        $include_charts = isset($_POST['include_charts']);
        $include_summary = isset($_POST['include_summary']);

        // Get date range
        if ($time_period == 'custom') {
            if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
                throw new Exception("Please provide start and end dates for custom range.");
            }
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
        } else {
            $dates = getDateRange($time_period);
            $start_date = $dates['start'];
            $end_date = $dates['end'];
        }

        error_log("Report type: $report_type, Period: $time_period, Format: $export_format");
        error_log("Date range: $start_date to $end_date");

        // Generate report data based on type
        $report_data = array();
        switch ($report_type) {
            case 'sales_summary':
                $report_data = generateSalesSummaryReport($connect, $start_date, $end_date);
                break;
            case 'inventory_valuation':
                $report_data = generateInventoryValuationReport($connect);
                break;
            case 'low_stock':
                $report_data = generateLowStockReport($connect);
                break;
            case 'stock_movement':
                $report_data = generateStockMovementReport($connect, $start_date, $end_date);
                break;
            case 'expiry_report':
                $report_data = generateExpiryReport($connect);
                break;
            case 'all':
                // Generate ALL report data, not just summary
                $report_data = generateComprehensiveReport($connect, $start_date, $end_date);
                break;
            default:
                throw new Exception("Invalid report type selected.");
        }

        error_log("Report data generated successfully");

        // Generate report name
        $report_name = ucwords(str_replace('_', ' ', $report_type)) . ' Report - ' . $start_date . ' to ' . $end_date;

        // Create reports directory if it doesn't exist
        $reports_dir = '../reports/generated/';
        if (!file_exists($reports_dir)) {
            if (!mkdir($reports_dir, 0755, true)) {
                throw new Exception("Could not create reports directory.");
            }
        }

        $filename = '';
        $file_path = '';
        $content = '';

        // Generate report based on format
        switch ($export_format) {
            case 'pdf':
            case 'html': // Treating PDF as HTML for now
                $content = generateHTMLReport($report_data, $report_type, $include_charts, $include_summary);
                $filename = $report_name . '.html';
                break;

            case 'csv':
                $content = generateCSVFromData($report_data, $report_type);
                $filename = $report_name . '.csv';
                break;

            case 'excel':
                // For Excel, we'll generate CSV which can be opened in Excel
                $content = generateCSVFromData($report_data, $report_type);
                $filename = $report_name . '.csv';
                break;
        }

        $file_path = $reports_dir . $filename;

        // Write content to file
        if (file_put_contents($file_path, $content) === false) {
            throw new Exception("Could not write report file.");
        }

        error_log("Report file created: $file_path");

        // Save report record to database
        try {
            $parameters = json_encode(array(
                'time_period' => $time_period,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'include_charts' => $include_charts,
                'include_summary' => $include_summary,
                'export_format' => $export_format
            ));

            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

            $sql = "INSERT INTO reports (report_name, report_type, report_period_start, report_period_end, 
                    generated_by, generated_at, file_path, parameters) 
                    VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";

            $stmt = mysqli_prepare($connect, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssiss",
                    $report_name,
                    $report_type,
                    $start_date,
                    $end_date,
                    $user_id,
                    $file_path,
                    $parameters
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } catch (Exception $db_error) {
            error_log("Database save failed: " . $db_error->getMessage());
        }

        // Download of the file
        if (file_exists($file_path)) {
            // Set appropriate content type
            $content_type = 'application/octet-stream';
            if ($export_format == 'csv' || $export_format == 'excel') {
                $content_type = 'text/csv';
            } elseif ($export_format == 'html' || $export_format == 'pdf') {
                $content_type = 'text/html';
            }

            header('Content-Description: File Transfer');
            header('Content-Type: ' . $content_type);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));

            // Clear any previous output
            if (ob_get_level()) {
                ob_end_clean();
            }

            readfile($file_path);

            // Optional: Delete the file after download
            // unlink($file_path);

            exit;
        } else {
            throw new Exception("Report file could not be found after generation.");
        }
    } catch (Exception $e) {
        error_log("Report generation error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: generateReport.php?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Not a POST request, redirect back
    header("Location: generateReport.php");
    exit;
}