<?php
// This file generates a downloadable CSV export of sales data
session_start();
include "../config/session_check.php";
include "../config/config.php";

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query
$query = "SELECT 
    s.id,
    s.transaction_number, 
    GROUP_CONCAT(p.product_name SEPARATOR ', ') as items,
    s.subtotal,
    s.tax_amount,
    s.total_amount,
    s.sale_status,
    s.sale_date
FROM 
    sales s
LEFT JOIN 
    sale_details si ON s.id = si.sale_id
LEFT JOIN 
    products p ON si.product_id = p.id";

// Add where conditions based on filters
$whereConditions = [];

if (!empty($search)) {
    $search = mysqli_real_escape_string($connect, $search);
    $whereConditions[] = "(p.product_name LIKE '%$search%' OR s.id LIKE '%$search%' OR c.customer_name LIKE '%$search%')";
}

if (!empty($category)) {
    $category = mysqli_real_escape_string($connect, $category);
    $whereConditions[] = "p.category = '$category'";
}

if (!empty($status)) {
    $status = mysqli_real_escape_string($connect, $status);
    $whereConditions[] = "s.sale_status = '$status'";
}

// Combine where conditions if any
if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

// Group by to ensure proper grouping of sales
$query .= " GROUP BY s.id ORDER BY s.sale_date DESC";

// Execute query
$result = mysqli_query($connect, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connect));
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM to fix Excel display issues with special characters
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Set column headers
fputcsv($output, [
    'Order ID',
    'Customer',
    'Items',
    'Amount ($)',
    'Tax ($)',
    'Total ($)',
    'Status',
    'Date'
]);

// Output each row of the data
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['transaction_number'],
        $row['customer_name'] ?? 'N/A',
        $row['items'],
        number_format($row['subtotal'], 2),
        number_format($row['tax_amount'], 2),
        number_format($row['total_amount'], 2),
        ucfirst($row['sale_status']),
        date('Y-m-d H:i', strtotime($row['sale_date']))
    ]);
}

// Log the export activity
$userId = $_SESSION['user_id']; // Assuming user ID is stored in session
$logQuery = "INSERT INTO activity_logs (user_id, activity_type, details, created_at) 
            VALUES ('$userId', 'export', 'Exported sales report', NOW())";
mysqli_query($connect, $logQuery);

// Close the connection
mysqli_close($connect);
