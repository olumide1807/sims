<?php
session_start();
include "../config/config.php";
include "../config/session_check.php";


// At the top of update_product.php
error_log('POST data: ' . print_r($_POST, true));

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all required fields are present
    $required_fields = ['product_id', 'product_name', 'category', 'quantity_packet', 'price_per_sachet', 'price_per_packet', 'low_stock_alert'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $response['message'] = 'Missing required fields: ' . implode(', ', $missing_fields);
        echo json_encode($response);
        exit;
    }
    
    // Sanitize inputs
    $product_id = (int)$_POST['product_id'];
    $product_name = mysqli_real_escape_string($connect, trim($_POST['product_name']));
    $category = mysqli_real_escape_string($connect, trim($_POST['category']));
    $quantity_packet = (int)$_POST['quantity_packet'];
    $price_per_sachet = (float)$_POST['price_per_sachet'];
    $price_per_packet = (float)$_POST['price_per_packet'];
    $low_stock_alert = (int)$_POST['low_stock_alert'];
    
    // Update the product in the database
    $update_sql = "UPDATE products SET 
                    product_name = '$product_name',
                    category = '$category',
                    quantity_packet = $quantity_packet,
                    price_per_sachet = $price_per_sachet,
                    price_per_packet = $price_per_packet,
                    low_stock_alert = $low_stock_alert,
                    updated_at = NOW()
                  WHERE id = $product_id";
    
    if (mysqli_query($connect, $update_sql)) {
        $response['success'] = true;
        $response['message'] = 'Product updated successfully';
    } else {
        $response['message'] = 'Error updating product: ' . mysqli_error($connect);
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
exit;
?>