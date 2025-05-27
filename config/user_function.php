<?php
// User Management Helper Functions
// File: config/user_functions.php

/**
 * Check if user has specific permission
 */
function hasPermission($user_id, $permission, $connect) {
    $query = "SELECT COUNT(*) as count FROM user_permissions 
              WHERE user_id = ? AND permission = ? AND granted = 1";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $permission);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

/**
 * Check if user has admin role
 */
function isAdmin($user_id, $connect) {
    $query = "SELECT role FROM users WHERE user_id = ? AND status = 'active'";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    return $user && $user['role'] === 'admin';
}

/**
 * Get user role
 */
function getUserRole($user_id, $connect) {
    $query = "SELECT role FROM users WHERE id = ? AND status = 'active'";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    return $user ? $user['role'] : null;
}

/**
 * Get all permissions for a user
 */
function getUserPermissions($user_id, $connect) {
    $query = "SELECT permission FROM user_permissions 
              WHERE user_id = ? AND granted = 1";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $permissions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $permissions[] = $row['permission'];
    }
    return $permissions;
}

/**
 * Assign default permissions based on role
 */
function assignDefaultPermissions($user_id, $role, $connect) {
    $permissions = [];
    
    switch ($role) {
        case 'manager':
            $permissions = [
                'user_management',
                'inventory_management', 
                'sales_management',
                'reports_access',
                'system_settings',
                'audit_logs'
            ];
            break;
            
        case 'sales_rep':
            $permissions = [
                'inventory_view',
                'sales_management'
            ];
            break;
    }
    
    // Clear existing permissions
    $delete_query = "DELETE FROM user_permissions WHERE user_id = ?";
    $stmt = mysqli_prepare($connect, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    // Insert new permissions
    foreach ($permissions as $permission) {
        $insert_query = "INSERT INTO user_permissions (user_id, permission, granted) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($connect, $insert_query);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $permission);
        mysqli_stmt_execute($stmt);
    }
}

/**
 * Log user activity for audit trail
 */
function logUserActivity($user_id, $action, $table_affected = null, $record_id = null, $old_values = null, $new_values = null, $connect) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $query = "INSERT INTO user_audit_log (user_id, action, table_affected, record_id, old_values, new_values, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ississss", 
        $user_id, 
        $action, 
        $table_affected, 
        $record_id, 
        json_encode($old_values), 
        json_encode($new_values), 
        $ip_address, 
        $user_agent
    );
    mysqli_stmt_execute($stmt);
}

/**
 * Update user last login time
 */
function updateLastLogin($user_id, $connect) {
    $query = "UPDATE users SET last_login = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
}

/**
 * Create user session record
 */
function createUserSession($user_id, $session_id, $connect) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Deactivate old sessions for this user
    $deactivate_query = "UPDATE user_sessions SET is_active = 0 WHERE user_id = ?";
    $stmt = mysqli_prepare($connect, $deactivate_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    // Create new session
    $insert_query = "INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $insert_query);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $session_id, $ip_address, $user_agent);
    mysqli_stmt_execute($stmt);
}

/**
 * Validate user session
 */
function isValidSession($user_id, $session_id, $connect) {
    $query = "SELECT COUNT(*) as count FROM user_sessions 
              WHERE user_id = ? AND session_id = ? AND is_active = 1";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

/**
 * Get user statistics
 */
function getUserStats($connect) {
    $stats = [];
    
    // Total users
    $query = "SELECT COUNT(*) as total FROM users";
    $result = mysqli_query($connect, $query);
    $stats['total_users'] = mysqli_fetch_assoc($result)['total'];
    
    // Active users
    $query = "SELECT COUNT(*) as active FROM users WHERE status = 'active'";
    $result = mysqli_query($connect, $query);
    $stats['active_users'] = mysqli_fetch_assoc($result)['active'];
    
    // Users by role
    $query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
    $result = mysqli_query($connect, $query);
    $stats['by_role'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['by_role'][$row['role']] = $row['count'];
    }
    
    // Recent logins (last 24 hours)
    $query = "SELECT COUNT(*) as recent FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $result = mysqli_query($connect, $query);
    $stats['recent_logins'] = mysqli_fetch_assoc($result)['recent'];
    
    return $stats;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isStrongPassword($password) {
    // At least 8 characters, one uppercase, one lowercase, one number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
}

/**
 * Generate secure random password
 */
function generateRandomPassword($length = 12) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $password;
}

/**
 * Check if username is available
 */
function isUsernameAvailable($username, $user_id = null, $connect) {
    $query = "SELECT COUNT(*) as count FROM users WHERE username = ?";
    
    if ($user_id) {
        $query .= " AND id != ?";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "si", $username, $user_id);
    } else {
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] == 0;
}

/**
 * Check if email is available
 */
function isEmailAvailable($email, $user_id = null, $connect) {
    $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    
    if ($user_id) {
        $query .= " AND id != ?";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
    } else {
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] == 0;
}

/**
 * Get role display name
 */
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Administrator',
        'manager' => 'Manager', 
        'pharmacist' => 'Pharmacist',
        'sales_rep' => 'Sales Representative'
    ];
    
    return $roles[$role] ?? ucfirst($role);
}

/**
 * Get role permissions description
 */
function getRolePermissions($role) {
    $permissions = [
        'admin' => 'Full system access including user management, system settings, and all modules',
        'manager' => 'Access to inventory, sales, and reports. Can view user information',
        'pharmacist' => 'Can view and update inventory, manage sales, and view reports',
        'sales_rep' => 'Can view inventory, manage sales, and view reports'
    ];
    
    return $permissions[$role] ?? 'Limited access based on assigned permissions';
}
?>