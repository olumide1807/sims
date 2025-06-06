<?php
session_start();
include "../../config/session_check.php";
include "../../config/config.php";
include "../../config/user_function.php";
include "../../phpmail.php";
include "../../config/notification_functions.php";

// Check if user has admin privileges or user management permissions
/* if (!isset($_SESSION['user_id']) || (!isAdmin($_SESSION['user_id'], $connect) && !hasPermission($_SESSION['user_id'], 'user_management', $connect))) {
    header("Location: ../dashboard/");
    exit();
} */

// Handle user operations
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $firstname = sanitizeInput($_POST['firstname']);
                $lastname = sanitizeInput($_POST['lastname']);
                $email = sanitizeInput($_POST['email']);
                $home_address = sanitizeInput($_POST['home_address']);
                $phone = $_POST['phone'];
                $role = sanitizeInput($_POST['role']);
                $status = isset($_POST['status']) ? 'active' : 'inactive';

                // Validate inputs
                if (!isValidEmail($email)) {
                    $error = "Please enter a valid email address!";
                } elseif (!isEmailAvailable($email, null, $connect)) {
                    $error = "Email already exists!";
                } else {
                    function generateStrongPassword($length = 8)
                    {
                        $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $lower = "abcdefghijklmnopqrstuvwxyz";
                        $numbers = "0123456789";
                        $symbols = "!@#$%^&*()-_=+";

                        $all = $upper . $lower . $numbers . $symbols;
                        $password = $upper[rand(0, strlen($upper) - 1)] .
                            $lower[rand(0, strlen($lower) - 1)] .
                            $numbers[rand(0, strlen($numbers) - 1)] .
                            $symbols[rand(0, strlen($symbols) - 1)];

                        for ($i = 4; $i < $length; $i++) {
                            $password .= $all[rand(0, strlen($all) - 1)];
                        }

                        return str_shuffle($password);
                    }

                    $password = generateStrongPassword(8);
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = mysqli_prepare($connect, "INSERT INTO users (firstname, lastname, email, home_address, phone, password, role, status, date_registered) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    mysqli_stmt_bind_param($stmt, "ssssssss", $firstname, $lastname, $email, $home_address, $phone, $hashed_password, $role, $status);

                    if (mysqli_stmt_execute($stmt)) {
                        $new_user_id = mysqli_insert_id($connect);
                        $recipient = $email;
                        $subject = "Login Details";
                        $body = "You have successfully created an account. Kindly login with your mail and the password specified below\n
                        Password: {$password}\n
                        You are adviced to change your password after your first login,\n
                        Thank you.";
                        $headers = "From: olumide@gmail.com\r\n";

                        // send email
                        $result = sendEmail($recipient, $subject, $body, $headers);
                        echo $result;
                        echo "<script> alert('An Email has been sent to " . $recipient . ". Kindly check for Login Details');
                                window.location.href = 'manage_users.php';
                        </script>";

                        // Assign default permissions based on role
                        assignDefaultPermissions($new_user_id, $role, $connect);

                        $message = "User added successfully!";
                    } else {
                        $error = "Error adding user: " . mysqli_error($connect);
                    }
                }
                break;

            case 'edit_user':
                $user_id = (int)$_POST['user_id'];
                $firstname = sanitizeInput($_POST['firstname']);
                $lastname = sanitizeInput($_POST['lastname']);
                $email = sanitizeInput($_POST['email']);
                $role = sanitizeInput($_POST['role']);
                $status = isset($_POST['status']) ? 'active' : 'inactive';

                // Get old values for audit log - Fixed column name consistency
                $old_query = "SELECT * FROM users WHERE user_id = ?";
                $stmt = mysqli_prepare($connect, $old_query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $old_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                // Validate inputs
                if (!isValidEmail($email)) {
                    $error = "Please enter a valid email address!";
                } elseif (!isEmailAvailable($email, $user_id, $connect)) {
                    $error = "Email already exists!";
                } else {
                    $stmt = mysqli_prepare($connect, "UPDATE users SET firstname = ?, lastname = ?, email = ?, role = ?, status = ?, updated_at = NOW() WHERE user_id = ?");
                    mysqli_stmt_bind_param($stmt, "sssssi", $firstname, $lastname, $email, $role, $status, $user_id);

                    if (mysqli_stmt_execute($stmt)) {
                        // Update permissions if role changed
                        if ($old_user['role'] !== $role) {
                            assignDefaultPermissions($user_id, $role, $connect);
                        }

                        // Log the activity
                        /* logUserActivity(
                            $_SESSION['user_id'],
                            'USER_UPDATED',
                            'users',
                            $user_id,
                            ['firstname' => $old_user['firstname'], 'lastname' => $old_user['lastname'], 'email' => $old_user['email'], 'role' => $old_user['role'], 'status' => $old_user['status']],
                            ['firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'role' => $role, 'status' => $status],
                            $connect
                        ); */

                        $message = "User updated successfully!";
                    } else {
                        $error = "Error updating user: " . mysqli_error($connect);
                    }
                }
                break;

            case 'delete_user':
                $user_id = (int)$_POST['user_id'];

                // Don't allow deletion of current user
                if ($user_id == $_SESSION['user_id']) {
                    $error = "You cannot delete your own account!";
                } else {
                    // Get user data for audit log
                    $user_query = "SELECT * FROM users WHERE user_id = ?";
                    $stmt = mysqli_prepare($connect, $user_query);
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    $user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                    $stmt = mysqli_prepare($connect, "DELETE FROM users WHERE user_id = ?");
                    mysqli_stmt_bind_param($stmt, "i", $user_id);

                    if (mysqli_stmt_execute($stmt)) {
                        $message = "User deleted successfully!";
                    } else {
                        $error = "Error deleting user: " . mysqli_error($connect);
                    }
                }
                break;

            case 'change_password':
                $user_id = (int)$_POST['user_id'];
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                // Check if user is trying to change their own password
                if ($user_id == $_SESSION['user_id']) {
                    // First, verify current password
                    $stmt = mysqli_prepare($connect, "SELECT password FROM users WHERE user_id = ?");
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $user_data = mysqli_fetch_assoc($result);

                    if (!$user_data || !password_verify($current_password, $user_data['password'])) {
                        $error = "Current password is incorrect!";
                    } elseif ($new_password !== $confirm_password) {
                        $error = "New passwords do not match!";
                    } elseif (!isStrongPassword($new_password)) {
                        $error = "Password must be at least 8 characters with uppercase, lowercase, and number!";
                    } elseif ($current_password === $new_password) {
                        $error = "New password must be different from current password!";
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                        $stmt = mysqli_prepare($connect, "UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?");
                        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);

                        if (mysqli_stmt_execute($stmt)) {
                            // Log the activity
                            // logUserActivity($_SESSION['user_id'], 'PASSWORD_CHANGED', 'users', $user_id, null, null, $connect);
                            $message = "Your password has been changed successfully!";
                        } else {
                            $error = "Error changing password: " . mysqli_error($connect);
                        }
                    }
                } else {
                    $error = "You can only change your own password!";
                }
                break;

            case 'request_password_reset':
                $user_id = (int)$_POST['user_id'];

                // Get user data
                $user_query = "SELECT firstname, lastname, email FROM users WHERE user_id = ?";
                $stmt = mysqli_prepare($connect, $user_query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                if ($user_data) {
                    // Generate new temporary password
                    function generateStrongPassword($length = 8)
                    {
                        $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $lower = "abcdefghijklmnopqrstuvwxyz";
                        $numbers = "0123456789";
                        $symbols = "!@#$%^&*()-_=+";

                        $all = $upper . $lower . $numbers . $symbols;
                        $password = $upper[rand(0, strlen($upper) - 1)] .
                            $lower[rand(0, strlen($lower) - 1)] .
                            $numbers[rand(0, strlen($numbers) - 1)] .
                            $symbols[rand(0, strlen($symbols) - 1)];

                        for ($i = 4; $i < $length; $i++) {
                            $password .= $all[rand(0, strlen($all) - 1)];
                        }

                        return str_shuffle($password);
                    }

                    $temp_password = generateStrongPassword(8);
                    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

                    // Update user password
                    $stmt = mysqli_prepare($connect, "UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?");
                    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);

                    if (mysqli_stmt_execute($stmt)) {
                        // Send email with new password
                        $recipient = $user_data['email'];
                        $subject = "Password Reset - SIMS";
                        $body = "Hello " . $user_data['firstname'] . " " . $user_data['lastname'] . ",\n\n";
                        $body .= "Your password has been reset by an administrator.\n";
                        $body .= "Your new temporary password is: {$temp_password}\n\n";
                        $body .= "Please login and change your password immediately.\n\n";
                        $body .= "Thank you.";
                        $headers = "From: olumide@gmail.com\r\n";

                        // Send email
                        $result = sendEmail($recipient, $subject, $body, $headers);

                        echo "<script>
                            alert('Password reset email has been sent to " . $recipient . "');
                            window.location.href = 'manage_users.php';
                        </script>";

                        $message = "Password reset email sent successfully!";
                    } else {
                        $error = "Error resetting password: " . mysqli_error($connect);
                    }
                } else {
                    $error = "User not found!";
                }
                break;
        }
    }
}

$user_id = $_SESSION['user_id'];

// Get notification data
$notification_count = getNotificationCount($user_id, $connect);
$notifications = getUserNotifications($user_id, $connect, 5); // Get 5 latest notifications
$notification_stats = getNotificationStats(getUserNotificationSettings($user_id, $connect), $connect);

// Fetch all users
$users_query = "SELECT * FROM users ORDER BY date_registered DESC";
$users_result = mysqli_query($connect, $users_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/css/style.css">

    <?php echo getNotificationDropdownCSS(); ?>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="layout-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo-section">
                <h4 class="d-flex align-items-center gap-2">
                    <i class="fas fa-cubes text-primary"></i>
                    SIMS
                </h4>
            </div>

            <nav>
                <!-- Dashboard -->
                <a href="../dashboard/" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <!-- Inventory Management -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('inventory')">
                    <i class="fas fa-box"></i> Inventory Management
                </a>
                <div class="submenu" id="inventory">
                    <a href="../inventory/" class="nav-link"><i class="fas fa-list"></i> View Inventory</a>
                    <a href="../inventory/addproduct.php" class="nav-link"><i class="fas fa-plus"></i> Add Product</a>
                    <!-- <a href="../inventory/updateproduct.php" class="nav-link"><i class="fas fa-edit"></i> Update Inventory</a> -->
                </div>

                <!-- Sales Management -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-shopping-cart"></i> Sales Management
                </a>
                <div class="submenu" id="sales">
                    <a href="../sales/logsales.php" class="nav-link"><i class="fas fa-cash-register"></i> Log Sale</a>
                    <a href="../sales/viewsales.php" class="nav-link"><i class="fas fa-chart-bar"></i> View Sales</a>
                </div>

                <!-- Reports -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
                <div class="submenu" id="reports">
                    <a href="../report/generateReport.php" class="nav-link"><i class="fas fa-file-export"></i> Generate Reports</a>
                    <a href="../report/viewreport.php" class="nav-link"><i class="fas fa-file-import"></i> View Reports</a>
                </div>

                <!-- Settings -->
                <a href="#" class="nav-link active" onclick="toggleSubmenu('settings')">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="submenu show" id="settings">
                    <a href="#" class="nav-link active"><i class="fas fa-users"></i> User Management</a>
                    <a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="reports_settings.php" class="nav-link"><i class="fas fa-file-cog"></i> Report Settings</a>
                    <a href="system_preferences.php" class="nav-link"><i class="fas fa-sliders-h"></i> System Preferences</a>
                    <!-- <a href="inventory_settings.php" class="nav-link"><i class="fas fa-box-open"></i> Inventory Settings</a> -->
                </div>

                <!-- Logout -->
                <a href="../../logout/" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div>
                    <!-- <i class="fas fa-search text-muted me-2"></i>
                    <input type="text" placeholder="Search..."> -->
                </div>
                <div class="user-section">
                    <?php echo generateNotificationDropdown($user_id, $connect); ?>
                    <div class="user-info ms-3">
                        <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['firstname']); ?></span>
                        <small class="d-block text-muted"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></small>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>User Management</h3>
                        <p class="mb-3">Manage user accounts, roles, and permissions.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Add New User
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">System Users</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <?php echo strtoupper(substr($user['firstname'], 0, 1) . substr($user['lastname'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="status-badge bg-<?php echo $user['role'] === 'sales_rep' ? 'danger' : ($user['role'] === 'manager' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['date_registered'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editUser(<?php echo $user['user_id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                                <!-- Current user can change their own password -->
                                                <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#changePasswordModal"
                                                    onclick="setPasswordUserId(<?php echo $user['user_id']; ?>)">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- For other users, show password reset option -->
                                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal"
                                                    onclick="setResetUserId(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>')">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="firstname" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="lastname" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Home Address</label>
                            <input type="text" class="form-control" name="home_address" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" required minlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="">Select Role</option>
                                <option value="manager">Manager</option>
                                <option value="sales_rep">Sales Representative</option>
                            </select>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="status" id="statusCheck" checked>
                            <label class="form-check-label" for="statusCheck">
                                Active User
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" id="editUserId">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="firstname" id="editFirstname" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="lastname" id="editLastname" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="editRole" required>
                                <option value="manager">Manager</option>
                                <option value="sales_rep">Sales Representative</option>
                            </select>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="status" id="editStatusCheck">
                            <label class="form-check-label" for="editStatusCheck">
                                Active User
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change My Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="user_id" id="passwordUserId">

                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                            <small class="form-text text-muted">Enter your current password to verify your identity</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="8">
                            <small class="form-text text-muted">At least 8 characters with uppercase, lowercase, and number</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="8">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset User Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will generate a new temporary password and send it to the user's email address.
                    </div>
                    <p>Are you sure you want to reset the password for:</p>
                    <p class="text-primary"><strong id="resetUserName"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="request_password_reset">
                        <input type="hidden" name="user_id" id="resetUserId">
                        <button type="submit" class="btn btn-warning">Send Reset Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                    <p class="text-muted"><strong id="deleteUserName"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
    <script>
        // Store users data for JavaScript access
        const usersData = <?php
                            mysqli_data_seek($users_result, 0); // Reset result pointer
                            $users_array = [];
                            while ($user = mysqli_fetch_assoc($users_result)) {
                                $users_array[] = $user;
                            }
                            echo json_encode($users_array);
                            ?>;

        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            if (submenu) {
                const allSubmenus = document.querySelectorAll('.submenu');
                allSubmenus.forEach(menu => {
                    if (menu !== submenu) {
                        menu.classList.remove('show');
                    }
                });
                submenu.classList.toggle('show');
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        function setResetUserId(userId, userName) {
            document.getElementById('resetUserId').value = userId;
            document.getElementById('resetUserName').textContent = userName;
        }

        function setPasswordUserId(userId) {
            // Only allow if it's the current user
            if (userId == <?php echo $_SESSION['user_id']; ?>) {
                document.getElementById('passwordUserId').value = userId;
            }
        }

        function editUser(userId) {
            // Find user data
            const user = usersData.find(u => u.user_id == userId);
            if (user) {
                // Populate form fields
                document.getElementById('editUserId').value = user.user_id;
                document.getElementById('editFirstname').value = user.firstname;
                document.getElementById('editLastname').value = user.lastname;
                document.getElementById('editEmail').value = user.email;
                document.getElementById('editRole').value = user.role;
                document.getElementById('editStatusCheck').checked = user.status === 'active';

                // Show modal
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            }
        }

        function confirmDelete(userId, userName) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.querySelector('#changePasswordModal form');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const currentPassword = this.querySelector('input[name="current_password"]').value;
                    const newPassword = this.querySelector('input[name="new_password"]').value;
                    const confirmPassword = this.querySelector('input[name="confirm_password"]').value;

                    // Check if current password is provided
                    if (!currentPassword.trim()) {
                        e.preventDefault();
                        alert('Please enter your current password!');
                        return;
                    }

                    // Check if new password matches confirmation
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert('New passwords do not match!');
                        return;
                    }

                    // Check if new password is different from current
                    if (currentPassword === newPassword) {
                        e.preventDefault();
                        alert('New password must be different from current password!');
                        return;
                    }

                    // Basic password strength check
                    if (newPassword.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long!');
                        return;
                    }

                    const hasUpper = /[A-Z]/.test(newPassword);
                    const hasLower = /[a-z]/.test(newPassword);
                    const hasNumber = /\d/.test(newPassword);

                    if (!hasUpper || !hasLower || !hasNumber) {
                        e.preventDefault();
                        alert('Password must contain at least one uppercase letter, one lowercase letter, and one number!');
                        return;
                    }
                });

                // Clear form when modal is closed
                const modal = document.getElementById('changePasswordModal');
                modal.addEventListener('hidden.bs.modal', function() {
                    passwordForm.reset();
                });
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
</body>

</html>