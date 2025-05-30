<?php
// This file fetches sale details for the modal view
session_start();
include "../../config/session_check.php";
include "../../config/config.php";

// Check if sale ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sale ID is required'
    ]);
    exit;
}

$saleId = mysqli_real_escape_string($connect, $_GET['id']);

// Query to get sale details
$saleQuery = "SELECT 
                s.id as sale_id,
                s.transaction_number, 
                s.payment_method,
                s.subtotal as amount,
                s.tax_amount,
                s.total_amount,
                s.sale_status as status,
                s.sale_date
              FROM 
                sales s
              WHERE 
                s.id = '$saleId'";

$saleResult = mysqli_query($connect, $saleQuery);

if (!$saleResult || mysqli_num_rows($saleResult) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Sale not found'
    ]);
    exit;
}

$sale = mysqli_fetch_assoc($saleResult);

// Query to get sale items
$itemsQuery = "SELECT 
                sd.quantity,
                p.product_name,
                sd.unit_price,
                p.category
              FROM 
                sale_details sd
              JOIN 
                products p ON sd.product_id = p.id
              WHERE 
                sd.sale_id = '$saleId'";

$itemsResult = mysqli_query($connect, $itemsQuery);
$saleItems = [];

if ($itemsResult && mysqli_num_rows($itemsResult) > 0) {
    while ($item = mysqli_fetch_assoc($itemsResult)) {
        $saleItems[] = $item;
    }
}

// Format date
$sale['formatted_date'] = date('F d, Y h:i A', strtotime($sale['sale_date']));

// Add items to sale data
$sale['items'] = $saleItems;

// Return JSON response
echo json_encode([
    'success' => true,
    'sale' => $sale
]);
?>