<?php
/**
 * Notification Functions for SIMS
 * Save this file as: config/notification_functions.php
 */

/**
 * Get user's notification settings
 * @param int $user_id
 * @param mysqli $connect
 * @return array
 */
function getUserNotificationSettings($user_id, $connect) {
    $settings_query = "SELECT * FROM notification_settings WHERE user_id = ?";
    $settings_stmt = mysqli_prepare($connect, $settings_query);
    mysqli_stmt_bind_param($settings_stmt, "i", $user_id);
    mysqli_stmt_execute($settings_stmt);
    $settings_result = mysqli_stmt_get_result($settings_stmt);
    $current_settings = mysqli_fetch_assoc($settings_result);

    // Default settings if none exist
    if (!$current_settings) {
        $current_settings = [
            'low_stock_alerts' => 1,
            'expiry_notifications' => 1,
            'low_stock_threshold' => 10,
            'expiry_threshold_days' => 30,
            'notification_methods' => 'email,dashboard'
        ];
    }

    return $current_settings;
}

/**
 * Get notification statistics
 * @param array $settings User notification settings
 * @param mysqli $connect
 * @return array
 */
function getNotificationStats($settings, $connect) {
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM products WHERE quantity_per_pack <= ?) as low_stock_count,
        (SELECT COUNT(*) FROM products WHERE exp_date <= DATE_ADD(NOW(), INTERVAL ? DAY) AND exp_date > NOW()) as expiring_soon_count,
        (SELECT COUNT(*) FROM products WHERE exp_date <= NOW()) as expired_count";
    
    $stats_stmt = mysqli_prepare($connect, $stats_query);
    mysqli_stmt_bind_param($stats_stmt, "ii", $settings['low_stock_threshold'], $settings['expiry_threshold_days']);
    mysqli_stmt_execute($stats_stmt);
    $stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stats_stmt));
    
    return $stats;
}

/**
 * Get all notifications for a user
 * @param int $user_id
 * @param mysqli $connect
 * @param int $limit Optional limit for number of notifications
 * @return array
 */
function getUserNotifications($user_id, $connect, $limit = 10) {
    $settings = getUserNotificationSettings($user_id, $connect);
    $notifications = [];

    // Get low stock notifications
    if ($settings['low_stock_alerts']) {
        $low_stock_query = "SELECT product_name, quantity_per_pack, 'low_stock' as type, 
                           created_at, id as product_id
                           FROM products 
                           WHERE quantity_per_pack <= ? 
                           ORDER BY quantity_per_pack ASC";
        
        $low_stock_stmt = mysqli_prepare($connect, $low_stock_query);
        mysqli_stmt_bind_param($low_stock_stmt, "i", $settings['low_stock_threshold']);
        mysqli_stmt_execute($low_stock_stmt);
        $low_stock_result = mysqli_stmt_get_result($low_stock_stmt);
        
        while ($row = mysqli_fetch_assoc($low_stock_result)) {
            $notifications[] = [
                'id' => 'low_stock_' . $row['product_id'],
                'type' => 'low_stock',
                'priority' => 1,
                'title' => 'Low Stock Alert',
                'message' => $row['product_name'] . ' has only ' . $row['quantity_per_pack'] . ' units left',
                'icon' => 'fas fa-box text-warning',
                'time' => 'Now',
                'timestamp' => time(),
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'details' => [
                    'current_stock' => $row['quantity_per_pack'],
                    'threshold' => $settings['low_stock_threshold']
                ]
            ];
        }
    }

    // Get expiry notifications
    if ($settings['expiry_notifications']) {
        $expiry_query = "SELECT product_name, exp_date, 
                        DATEDIFF(exp_date, NOW()) as days_to_expiry,
                        id as product_id,
                        CASE 
                            WHEN exp_date <= NOW() THEN 'expired'
                            ELSE 'expiring_soon'
                        END as expiry_status
                        FROM products 
                        WHERE exp_date <= DATE_ADD(NOW(), INTERVAL ? DAY) 
                        ORDER BY exp_date ASC";
        
        $expiry_stmt = mysqli_prepare($connect, $expiry_query);
        mysqli_stmt_bind_param($expiry_stmt, "i", $settings['expiry_threshold_days']);
        mysqli_stmt_execute($expiry_stmt);
        $expiry_result = mysqli_stmt_get_result($expiry_stmt);
        
        while ($row = mysqli_fetch_assoc($expiry_result)) {
            if ($row['expiry_status'] === 'expired') {
                $notifications[] = [
                    'id' => 'expired_' . $row['product_id'],
                    'type' => 'expired',
                    'priority' => 3,
                    'title' => 'Product Expired',
                    'message' => $row['product_name'] . ' expired on ' . date('M d, Y', strtotime($row['exp_date'])),
                    'icon' => 'fas fa-exclamation-triangle text-danger',
                    'time' => abs($row['days_to_expiry']) . ' days ago',
                    'timestamp' => strtotime($row['exp_date']),
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'details' => [
                        'exp_date' => $row['exp_date'],
                        'days_expired' => abs($row['days_to_expiry'])
                    ]
                ];
            } else {
                $notifications[] = [
                    'id' => 'expiring_' . $row['product_id'],
                    'type' => 'expiring_soon',
                    'priority' => 2,
                    'title' => 'Expiring Soon',
                    'message' => $row['product_name'] . ' expires in ' . $row['days_to_expiry'] . ' days',
                    'icon' => 'fas fa-clock text-warning',
                    'time' => $row['days_to_expiry'] . ' days',
                    'timestamp' => strtotime($row['exp_date']),
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'details' => [
                        'exp_date' => $row['exp_date'],
                        'days_to_expiry' => $row['days_to_expiry']
                    ]
                ];
            }
        }
    }

    // Sort notifications by priority (expired first, then expiring soon, then low stock)
    usort($notifications, function($a, $b) {
        if ($a['priority'] === $b['priority']) {
            return $b['timestamp'] - $a['timestamp']; // Sort by time if same priority
        }
        return $b['priority'] - $a['priority'];
    });

    // Apply limit if specified
    if ($limit > 0) {
        $notifications = array_slice($notifications, 0, $limit);
    }

    return $notifications;
}

/**
 * Get notification count for a user
 * @param int $user_id
 * @param mysqli $connect
 * @return int
 */
function getNotificationCount($user_id, $connect) {
    $notifications = getUserNotifications($user_id, $connect, 0); // Get all notifications
    return count($notifications);
}

/**
 * Format notification count display (show "10+" when count > 10)
 * @param int $count
 * @return string
 */
function formatNotificationCount($count) {
    return $count > 10 ? '10+' : (string)$count;
}

/**
 * Generate notification dropdown HTML
 * @param int $user_id
 * @param mysqli $connect
 * @param int $display_limit Number of notifications to display in dropdown
 * @return string
 */
function generateNotificationDropdown($user_id, $connect, $display_limit = 8) {
    $all_notifications = getUserNotifications($user_id, $connect, 0); // Get all notifications
    $total_count = count($all_notifications);
    $display_notifications = array_slice($all_notifications, 0, $display_limit);
    $remaining_notifications = array_slice($all_notifications, $display_limit);
    
    ob_start();
    ?>
    <div class="notification-dropdown">
        <div class="notification-badge" onclick="toggleNotificationDropdown()" style="cursor: pointer;">
            <i class="fas fa-bell text-muted"></i>
            <?php if ($total_count > 0): ?>
                <span class="badge rounded-pill bg-danger"><?php echo formatNotificationCount($total_count); ?></span>
            <?php endif; ?>
        </div>
        
        <!-- Notification Dropdown -->
        <div class="notification-dropdown-menu" id="notificationDropdown">
            <div class="notification-header">
                <h6 class="mb-0">Notifications</h6>
                <?php if ($total_count > 0): ?>
                    <span class="status-badge bg-primary"><?php echo formatNotificationCount($total_count); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="notification-list" id="notificationList">
                <?php if (empty($all_notifications)): ?>
                    <div class="notification-item text-center py-3">
                        <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                        <p class="mb-0 text-muted">No notifications</p>
                        <small class="text-muted">All systems are running smoothly</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($display_notifications as $notification): ?>
                        <div class="notification-item" data-notification-id="<?php echo $notification['id']; ?>">
                            <div class="notification-icon">
                                <i class="<?php echo $notification['icon']; ?>"></i>
                            </div>
                            <div class="notification-content">
                                <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                <p class="mb-1 small"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <small class="text-muted"><?php echo htmlspecialchars($notification['time']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Hidden remaining notifications -->
                    <div id="remainingNotifications" style="display: none;">
                        <?php foreach ($remaining_notifications as $notification): ?>
                            <div class="notification-item" data-notification-id="<?php echo $notification['id']; ?>">
                                <div class="notification-icon">
                                    <i class="<?php echo $notification['icon']; ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                    <p class="mb-1 small"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <small class="text-muted"><?php echo htmlspecialchars($notification['time']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($total_count > $display_limit): ?>
                        <div class="notification-footer">
                            <a href="javascript:void(0)" class="text-decoration-none" id="viewAllNotifications" onclick="toggleAllNotifications()">
                                View all <?php echo $total_count; ?> notifications
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Store data for JavaScript -->
    <script>
        window.notificationData = {
            totalCount: <?php echo $total_count; ?>,
            displayLimit: <?php echo $display_limit; ?>,
            remainingCount: <?php echo count($remaining_notifications); ?>
        };
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Generate notification dropdown CSS
 * @return string
 */
function getNotificationDropdownCSS() {
    return '
    <style>
    .notification-dropdown {
        position: relative;
        display: inline-block;
    }

    .notification-dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        width: 350px;
        max-height: 400px;
        overflow: hidden;
        z-index: 1000;
        display: none;
        animation: slideDown 0.2s ease;
    }

    .notification-dropdown-menu.show {
        display: block;
    }

    .notification-header {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
    }

    .notification-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .notification-list.expanded {
        max-height: 500px;
    }

    .notification-item {
        padding: 1rem;
        border-bottom: 1px solid #f1f3f4;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        transition: background-color 0.2s;
        cursor: pointer;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-icon {
        flex-shrink: 0;
        width: 24px;
        text-align: center;
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-content h6 {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        color: #495057;
    }

    .notification-content p {
        font-size: 0.8125rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }

    .notification-footer {
        padding: 0.75rem;
        text-align: center;
        border-top: 1px solid #e9ecef;
        background: #f8f9fa;
    }

    .notification-footer a {
        color: #007bff;
        font-size: 0.875rem;
        font-weight: 500;
        transition: color 0.2s;
    }

    .notification-footer a:hover {
        color: #0056b3;
    }

    /* Animation for expanding notifications */
    #remainingNotifications {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .notification-dropdown-menu {
            width: 300px;
            right: -50px;
        }
    }
    </style>';
}

/**
 * Generate notification dropdown JavaScript
 * @return string
 */
function getNotificationDropdownJS() {
    return '
    <script>
    let allNotificationsVisible = false;

    function toggleNotificationDropdown() {
        const dropdown = document.getElementById("notificationDropdown");
        dropdown.classList.toggle("show");
    }

    function toggleAllNotifications() {
        const remainingNotifications = document.getElementById("remainingNotifications");
        const viewAllLink = document.getElementById("viewAllNotifications");
        const notificationList = document.getElementById("notificationList");
        
        if (!allNotificationsVisible) {
            // Show all notifications
            remainingNotifications.style.display = "block";
            notificationList.classList.add("expanded");
            viewAllLink.textContent = "Show less";
            allNotificationsVisible = true;
        } else {
            // Hide remaining notifications
            remainingNotifications.style.display = "none";
            notificationList.classList.remove("expanded");
            viewAllLink.textContent = "View all " + window.notificationData.totalCount + " notifications";
            allNotificationsVisible = false;
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("notificationDropdown");
        const notificationBadge = document.querySelector(".notification-badge");
        
        if (dropdown && notificationBadge && 
            !dropdown.contains(event.target) && 
            !notificationBadge.contains(event.target)) {
            dropdown.classList.remove("show");
            
            // Reset to collapsed state when closing
            if (allNotificationsVisible) {
                toggleAllNotifications();
            }
        }
    });

    // Close dropdown when pressing escape key
    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            const dropdown = document.getElementById("notificationDropdown");
            if (dropdown) {
                dropdown.classList.remove("show");
                
                // Reset to collapsed state when closing
                if (allNotificationsVisible) {
                    toggleAllNotifications();
                }
            }
        }
    });

    // Handle notification item clicks
    document.addEventListener("click", function(event) {
        const notificationItem = event.target.closest(".notification-item");
        if (notificationItem && notificationItem.dataset.notificationId) {
            // You can add custom logic here to handle notification clicks
            console.log("Clicked notification:", notificationItem.dataset.notificationId);
            
            // Example: Mark notification as read or navigate to related page
            // markNotificationAsRead(notificationItem.dataset.notificationId);
        }
    });

    // Optional: Auto-refresh notifications every 30 seconds
    function refreshNotifications() {
        // You can implement AJAX call here to refresh notifications
        console.log("Refreshing notifications...");
    }

    // Uncomment to enable auto-refresh
    // setInterval(refreshNotifications, 30000);
    </script>';
}

/**
 * Check if notifications should be sent via specific method
 * @param int $user_id
 * @param string $method (email, sms, dashboard)
 * @param mysqli $connect
 * @return bool
 */
function shouldSendNotification($user_id, $method, $connect) {
    $settings = getUserNotificationSettings($user_id, $connect);
    $methods = explode(',', $settings['notification_methods']);
    return in_array($method, $methods);
}
?>