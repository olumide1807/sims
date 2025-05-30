<?php
session_start();
include "../../config/config.php";
include "../../config/session_check.php";

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which action we're performing
    $isVariantUpdate = isset($_POST['variant_id']) && !empty($_POST['variant_id']);
    $isVariantAdd = isset($_POST['action']) && $_POST['action'] === 'add_variant';
    $isDeleteItem = isset($_POST['action']) && $_POST['action'] === 'delete_item';

    if ($isVariantAdd) {
        // ADD NEW VARIANT
        $required_fields = ['product_id', 'variant_name', 'qty_packet', 'qty_sachet', 'price_per_packet', 'price_per_sachet'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            $response['message'] = 'Missing required fields for adding variant: ' . implode(', ', $missing_fields);
            echo json_encode($response);
            exit;
        }

        // Sanitize inputs
        $product_id = (int)$_POST['product_id'];
        $variant_name = mysqli_real_escape_string($connect, trim($_POST['variant_name']));
        $qty_packet = (int)$_POST['qty_packet'];
        $qty_sachet = (int)$_POST['qty_sachet'];
        $price_per_packet = (float)$_POST['price_per_packet'];
        $price_per_sachet = (float)$_POST['price_per_sachet'];

        // Check if variant name already exists for this product
        $check_sql = "SELECT COUNT(*) as count FROM product_variants 
                     WHERE product_id = $product_id AND variant_name = '$variant_name'";
        $check_result = mysqli_query($connect, $check_sql);
        $check_row = mysqli_fetch_assoc($check_result);

        if ($check_row['count'] > 0) {
            $response['message'] = 'A variant with this name already exists for this product';
            echo json_encode($response);
            exit;
        }

        // Insert the new variant into the database
        $insert_sql = "INSERT INTO product_variants 
                      (product_id, variant_name, qty_packet, qty_sachet, price_per_packet, price_per_sachet, created_at) 
                      VALUES 
                      ($product_id, '$variant_name', $qty_packet, $qty_sachet, $price_per_packet, $price_per_sachet, NOW())";

        if (mysqli_query($connect, $insert_sql)) {
            $variant_id = mysqli_insert_id($connect);
            $response['success'] = true;
            $response['message'] = 'Variant added successfully';
            $response['variant_id'] = $variant_id;
        } else {
            $response['message'] = 'Error adding variant: ' . mysqli_error($connect);
        }
    } elseif ($isVariantUpdate) {
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
    } elseif ($isDeleteItem) {
        $item_type = mysqli_real_escape_string($connect, $_POST['item_type']);
        $item_id = mysqli_real_escape_string($connect, $_POST['item_id']);

        if ($item_type == 'product') {
            // Delete product and all its variants (due to foreign key constraints, delete variants first)
            mysqli_query($connect, "DELETE FROM product_variants WHERE product_id = $item_id");
            $delete_query = "DELETE FROM products WHERE id = $item_id";
        } elseif ($item_type == 'variant') {
            $delete_query = "DELETE FROM product_variants WHERE id = $item_id";
        } else {
            $response['message'] = 'Invalid item type';
        }

        if (mysqli_query($connect, $delete_query)) {
            $response['success'] = true;
            $response['message'] = ucfirst($item_type) . ' deleted successfully';
        } else {
            $response['message'] = 'Error deleting ' . $item_type . ': ' . mysqli_error($connect);
        }
    } else {
        // PRODUCT UPDATE
        // Only require basic fields
        $required_fields = ['product_id', 'product_name', 'category', 'low_stock_alert'];
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
        $brand = isset($_POST['brand']) ? mysqli_real_escape_string($connect, trim($_POST['brand'])) : '';
        $batch_number = isset($_POST['batch_number']) ? mysqli_real_escape_string($connect, trim($_POST['batch_number'])) : '';
        $low_stock_alert = (int)$_POST['low_stock_alert'];

        // Check if this product has variants
        $variant_check_sql = "SELECT COUNT(*) as variant_count FROM product_variants WHERE product_id = $product_id";
        $variant_result = mysqli_query($connect, $variant_check_sql);
        $variant_data = mysqli_fetch_assoc($variant_result);
        $has_variants = $variant_data['variant_count'] > 0;

        // Create SQL statement based on whether the product has variants
        if ($has_variants) {
            // For products with variants, only update basic information
            $update_sql = "UPDATE products SET 
                        product_name = '$product_name',
                        category = '$category',
                        brand = '$brand',
                        batch_number = '$batch_number',
                        low_stock_alert = $low_stock_alert,
                        created_at = NOW()
                      WHERE id = $product_id";
        } else {
            // For products without variants, update all fields including stock and prices
            $quantity_packet = (int)$_POST['quantity_packet'];
            $price_per_sachet = (float)$_POST['price_per_sachet'];
            $price_per_packet = (float)$_POST['price_per_packet'];

            $update_sql = "UPDATE products SET 
                        product_name = '$product_name',
                        category = '$category',
                        brand = '$brand',
                        batch_number = '$batch_number',
                        quantity_packet = $quantity_packet,
                        price_per_sachet = $price_per_sachet,
                        price_per_packet = $price_per_packet,
                        low_stock_alert = $low_stock_alert,
                        created_at = NOW()
                      WHERE id = $product_id";
        }

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
