<?php
// Absolutely no whitespace or outputs before this
header('Content-Type: application/json');

session_start();
include "../../config/config.php";
include "../../config/session_check.php";

$response = ['success' => false, 'message' => ''];

if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    
    $query = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($connect, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $response['product'] = mysqli_fetch_assoc($result);
        
        // Fetch variants for this product
        $variants_query = "SELECT * FROM product_variants WHERE product_id = $product_id";
        $variants_result = mysqli_query($connect, $variants_query);
        
        $variants = [];
        if ($variants_result && mysqli_num_rows($variants_result) > 0) {
            while ($variant = mysqli_fetch_assoc($variants_result)) {
                $variants[] = $variant;
            }
        }
        
        $response['product']['variants'] = $variants;
        $response['success'] = true;
    } else {
        $response['message'] = 'Product not found';
    }
} elseif (isset($_GET['variant_id'])) {
    $variant_id = (int)$_GET['variant_id'];
    error_log("Requested variant_id: " . $variant_id);
    
    $query = "SELECT * FROM `product_variants` WHERE id = $variant_id";
    error_log("SQL Query: " . $query);

    $result = mysqli_query($connect, $query);
    
    if (!$result) {
        error_log("SQL Error: " . mysqli_error($connect));
    }

    if ($result && mysqli_num_rows($result) > 0) {
        $response['variant'] = mysqli_fetch_assoc($result);
        $response['success'] = true;
    } else {
        $response['message'] = 'Variant not found';
        if ($result) {
            error_log("No rows found for variant_id: " . $variant_id);
        }
    }
} else {
    $response['message'] = 'Either product_id or variant_id is required';
}

echo json_encode($response);
exit;
?>