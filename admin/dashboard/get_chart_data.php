<?php
// get_chart_data.php - Create this file in the same directory as your index.php

session_start();
include "../../config/session_check.php";
include "../../config/config.php";
include "dashboard_functions.php";

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get the period parameter
$period = $_GET['period'] ?? 'weekly';

// Validate period
$allowed_periods = ['weekly', 'monthly', 'yearly'];
if (!in_array($period, $allowed_periods)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid period']);
    exit;
}

try {
    // Get the chart data
    $data = getInventoryOverviewData($connect, $period);
    
    // Return the data as JSON
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch chart data']);
    error_log("Chart data error: " . $e->getMessage());
}
?>