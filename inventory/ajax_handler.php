<?php
session_start();
include "../config/config.php";
include "../config/session_check.php";

// Default response structure
$response = [
    'success' => false,
    'items' => [],
    'pagination' => [
        'current_page' => 1,
        'total_pages' => 1,
        'total_items' => 0
    ],
    'product' => null,
    'message' => ''
];

// Check if it's an AJAX request for inventory filtering
if (isset($_POST['action']) && $_POST['action'] == 'filter_inventory') {
    // Get filter parameters
    $search = isset($_POST['search']) ? mysqli_real_escape_string($connect, $_POST['search']) : '';
    $category = isset($_POST['category']) ? mysqli_real_escape_string($connect, $_POST['category']) : '';
    $status = isset($_POST['status']) ? mysqli_real_escape_string($connect, $_POST['status']) : '';
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

    // Pagination settings
    $limit = 10; // Items per page
    $offset = ($page - 1) * $limit;

    // Build WHERE clause for filtering
    $where_clauses = [];
    if (!empty($search)) {
        $where_clauses[] = "p.product_name LIKE '%$search%'";
    }
    if (!empty($category)) {
        $where_clauses[] = "p.category = '$category'";
    }
    if (!empty($status)) {
        switch ($status) {
            case 'low-stock':
                $where_clauses[] = "p.quantity_packet <= p.low_stock_alert AND p.quantity_packet > 0";
                break;
            case 'out-of-stock':
                $where_clauses[] = "p.quantity_packet = 0";
                break;
            case 'in-stock':
                $where_clauses[] = "p.quantity_packet > p.low_stock_alert";
                break;
        }
    }

    // Combine WHERE clauses
    $where_condition = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

    // Count total filtered items
    $total_qry = "SELECT COUNT(*) as total FROM products p $where_condition";
    $total_res = mysqli_query($connect, $total_qry);

    if ($total_res) {
        $total_row = mysqli_fetch_assoc($total_res);
        $total_products = $total_row['total'];
        $total_pages = ceil($total_products / $limit);

        // Update pagination info in response
        $response['pagination'] = [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_products
        ];

        // Get filtered products
        $select_qry = "SELECT 
                p.id AS product_id, 
                p.product_name, 
                p.category, 
                p.quantity_packet AS product_stock,
                p.price_per_sachet,
                p.price_per_packet,
                p.low_stock_alert,
                p.created_at, 
                v.id AS variant_id, 
                v.variant_name, 
                v.qty_packet,
                v.qty_sachet,
                v.price_per_packet AS packPrice,
                v.price_per_sachet AS unitPrice
            FROM products p
            LEFT JOIN product_variants v ON p.id = v.product_id
            $where_condition
            ORDER BY p.id, v.id
            LIMIT $offset, $limit";

        $result = mysqli_query($connect, $select_qry);

        if ($result) {
            $items = [];

            // Group products with their variants
            $products = [];
            $current_product_id = null;
            $current_product = null;

            while ($row = mysqli_fetch_assoc($result)) {
                // If this is a new product or the first row
                if ($current_product_id !== $row['product_id']) {
                    // Save the previous product if it exists
                    if ($current_product !== null) {
                        $items[] = $current_product;
                    }

                    // Start a new product
                    $current_product_id = $row['product_id'];
                    $current_product = [
                        'product_id' => $row['product_id'],
                        'product_name' => $row['product_name'],
                        'category' => $row['category'],
                        'product_stock' => $row['product_stock'],
                        'price_per_sachet' => $row['price_per_sachet'],
                        'price_per_packet' => $row['price_per_packet'],
                        'low_stock_alert' => $row['low_stock_alert'],
                        'created_at' => $row['created_at'],
                        'variants' => []
                    ];
                }

                // Add variant if it exists
                if (!empty($row['variant_id'])) {
                    $current_product['variants'][] = [
                        'variant_id' => $row['variant_id'],
                        'variant_name' => $row['variant_name'],
                        'qty_packet' => $row['qty_packet'],
                        'qty_sachet' => $row['qty_sachet'],
                        'packPrice' => $row['packPrice'],
                        'unitPrice' => $row['unitPrice']
                    ];
                }
            }

            // Add the last product if it exists
            if ($current_product !== null) {
                $items[] = $current_product;
            }

            $response['items'] = $items;
            $response['success'] = true;
        } else {
            $response['message'] = 'Error executing query: ' . mysqli_error($connect);
        }
    } else {
        $response['message'] = 'Error counting total items: ' . mysqli_error($connect);
    }
} else {
    $response['message'] = 'Invalid request';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
