<?php
// Absolutely no whitespace or outputs before this
header('Content-Type: application/json');

/* // Basic configuration - minimal version
$connect = mysqli_connect("localhost", "root", "", "sims");

if (!$connect) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
} */

session_start();
include "../config/config.php";
include "../config/session_check.php";

$response = ['success' => false, 'product' => null, 'message' => ''];

if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    
    $query = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($connect, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $response['product'] = mysqli_fetch_assoc($result);
        $response['success'] = true;
    } else {
        $response['message'] = 'Product not found';
    }
} else {
    $response['message'] = 'Product ID is required';
}

echo json_encode($response);
exit;
?>