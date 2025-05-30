<?php

function getDashboardStats($connect) {
    $stats = [];
    
    try {
        // Total Products
        $query = "SELECT COUNT(*) as total_products FROM products";
        $result = mysqli_query($connect, $query);
        $stats['total_products'] = mysqli_fetch_assoc($result)['total_products'] ?? 0;
        
        // Monthly Revenue (current month)
        $current_month = date('Y-m');
        $query = "SELECT SUM(total_amount) as monthly_revenue 
                  FROM sales 
                  WHERE DATE_FORMAT(sale_date, '%Y-%m') = '$current_month'";
        $result = mysqli_query($connect, $query);
        $stats['monthly_revenue'] = mysqli_fetch_assoc($result)['monthly_revenue'] ?? 0;
        
        // Low Stock Items (assuming you have a minimum_stock column)
        $query = "SELECT COUNT(*) as low_stock_count 
                  FROM products 
                  WHERE quantity_per_pack <= low_stock_alert";
        $result = mysqli_query($connect, $query);
        $stats['low_stock_items'] = mysqli_fetch_assoc($result)['low_stock_count'] ?? 0;
        
        // Pending Orders (if you have an orders table)
        $query = "SELECT COUNT(*) as pending_orders 
                  FROM sales 
                  WHERE sale_status = 'pending'";
        $result = mysqli_query($connect, $query);
        $stats['pending_orders'] = mysqli_fetch_assoc($result)['pending_orders'] ?? 0;
        
    } catch (Exception $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        // Return default values on error
        $stats = [
            'total_products' => 0,
            'monthly_revenue' => 0,
            'low_stock_items' => 0,
            'pending_orders' => 0
        ];
    }
    
    return $stats;
}

function getRecentActivities($connect, $limit = 5) {
    $activities = [];
    
    try {
        // Get recent activities from different sources
        $query = "
            (SELECT 
                'New Product Added' as activity_type,
                product_name as description,
                created_at as activity_date,
                'primary' as badge_type
            FROM products 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY created_at DESC
            LIMIT 3)
            
            UNION ALL
            
            (SELECT 
                'Sale Completed' as activity_type,
                CONCAT('Sale #', sale_id, ' - $', FORMAT(total_amount, 2)) as description,
                sale_date as activity_date,
                'success' as badge_type
            FROM sales 
            WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY sale_date DESC
            LIMIT 3)
            
            UNION ALL
            
            (SELECT 
                'Low Stock Alert' as activity_type,
                CONCAT(product_name, ' - ', quantity, ' units left') as description,
                updated_at as activity_date,
                'warning' as badge_type
            FROM products 
            WHERE quantity <= minimum_stock 
            AND status = 'active'
            ORDER BY updated_at DESC
            LIMIT 2)
            
            ORDER BY activity_date DESC
            LIMIT $limit
        ";
        
        $result = mysqli_query($connect, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $activities[] = $row;
        }
        
    } catch (Exception $e) {
        error_log("Error fetching recent activities: " . $e->getMessage());
    }
    
    return $activities;
}

function getActiveAlerts($connect) {
    $alerts = [];
    
    try {
        // Low Stock Alert
        $query = "SELECT COUNT(*) as count FROM products WHERE quantity_per_pack <= low_stock_alert";
        $result = mysqli_query($connect, $query);
        $low_stock_count = mysqli_fetch_assoc($result)['count'] ?? 0;
        
        if ($low_stock_count > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-exclamation-circle',
                'title' => 'Low Stock Alert',
                'message' => "$low_stock_count products need reordering"
            ];
        }
        
        // Expiring Products (if you have expiry_date column)
        $query = "SELECT COUNT(*) as count 
                  FROM products 
                  WHERE exp_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)";
        $result = mysqli_query($connect, $query);
        $expiring_count = mysqli_fetch_assoc($result)['count'] ?? 0;
        
        if ($expiring_count > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-exclamation-triangle',
                'title' => 'Expiring Soon',
                'message' => "$expiring_count products expiring in 7 days"
            ];
        }
        
        // Out of Stock Alert
        $query = "SELECT COUNT(*) as count FROM products WHERE quantity_per_pack = 0";
        $result = mysqli_query($connect, $query);
        $out_of_stock_count = mysqli_fetch_assoc($result)['count'] ?? 0;
        
        if ($out_of_stock_count > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-times-circle',
                'title' => 'Out of Stock',
                'message' => "$out_of_stock_count products are out of stock"
            ];
        }
        
        // Success message if no critical alerts
        if (empty($alerts)) {
            $alerts[] = [
                'type' => 'success',
                'icon' => 'fas fa-check-circle',
                'title' => 'All Good',
                'message' => 'No critical alerts at this time'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error fetching alerts: " . $e->getMessage());
        $alerts[] = [
            'type' => 'info',
            'icon' => 'fas fa-info-circle',
            'title' => 'System Status',
            'message' => 'Unable to fetch alerts at this time'
        ];
    }
    
    return $alerts;
}

function getInventoryOverviewData($connect, $period = 'weekly') {
    $data = [];
    
    try {
        switch ($period) {
            case 'weekly':
                // Get data for the last 7 days
                $query = "
                    SELECT 
                        DATE(sale_date) as date,
                        SUM(total_amount) as revenue,
                        COUNT(*) as sales_count
                    FROM sales 
                    WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(sale_date)
                    ORDER BY date ASC
                ";
                break;
                
            case 'monthly':
                // Get data for the last 30 days
                $query = "
                    SELECT 
                        DATE(sale_date) as date,
                        SUM(total_amount) as revenue,
                        COUNT(*) as sales_count
                    FROM sales 
                    WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(sale_date)
                    ORDER BY date ASC
                ";
                break;
                
            case 'yearly':
                // Get data for the last 12 months
                $query = "
                    SELECT 
                        DATE_FORMAT(sale_date, '%Y-%m') as date,
                        SUM(total_amount) as revenue,
                        COUNT(*) as sales_count
                    FROM sales 
                    WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
                    ORDER BY date ASC
                ";
                break;
                
            default:
                $query = "
                    SELECT 
                        DATE(sale_date) as date,
                        SUM(total_amount) as revenue,
                        COUNT(*) as sales_count
                    FROM sales 
                    WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(sale_date)
                    ORDER BY date ASC
                ";
        }
        
        $result = mysqli_query($connect, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'date' => $row['date'],
                'revenue' => floatval($row['revenue']),
                'sales_count' => intval($row['sales_count'])
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error fetching inventory overview data: " . $e->getMessage());
    }
    
    return $data;
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function formatNumber($number) {
    if ($number >= 1000000) {
        return number_format($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return number_format($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j', strtotime($datetime));
}
?>