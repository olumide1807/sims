<?php
session_start();
include "../config/config.php";
include "../config/session_check.php";

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine if we're updating a product or a variant
    $isVariantUpdate = isset($_POST['variant_id']) && !empty($_POST['variant_id']);
    
    if ($isVariantUpdate) {
        // VARIANT UPDATE
        $required_fields = ['variant_id', 'product_id', 'variant_name', 'qty_packet', 'qty_sachet', 'price_per_packet', 'price_per_sachet'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            $response['message'] = 'Missing required fields for variant update: ' . implode(', ', $missing_fields);
            echo json_encode($response);
            exit;
        }
        
        // Sanitize inputs
        $variant_id = (int)$_POST['variant_id'];
        $product_id = (int)$_POST['product_id'];
        $variant_name = mysqli_real_escape_string($connect, trim($_POST['variant_name']));
        $qty_packet = (int)$_POST['qty_packet'];
        $qty_sachet = (int)$_POST['qty_sachet'];
        $price_per_packet = (float)$_POST['price_per_packet'];
        $price_per_sachet = (float)$_POST['price_per_sachet'];
        
        // Update the variant in the database
        $update_sql = "UPDATE product_variants SET 
                        variant_name = '$variant_name',
                        qty_packet = $qty_packet,
                        qty_sachet = $qty_sachet,
                        price_per_packet = $price_per_packet,
                        price_per_sachet = $price_per_sachet,
                        created_at = NOW()
                      WHERE id = $variant_id AND product_id = $product_id";
        
        if (mysqli_query($connect, $update_sql)) {
            $response['success'] = true;
            $response['message'] = 'Variant updated successfully';
        } else {
            $response['message'] = 'Error updating variant: ' . mysqli_error($connect);
        }
    } else {
        // PRODUCT UPDATE
        $required_fields = ['product_id', 'product_name', 'category', 'quantity_packet', 'price_per_sachet', 'price_per_packet', 'low_stock_alert'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            $response['message'] = 'Missing required fields for product update: ' . implode(', ', $missing_fields);
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
                        created_at = NOW()
                      WHERE id = $product_id";
        
        if (mysqli_query($connect, $update_sql)) {
            $response['success'] = true;
            $response['message'] = 'Product updated successfully';
        } else {
            $response['message'] = 'Error updating product: ' . mysqli_error($connect);
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
exit;
?>