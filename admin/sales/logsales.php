<?php
session_start();

include "../../config/config.php";
include "../../config/session_check.php";
include "../../config/notification_functions.php";

// Initialize variables
$products = [];
$success_message = "";
$error_message = "";

// Fetch all products from the database
$query = "SELECT id, product_name, price_per_sachet
              FROM products
              ORDER BY product_name ASC";
$result = mysqli_query($connect, $query);

if ($result) {
    // Modify the variants fetching logic
    while ($row = mysqli_fetch_assoc($result)) {
        $product_id = $row['id'];
        $products[$product_id] = $row;

        $variants_query = "SELECT * FROM product_variants WHERE product_id = ?";
        $stmt = mysqli_prepare($connect, $variants_query);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $variants_result = mysqli_stmt_get_result($stmt);

        $products[$product_id]['variants'] = [];
        while ($variant = mysqli_fetch_assoc($variants_result)) {
            $products[$product_id]['variants'][] = $variant;
        }
    }
}

// Fetch pending sales
$pending_sales = [];
$pending_query = "SELECT 
                    s.id,
                    s.transaction_number,
                    s.payment_method,
                    s.subtotal,
                    s.tax_amount,
                    s.total_amount,
                    s.sale_date,
                    s.created_by,
                    CONCAT(u.firstname, ' ', u.lastname) as created_by_name
                  FROM 
                    sales s
                  LEFT JOIN 
                    users u ON s.created_by = u.user_id
                  WHERE 
                    s.sale_status = 'Pending'
                  ORDER BY 
                    s.sale_date DESC";

$pending_result = mysqli_query($connect, $pending_query);

if ($pending_result && mysqli_num_rows($pending_result) > 0) {
    while ($pending_sale = mysqli_fetch_assoc($pending_result)) {
        $sale_id = $pending_sale['id'];

        // Fetch sale details for each pending sale
        $details_query = "SELECT 
                            sd.quantity,
                            sd.unit_price,
                            sd.total_price,
                            p.product_name,
                            pv.variant_name
                          FROM 
                            sale_details sd
                          LEFT JOIN 
                            products p ON sd.product_id = p.id
                          LEFT JOIN 
                            product_variants pv ON sd.variant_id = pv.id
                          WHERE 
                            sd.sale_id = '$sale_id'";

        $details_result = mysqli_query($connect, $details_query);
        $sale_details = [];

        if ($details_result && mysqli_num_rows($details_result) > 0) {
            while ($detail = mysqli_fetch_assoc($details_result)) {
                $sale_details[] = $detail;
            }
        }

        $pending_sale['details'] = $sale_details;
        $pending_sale['created_by'] = $pending_sale['created_by_name'] ?: 'Unknown';
        $pending_sales[] = $pending_sale;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['sale_id'])) {
    $sale_id = mysqli_real_escape_string($connect, $_POST['sale_id']);
    $action = $_POST['action'];

    error_log("Processing pending sale action: " . $action . " for sale ID: " . $sale_id);

    mysqli_begin_transaction($connect);
    try {
        if ($action === 'complete') {
            // Update sale status to completed
            $update_query = "UPDATE sales SET 
                            sale_status = 'Completed',
                            transaction_number = REPLACE(transaction_number, 'PND-', 'TXN-') 
                            WHERE id = ? AND sale_status = 'Pending'";
            $stmt = mysqli_prepare($connect, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $sale_id);
            $result = mysqli_stmt_execute($stmt);

            if (!$result) {
                throw new Exception('Failed to update sale status');
            }

            // Check if any rows were affected
            if (mysqli_stmt_affected_rows($stmt) == 0) {
                throw new Exception('Sale not found or not in pending status');
            }

            // Get sale details to update inventory
            $details_query = "SELECT product_id, variant_id, quantity FROM sale_details WHERE sale_id = ?";
            $details_stmt = mysqli_prepare($connect, $details_query);
            mysqli_stmt_bind_param($details_stmt, "i", $sale_id);
            mysqli_stmt_execute($details_stmt);
            $details_result = mysqli_stmt_get_result($details_stmt);

            // Update inventory for each product
            while ($detail = mysqli_fetch_assoc($details_result)) {
                $product_id = $detail['product_id'];
                $variant_id = $detail['variant_id'];
                $quantity = $detail['quantity'];

                if ($variant_id) {
                    // Update variant stock
                    $update_inventory = "UPDATE product_variants SET qty_sachet = qty_sachet - ? WHERE id = ?";
                    $inventory_stmt = mysqli_prepare($connect, $update_inventory);
                    mysqli_stmt_bind_param($inventory_stmt, "di", $quantity, $variant_id);
                } else {
                    // Update product stock
                    $update_inventory = "UPDATE products SET quantity_per_pack = quantity_per_pack - ? WHERE id = ?";
                    $inventory_stmt = mysqli_prepare($connect, $update_inventory);
                    mysqli_stmt_bind_param($inventory_stmt, "di", $quantity, $product_id);
                }

                $inventory_result = mysqli_stmt_execute($inventory_stmt);
                if (!$inventory_result) {
                    throw new Exception('Failed to update inventory for item');
                }
            }

            mysqli_commit($connect);
            $success_message = "Sale has been completed successfully!";
        } elseif ($action === 'cancel') {
            // Update sale status to canceled
            $update_query = "UPDATE sales SET 
                            sale_status = 'Cancelled',
                            transaction_number = REPLACE(transaction_number, 'PND-', 'CNL-')
                            WHERE id = ? AND sale_status = 'Pending'";
            $stmt = mysqli_prepare($connect, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $sale_id);
            $result = mysqli_stmt_execute($stmt);

            if (!$result) {
                throw new Exception('Failed to cancel sale');
            }

            // Check if any rows were affected
            if (mysqli_stmt_affected_rows($stmt) == 0) {
                throw new Exception('Sale not found or not in pending status');
            }

            mysqli_commit($connect);
            $success_message = "Sale has been canceled successfully!";
        }

        // Redirect to prevent form resubmission
        header("Location: logsales.php?success=1&message=" . urlencode($success_message));
        exit;
    } catch (Exception $e) {
        mysqli_rollback($connect);
        $error_message = "Error processing sale: " . $e->getMessage();
        error_log("Process Sale Error: " . $e->getMessage());
    }
}

// Check for success message from pending sale actions
if (isset($_GET['message'])) {
    $success_message = $_GET['message'];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check which button was clicked to determine the sale status
    if (isset($_POST['save_sale'])) {
        $sale_status = 'Completed';
    } elseif (isset($_POST['pend_sale'])) {
        $sale_status = 'Pending';
    } elseif (isset($_POST['cancel_sale'])) {
        $sale_status = 'Cancelled';
    } else {
        // Not a recognized sale submission
        exit;
    }

    // Initialize sale variables
    $subtotal = 0;
    $tax_rate = 0.02; // 2% tax rate
    $tax_amount = 0;
    $total_amount = 0;
    $sale_date = date('Y-m-d H:i:s');
    // $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : NULL;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Cash';
    $payment_reference = isset($_POST['payment_reference']) ? $_POST['payment_reference'] : NULL;

    // Debug output to check if data is coming through (remove in production)
    error_log("Sale Status: " . $sale_status);
    error_log("Payment Method: " . $payment_method);
    error_log("Payment Reference: " . $payment_reference);

    // Start transaction
    mysqli_begin_transaction($connect);
    try {
        // Calculate totals based on submitted form data
        $product_ids = isset($_POST['product_id']) ? $_POST['product_id'] : [];
        $variant_ids = isset($_POST['variant_id']) ? $_POST['variant_id'] : [];
        $quantities = isset($_POST['quantity']) ? $_POST['quantity'] : [];
        $unit_prices = isset($_POST['unit_price']) ? $_POST['unit_price'] : [];

        // Validate that we have product data
        if (empty($product_ids)) {
            throw new Exception("No products selected");
        }

        // Calculate subtotal
        foreach ($product_ids as $index => $product_id) {
            if (empty($product_id)) continue;

            $quantity = $quantities[$index];
            $unit_price = $unit_prices[$index];
            $item_total = $quantity * $unit_price;
            $subtotal += $item_total;
        }

        $tax_amount = $subtotal * $tax_rate;
        $total_amount = $subtotal + $tax_amount;

        // Insert into sales table
        $sale_query = "INSERT INTO sales (sale_date, subtotal, tax_amount, total_amount, created_by, payment_method, transaction_number, sale_status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $sale_query);

        if (!$stmt) {
            throw new Exception("Database prepare error: " . mysqli_error($connect));
        }

        mysqli_stmt_bind_param($stmt, "sdddisss", $sale_date, $subtotal, $tax_amount, $total_amount, $_SESSION['user_id'], $payment_method, $payment_reference, $sale_status);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
            throw new Exception("Database execute error: " . mysqli_stmt_error($stmt));
        }

        $sale_id = mysqli_insert_id($connect);

        if (!$sale_id) {
            throw new Exception("Failed to get sale ID");
        }

        // Insert sale details
        foreach ($product_ids as $index => $product_id) {
            if (empty($product_id)) continue;

            $variant_id = isset($variant_ids[$index]) && !empty($variant_ids[$index]) ? $variant_ids[$index] : NULL;
            $quantity = $quantities[$index];
            $unit_price = $unit_prices[$index];
            $item_total = $quantity * $unit_price;

            $detail_query = "INSERT INTO sale_details (sale_id, product_id, variant_id, quantity, unit_price, total_price) 
                                VALUES (?, ?, ?, ?, ?, ?)";
            $detail_stmt = mysqli_prepare($connect, $detail_query);

            if (!$detail_stmt) {
                throw new Exception("Database prepare error (detail): " . mysqli_error($connect));
            }

            mysqli_stmt_bind_param($detail_stmt, "iiiddd", $sale_id, $product_id, $variant_id, $quantity, $unit_price, $item_total);
            $detail_result = mysqli_stmt_execute($detail_stmt);

            if (!$detail_result) {
                throw new Exception("Database execute error (detail): " . mysqli_stmt_error($detail_stmt));
            }
        }

        // Only update inventory if sale is completed (not pending or canceled)
        if ($sale_status === 'Completed') {
            foreach ($product_ids as $index => $product_id) {
                if (empty($product_id)) continue;

                $variant_id = isset($variant_ids[$index]) && !empty($variant_ids[$index]) ? $variant_ids[$index] : NULL;
                $quantity = $quantities[$index];

                // Update inventory quantity
                if ($variant_id) {
                    // Update variant stock
                    $update_query = "UPDATE product_variants SET qty_sachet = qty_sachet - ? WHERE id = ?";
                    $update_stmt = mysqli_prepare($connect, $update_query);
                    mysqli_stmt_bind_param($update_stmt, "di", $quantity, $variant_id);
                } else {
                    // Update product stock
                    $update_query = "UPDATE products SET quantity_per_pack = quantity_per_pack - ? WHERE id = ?";
                    $update_stmt = mysqli_prepare($connect, $update_query);
                    mysqli_stmt_bind_param($update_stmt, "di", $quantity, $product_id);
                }

                $update_result = mysqli_stmt_execute($update_stmt);

                if (!$update_result) {
                    throw new Exception("Database execute error (update): " . mysqli_stmt_error($update_stmt));
                }
            }
        }

        // Commit the transaction
        mysqli_commit($connect);

        // Set appropriate success message based on sale status
        if ($sale_status === 'Completed') {
            $success_message = "Sale has been successfully recorded!";
        } elseif ($sale_status === 'Pending') {
            $success_message = "Sale has been saved as pending!";
        } else {
            $success_message = "Sale has been canceled!";
        }

        // Redirect to prevent form resubmission
        header("Location: logsales.php?success=1&status=" . urlencode($sale_status));
        exit;
    } catch (Exception $e) {
        // Rollback in case of error
        mysqli_rollback($connect);
        $error_message = "Error recording sale: " . $e->getMessage();
        error_log("Sale Error: " . $e->getMessage());
    }
}

// Check for success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $status = isset($_GET['status']) ? $_GET['status'] : 'Completed';

    if ($status === 'Completed') {
        $success_message = "Sale has been successfully recorded!";
    } elseif ($status === 'Pending') {
        $success_message = "Sale has been successfully saved as pending!";
    } elseif ($status === 'Canceled') {
        $success_message = "Sale has been canceled!";
    }
}

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];

// Get notification data
$notification_count = getNotificationCount($user_id, $connect);
$notifications = getUserNotifications($user_id, $connect, 5); // Get 5 latest notifications
$notification_stats = getNotificationStats(getUserNotificationSettings($user_id, $connect), $connect);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Sale - SIMS</title>
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
                <a href="#" class="nav-link active" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-shopping-cart"></i> Sales Management
                </a>
                <div class="submenu show" id="sales">
                    <a href="#" class="nav-link active"><i class="fas fa-cash-register"></i> Log Sale</a>
                    <a href="../sales/viewsales.php" class="nav-link"><i class="fas fa-chart-bar"></i> View Sales</a>
                </div>

                <!-- AI Insights -->
                <!-- <a href="#" class="nav-link" onclick="toggleSubmenu('ai-insights')">
                    <i class="fas fa-robot"></i> AI-Powered Insights
                </a>
                <div class="submenu" id="ai-insights">
                    <a href="#" class="nav-link"><i class="fas fa-bell"></i> Reordering Suggestions</a>
                    <a href="#" class="nav-link"><i class="fas fa-clock"></i> Expiration Alerts</a>
                    <a href="#" class="nav-link"><i class="fas fa-chart-line"></i> Low-Demand Products</a>
                </div> -->

                <!-- Reports -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
                <div class="submenu" id="reports">
                    <a href="../report/generateReport.php" class="nav-link"><i class="fas fa-file-export"></i> Generate Reports</a>
                    <a href="../report/viewreport.php" class="nav-link"><i class="fas fa-file-import"></i> View Reports</a>
                </div>

                <!-- Settings -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('settings')">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="submenu" id="settings">
                    <a href="../settings/manage_users.php" class="nav-link"><i class="fas fa-users"></i> User Management</a>
                    <a href="../settings/notifications.php" class="nav-link"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="../settings/reports_settings.php" class="nav-link"><i class="fas fa-file-cog"></i> Report Settings</a>
                    <a href="../settings/system_preferences.php" class="nav-link"><i class="fas fa-sliders-h"></i> System Preferences</a>
                    <!-- <a href="../settings/inventory_settings.php" class="nav-link"><i class="fas fa-box-open"></i> Inventory Settings</a> -->
                </div>

                <!-- Help/Support -->
                <!-- <a href="#" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help/Support
                </a> -->

                <!-- Logout -->
                <a href="../../logout/" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">

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

            <div class="header">
                <h4 class="mb-0">Log New Sale</h4>
                <div class="d-flex gap-3">
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-clock me-2"></i>View Pending
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Sales Form -->
            <div class="content-card">
                <form id="salesForm" method="POST" action="logsales.php">
                    <!-- Products Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Products</h5>
                        </div>

                        <div id="productsContainer">
                            <div class="product-row">
                                <button type="button" style="float: right;" class="btn btn-outline-danger btn-sm remove-product-btn" onclick="removeProductRow(this)"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Remove this product">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Product</label>
                                        <select class="form-select product-select" name="product_id[]" onchange="handleProductSelection(this)">
                                            <option value="">Select Product</option>
                                            <?php foreach ($products as $product_id => $product): ?>
                                                <option value="<?php echo $product_id; ?>"
                                                    data-has-variants="<?php echo !empty($product['variants']) ? '1' : '0'; ?>"
                                                    data-price="<?php echo $product['price_per_sachet']; ?>">
                                                    <?php echo $product['product_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <!-- Variant Selector (Will show/hide based on selected product) -->
                                        <div class="variant-select">
                                            <label class="form-label">Variant</label>
                                            <select class="form-select variant-select-dropdown" name="variant_id[]" onchange="updatePriceFromVariant(this)">
                                                <option value="">Select Variant</option>
                                                <!-- Options will be populated dynamically -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control quantity-input" name="quantity[]" min="1" value="1" onchange="updateTotal(this)">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Unit Price</label>
                                        <input type="number" class="form-control unit-price" name="unit_price[]" value="0.00" step="0.01" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Total</label>
                                        <input type="number" class="form-control item-total" name="item_total[]" value="0.00" step="0.01" readonly>
                                    </div>
                                    <!-- Add this hidden input field to your form -->
                                    <input type="hidden" id="selectedPaymentMethod" name="payment_method" value="Cash">
                                </div>
                            </div>
                        </div>

                        <!-- Add Product button moved here, aligned to the right -->
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addProductRow()">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </button>
                        </div>
                    </div>

                    <!-- Summary Section -->
                    <div class="summary-section mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h6 class="mb-2">Subtotal</h6>
                                <h4 id="subtotal">₵0.00</h4>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h6 class="mb-2">Tax (10%)</h6>
                                <h4 id="tax">₵0.00</h4>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-2">Total Amount</h6>
                                <h4 id="total">₵0.00</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Save Sale button - full width -->
                    <button type="submit" name="save_sale" class="btn btn-theme-outline btn-lg w-100 py-3">
                        <i class="fas fa-save me-2"></i>Save Sale
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="lastRowToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    At least one product row is required for sales entry!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Payment Method Modal -->
    <div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-labelledby="paymentMethodModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="paymentMethodModalLabel">Select payment method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-4">Preferred method used with secure transactions.</p>

                    <div class="payment-methods">
                        <!-- Cash -->
                        <div class="payment-option" data-payment="Cash">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="payment-icon me-3">
                                        <i class="fas fa-money-bill-wave fs-3 text-success"></i>
                                    </div>
                                    <span>Pay with Cash</span>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </div>

                        <!-- Mobile Money -->
                        <div class="payment-option" data-payment="Mobile Money">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="payment-icon me-3">
                                        <i class="fas fa-mobile-alt fs-3 text-primary"></i>
                                    </div>
                                    <span>Pay with Mobile Money</span>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </div>

                        <!-- PoS -->
                        <div class="payment-option" data-payment="PoS">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="payment-icon me-3">
                                        <i class="fas fa-credit-card fs-3 text-danger"></i>
                                    </div>
                                    <span>Pay with PoS</span>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </div>

                        <!-- Bank Transfer -->
                        <div class="payment-option" data-payment="Bank Transfer">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="payment-icon me-3">
                                        <i class="fas fa-university fs-3 text-info"></i>
                                    </div>
                                    <span>Pay with Bank Transfer</span>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-block border-0 pt-0">
                    <button type="button" id="continueWithPayment" class="btn btn-primary w-100 py-2 mb-2" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                        Continue
                    </button>
                    <button type="button" class="btn btn-link text-muted w-100" data-bs-dismiss="modal">
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Screen -->
    <div class="modal fade" id="cashPaymentModal" tabindex="-1" aria-labelledby="cashPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="cashPaymentModalLabel">Cash Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <p class="mb-1">Amount Due</p>
                        <div class="payment-amount mb-3" id="cashAmountDue">$0.00</div>
                    </div>

                    <div class="mb-4">
                        <label for="cashReceived" class="form-label">Cash Received</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">₵</span>
                            <input type="number" class="form-control form-control-lg" id="cashReceived" placeholder="0.00" min="0" step="0.01">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="cashChange" class="form-label">Change</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">₵</span>
                            <input type="number" class="form-control form-control-lg" id="cashChange" value="0.00" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" id="cancelSale" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="pendSale">Pend Sale</button>
                    <button type="button" class="btn btn-primary" id="completeCashPayment" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);" disabled>
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MoMo Screen -->
    <div class="modal fade" id="mobileMoneyModal" tabindex="-1" aria-labelledby="mobileMoneyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="mobileMoneyModalLabel">Mobile Money Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <p class="mb-1">Amount Due</p>
                        <div class="payment-amount mb-3" id="mobileMoneyAmountDue">₵0.00</div>
                    </div>

                    <div class="mb-3">
                        <label for="mobileMoneyProvider" class="form-label">Provider</label>
                        <select class="form-select form-select-lg" id="mobileMoneyProvider">
                            <option value="">Select Provider</option>
                            <option value="MTN">MTN Mobile Money</option>
                            <option value="Airtel">Airtel Money</option>
                            <option value="Vodafone">Vodafone Cash</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="mobileMoneyNumber" class="form-label">Phone Number</label>
                        <input type="text" class="form-control form-control-lg" id="mobileMoneyNumber" placeholder="Enter phone number">
                    </div>

                    <div class="mb-3">
                        <label for="mobileMoneyReference" class="form-label">Reference/ID</label>
                        <input type="text" class="form-control form-control-lg" id="mobileMoneyReference" placeholder="Enter reference ID">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" id="cancelSale" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="pendSale">Pend Sale</button>
                    <button type="button" class="btn btn-primary" id="completeMobileMoneyPayment" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- POS Screen -->
    <div class="modal fade" id="posPaymentModal" tabindex="-1" aria-labelledby="posPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="posPaymentModalLabel">PoS Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <p class="mb-1">Amount Due</p>
                        <div class="payment-amount mb-3" id="posAmountDue">₵0.00</div>
                    </div>

                    <div class="mb-3">
                        <label for="cardType" class="form-label">Card Type</label>
                        <select class="form-select form-select-lg" id="cardType">
                            <option value="">Select Card Type</option>
                            <option value="Visa">Visa</option>
                            <option value="Mastercard">Mastercard</option>
                            <option value="Verve">Verve</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="posReference" class="form-label">Transaction Reference</label>
                        <input type="text" class="form-control form-control-lg" id="posReference" placeholder="Enter transaction reference">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" id="cancelSale" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="pendSale">Pend Sale</button>
                    <button type="button" class="btn btn-primary" id="completePosPayment" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Transfer Screen -->
    <div class="modal fade" id="bankTransferModal" tabindex="-1" aria-labelledby="bankTransferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="bankTransferModalLabel">Bank Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <p class="mb-1">Amount Due</p>
                        <div class="payment-amount mb-3" id="bankTransferAmountDue">₵0.00</div>
                    </div>

                    <div class="mb-3">
                        <label for="bankName" class="form-label">Bank Name</label>
                        <input type="text" class="form-control form-control-lg" id="bankName" placeholder="Enter bank name">
                    </div>

                    <div class="mb-3">
                        <label for="transferReference" class="form-label">Transfer Reference</label>
                        <input type="text" class="form-control form-control-lg" id="transferReference" placeholder="Enter transfer reference">
                    </div>

                    <div class="mb-3">
                        <label for="accountName" class="form-label">Account Name</label>
                        <input type="text" class="form-control form-control-lg" id="accountName" placeholder="Enter account name">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" id="cancelSale" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="pendSale">Pend Sale</button>
                    <button type="button" class="btn btn-primary" id="completeBankTransferPayment" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Success Modal -->
    <div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="success-checkmark">
                        <div class="check-icon">
                            <span class="icon-line line-tip"></span>
                            <span class="icon-line line-long"></span>
                            <div class="icon-circle"></div>
                            <div class="icon-fix"></div>
                        </div>
                    </div>
                    <h4 class="mb-3 mt-4">Payment Successful!</h4>
                    <p class="mb-4">The sale has been recorded successfully.</p>
                    <div id="paymentDetails" class="text-start mb-4 p-3 bg-light rounded">
                        <!-- Payment details will be filled dynamically -->
                    </div>
                    <button type="button" class="btn btn-primary w-100" id="finishPaymentProcess" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Sales Modal -->
    <div class="modal fade" id="pendingSalesModal" tabindex="-1" aria-labelledby="pendingSalesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pendingSalesModalLabel">Pending Sales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($pending_sales)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5>No pending sales found</h5>
                            <p class="text-muted">All sales have been completed or canceled.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">S/N</th>
                                        <th class="text-center">Transaction ID</th>
                                        <th class="text-center">Items</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Payment Method</th>
                                        <th class="text-center">Created By</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($pending_sales as $sale): ?>
                                        <tr>
                                            <td class="text-center"><?php echo $counter++; ?></td>
                                            <!-- <td><?php echo date('M d, Y h:i A', strtotime($sale['sale_date'])); ?></td> -->
                                            <td><?php echo $sale['transaction_number']; ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-link view-details-btn"
                                                    data-sale-id="<?php echo $sale['id']; ?>">
                                                    View <?php echo count($sale['details']); ?> items
                                                </button>
                                            </td>
                                            <td class="text-center">₵<?php echo number_format($sale['total_amount'], 2); ?></td>
                                            <td class="text-center"><?php echo $sale['payment_method']; ?></td>
                                            <td class="text-center"><?php echo $sale['created_by']; ?></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-success complete-sale-btn"
                                                        data-sale-id="<?php echo $sale['id']; ?>">
                                                        <i class="fas fa-check"></i> Complete
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger cancel-sale-btn"
                                                        data-sale-id="<?php echo $sale['id']; ?>">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Details Modal -->
    <div class="modal fade" id="saleDetailsModal" tabindex="-1" aria-labelledby="saleDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saleDetailsModalLabel">Sale Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="saleDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Sale Status Form -->
    <form id="processSaleForm" method="POST" action="logsales.php" style="display: none;">
        <input type="hidden" name="sale_id" id="processSaleId">
        <input type="hidden" name="action" id="processSaleAction">
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
    <script>
        // Store products data with variants from PHP to JavaScript
        const productsData = <?php echo json_encode($products); ?>;
        let paymentMethodModal, cashPaymentModal, mobileMoneyModal, posPaymentModal, bankTransferModal, paymentSuccessModal;

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

        function addProductRow() {
            const container = document.getElementById('productsContainer');
            const newRow = container.children[0].cloneNode(true);

            // Clear input values
            newRow.querySelectorAll('input').forEach(input => {
                if (input.type === 'number') {
                    input.value = input.min || 0;
                } else {
                    input.value = '';
                }
            });

            // Reset select
            newRow.querySelector('.product-select').value = '';

            // Hide variant selector
            newRow.querySelector('.variant-select').style.display = 'none';

            container.appendChild(newRow);

            // Reinitialize tooltips for the new row
            var newTooltips = newRow.querySelectorAll('[data-bs-toggle="tooltip"]');
            newTooltips.forEach(function(tooltipEl) {
                new bootstrap.Tooltip(tooltipEl);
            });

            // Update summary
            updateSummary();
        }

        function removeProductRow(button) {
            const container = document.getElementById('productsContainer');
            // Prevent removing the last row
            if (container.children.length > 1) {
                button.closest('.product-row').remove();
                // Update summary after removing a row
                updateSummary();
            } else {
                // Show toast notification if this is the last row
                const lastRowToast = new bootstrap.Toast(document.getElementById('lastRowToast'));
                lastRowToast.show();
            }
        }

        function handleProductSelection(selectElement) {
            const productRow = selectElement.closest('.product-row');
            const variantContainer = productRow.querySelector('.variant-select');
            const variantSelect = productRow.querySelector('.variant-select-dropdown');
            const unitPriceInput = productRow.querySelector('.unit-price');
            const quantityInput = productRow.querySelector('.quantity-input');

            // Clear variant options
            while (variantSelect.options.length > 1) {
                variantSelect.remove(1);
            }

            if (selectElement.value) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const productId = selectElement.value;
                const hasVariants = selectedOption.getAttribute('data-has-variants') === '1';
                const price = parseFloat(selectedOption.getAttribute('data-price'));

                if (hasVariants && productsData[productId] && productsData[productId]['variants'] && productsData[productId]['variants'].length > 0) {
                    // Show variant selector and populate options
                    variantContainer.style.display = 'block';

                    // Add variant options
                    productsData[productId]['variants'].forEach(variant => {
                        const option = document.createElement('option');
                        option.value = variant.id; // Use 'id' field from variant
                        option.text = variant.variant_name || 'Unnamed Variant';
                        option.setAttribute('data-price', variant.price_per_sachet || '0.00');
                        option.setAttribute('data-stock', variant.qty_sachet || '0');
                        variantSelect.add(option);
                    });

                    // Reset price until variant is selected
                    unitPriceInput.value = "0.00";
                } else {
                    // Hide variant selector for products without variants
                    variantContainer.style.display = 'none';
                    variantSelect.value = '';

                    // Set price from product
                    unitPriceInput.value = price.toFixed(2);
                }

                // Update total
                updateTotal(quantityInput);
            } else {
                // Hide variant selector if no product selected
                variantContainer.style.display = 'none';
                unitPriceInput.value = "0.00";
                updateTotal(quantityInput);
            }
        }

        function updatePriceFromVariant(variantSelect) {
            const productRow = variantSelect.closest('.product-row');
            const unitPriceInput = productRow.querySelector('.unit-price');
            const quantityInput = productRow.querySelector('.quantity-input');

            if (variantSelect.value) {
                const selectedOption = variantSelect.options[variantSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price'));
                unitPriceInput.value = price.toFixed(2);
            } else {
                // If no variant selected, revert to product price
                const productSelect = productRow.querySelector('.product-select');
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price'));
                unitPriceInput.value = price.toFixed(2);
            }

            // Update total
            updateTotal(quantityInput);
        }

        function updateTotal(input) {
            const productRow = input.closest('.product-row');
            const quantityInput = productRow.querySelector('.quantity-input');
            const unitPriceInput = productRow.querySelector('.unit-price');
            const totalInput = productRow.querySelector('.item-total');

            const quantity = parseInt(quantityInput.value) || 0;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;
            const total = quantity * unitPrice;

            totalInput.value = total.toFixed(2);

            // Update summary
            updateSummary();
        }

        function updateSummary() {
            let subtotal = 0;
            const taxRate = 0.05; // 10% tax rate

            // Calculate subtotal from all products
            document.querySelectorAll('.item-total').forEach(input => {
                subtotal += parseFloat(input.value) || 0;
            });

            const taxAmount = subtotal * taxRate;
            const totalAmount = subtotal + taxAmount;

            // Update summary display
            document.getElementById('subtotal').textContent = '₵' + subtotal.toFixed(2);
            document.getElementById('tax').textContent = '₵' + taxAmount.toFixed(2);
            document.getElementById('total').textContent = '₵' + totalAmount.toFixed(2);
        }

        // Form validation before submission
        document.getElementById('salesForm').addEventListener('submit', function(event) {
            let isValid = false;

            // Check if at least one product is selected
            document.querySelectorAll('.product-select').forEach(select => {
                if (select.value) {
                    isValid = true;
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert('Please select at least one product before saving the sale.');
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

        // Initialize tooltips and toasts when the document is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize toast
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: 3000 // Toast will automatically hide after 3 seconds
                });
            });

            // Initialize first row
            const firstProductSelect = document.querySelector('.product-select');
            if (firstProductSelect) {
                handleProductSelection(firstProductSelect);
            }

            // Initialize summary
            updateSummary();
        });

        function updateAmountDisplays(amount) {
            document.getElementById('cashAmountDue').textContent = '₵' + amount.toFixed(2);
            document.getElementById('mobileMoneyAmountDue').textContent = '₵' + amount.toFixed(2);
            document.getElementById('posAmountDue').textContent = '₵' + amount.toFixed(2);
            document.getElementById('bankTransferAmountDue').textContent = '₵' + amount.toFixed(2);
        }

        function showPaymentModalForMethod(paymentMethod) {
            // Initialize modals if not already done
            if (!cashPaymentModal) {
                paymentMethodModal = new bootstrap.Modal(document.getElementById('paymentMethodModal'));
                cashPaymentModal = new bootstrap.Modal(document.getElementById('cashPaymentModal'));
                mobileMoneyModal = new bootstrap.Modal(document.getElementById('mobileMoneyModal'));
                posPaymentModal = new bootstrap.Modal(document.getElementById('posPaymentModal'));
                bankTransferModal = new bootstrap.Modal(document.getElementById('bankTransferModal'));
                paymentSuccessModal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
            }

            switch (paymentMethod) {
                case 'Cash':
                    cashPaymentModal.show();
                    break;
                case 'Mobile Money':
                    mobileMoneyModal.show();
                    break;
                case 'PoS':
                    posPaymentModal.show();
                    break;
                case 'Bank Transfer':
                    bankTransferModal.show();
                    break;
                default:
                    paymentMethodModal.show();
                    break;
            }
        }

        // Update your form submission logic
        document.addEventListener('DOMContentLoaded', function() {
            const salesForm = document.getElementById('salesForm');
            const saveButton = salesForm.querySelector('button[name="save_sale"]');
            const paymentMethodModal = new bootstrap.Modal(document.getElementById('paymentMethodModal'));
            const cashPaymentModal = new bootstrap.Modal(document.getElementById('cashPaymentModal'));
            const mobileMoneyModal = new bootstrap.Modal(document.getElementById('mobileMoneyModal'));
            const posPaymentModal = new bootstrap.Modal(document.getElementById('posPaymentModal'));
            const bankTransferModal = new bootstrap.Modal(document.getElementById('bankTransferModal'));
            const paymentSuccessModal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
            const continueButton = document.getElementById('continueWithPayment');
            const paymentOptions = document.querySelectorAll('.payment-option');
            const selectedPaymentInput = document.getElementById('selectedPaymentMethod');
            const pendSaleButtons = document.querySelectorAll('button#pendSale');

            // For new sales
            let newSalePaymentDetails = {
                method: 'Cash',
                amount: 0,
                additionalFields: {}
            };

            // For pending sale completion
            let pendingSalePaymentDetails = {
                method: 'Cash',
                amount: 0,
                additionalFields: {}
            };

            // Add a flag to track which flow we're in
            let isCompletingPendingSale = false;

            // Helper function to get current payment details
            function getCurrentPaymentDetails() {
                return isCompletingPendingSale ? pendingSalePaymentDetails : newSalePaymentDetails;
            }

            // Helper function to set current payment details
            function setCurrentPaymentDetails(details) {
                if (isCompletingPendingSale) {
                    pendingSalePaymentDetails = {
                        ...pendingSalePaymentDetails,
                        ...details
                    };
                } else {
                    newSalePaymentDetails = {
                        ...newSalePaymentDetails,
                        ...details
                    };
                }
            }

            // Set total amount from the page
            function setTotalAmountForNewSale() {
                const totalElement = document.getElementById('total');
                const totalText = totalElement.textContent;
                const totalAmount = parseFloat(totalText.replace(/[^\d.-]/g, ''));

                newSalePaymentDetails.amount = totalAmount;
                updateAmountDisplays(totalAmount);
            }

            // Set up payment option selection
            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selection from all options
                    paymentOptions.forEach(opt => {
                        opt.querySelector('.d-flex').classList.remove('border-primary');
                        opt.querySelector('.d-flex').classList.add('border');
                        opt.classList.remove('selected');
                    });

                    // Add selection to clicked option
                    this.querySelector('.d-flex').classList.add('border-primary');
                    this.querySelector('.d-flex').classList.remove('border');
                    this.classList.add('selected');

                    // Update hidden input with payment method
                    const paymentMethod = this.getAttribute('data-payment');
                    selectedPaymentInput.value = paymentMethod;
                    setCurrentPaymentDetails({
                        method: paymentMethod
                    });
                });
            });

            // Set Cash as default selected option
            const defaultPaymentOption = document.querySelector('[data-payment="Cash"]');
            if (defaultPaymentOption) {
                defaultPaymentOption.classList.add('selected');
                defaultPaymentOption.querySelector('.d-flex').classList.add('border-primary');
                defaultPaymentOption.querySelector('.d-flex').classList.remove('border');
                setCurrentPaymentDetails({
                    method: 'Cash'
                });
            }

            // Override original form submission
            if (saveButton) {
                saveButton.addEventListener('click', function(event) {
                    event.preventDefault();

                    // Do validation first
                    let isValid = false;
                    document.querySelectorAll('.product-select').forEach(select => {
                        if (select.value) {
                            isValid = true;
                        }
                    });

                    if (!isValid) {
                        alert('Please select at least one product before saving the sale.');
                        return;
                    }

                    // Set flag for new sale
                    isCompletingPendingSale = false;
                    window.pendingSaleId = null;

                    // Update the total amount
                    setTotalAmountForNewSale();

                    // Show payment modal instead of submitting form
                    paymentMethodModal.show();
                });
            }

            pendSaleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Close all modals
                    const modals = ['cashPaymentModal', 'mobileMoneyModal', 'posPaymentModal', 'bankTransferModal'];
                    modals.forEach(modalId => {
                        const modalElement = document.getElementById(modalId);
                        if (modalElement) {
                            const bsModal = bootstrap.Modal.getInstance(modalElement);
                            if (bsModal) {
                                bsModal.hide();
                            }
                        }
                    });

                    // Get the form
                    const salesForm = document.getElementById('salesForm');

                    // Remove any existing buttons/inputs that might affect submission type
                    const existingBtn = salesForm.querySelector('input[name="pend_sale"]');
                    if (existingBtn) {
                        existingBtn.remove();
                    }

                    // Add payment method to the form
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = 'payment_method';
                    methodInput.value = document.getElementById('selectedPaymentMethod').value;
                    salesForm.appendChild(methodInput);

                    // Create a unique reference for the pending sale
                    const uniqueId = "PND-" + Date.now().toString();
                    const referenceInput = document.createElement('input');
                    referenceInput.type = 'hidden';
                    referenceInput.name = 'payment_reference';
                    referenceInput.value = uniqueId;
                    salesForm.appendChild(referenceInput);

                    // Create the pend_sale button/input
                    const pendBtn = document.createElement('input');
                    pendBtn.type = 'hidden';
                    pendBtn.name = 'pend_sale';
                    pendBtn.value = '1';
                    salesForm.appendChild(pendBtn);

                    // Submit the form with pending status
                    salesForm.submit();
                });
            });

            // Handle cash payment
            const cashReceivedInput = document.getElementById('cashReceived');
            const cashChangeInput = document.getElementById('cashChange');
            const completeCashPaymentButton = document.getElementById('completeCashPayment');

            cashReceivedInput.addEventListener('input', function() {
                const cashReceived = parseFloat(this.value) || 0;
                const amountDue = parseFloat(document.getElementById('cashAmountDue').textContent.replace(/[^\d.-]/g, ''));
                const change = cashReceived - amountDue;

                cashChangeInput.value = change >= 0 ? change.toFixed(2) : '0.00';
                completeCashPaymentButton.disabled = cashReceived < amountDue;

                // Store in payment details for current context
                setCurrentPaymentDetails({
                    additionalFields: {
                        cashReceived: cashReceived.toFixed(2),
                        change: change >= 0 ? change.toFixed(2) : '0.00'
                    }
                });
            });

            // Continue button shows appropriate payment detail screen
            continueButton.addEventListener('click', function() {
                paymentMethodModal.hide();

                const currentDetails = getCurrentPaymentDetails();
                switch (currentDetails.method) {
                    case 'Cash':
                        cashPaymentModal.show();
                        break;
                    case 'Mobile Money':
                        mobileMoneyModal.show();
                        break;
                    case 'PoS':
                        posPaymentModal.show();
                        break;
                    case 'Bank Transfer':
                        bankTransferModal.show();
                        break;
                }
            });

            // Complete Cash Payment
            document.getElementById('completeCashPayment').addEventListener('click', function() {
                cashPaymentModal.hide();

                if (window.pendingSaleId) {
                    // Complete pending sale
                    completePendingSale();
                } else {
                    // New sale
                    showPaymentSuccess();
                }

                // showPaymentSuccess();
            });

            // Complete Mobile Money Payment
            document.getElementById('completeMobileMoneyPayment').addEventListener('click', function() {
                const provider = document.getElementById('mobileMoneyProvider').value;
                const phoneNumber = document.getElementById('mobileMoneyNumber').value;
                const reference = document.getElementById('mobileMoneyReference').value;

                if (!provider || !phoneNumber) {
                    alert('Please fill in all required fields.');
                    return;
                }

                setCurrentPaymentDetails({
                    additionalFields: {
                        provider: provider,
                        phoneNumber: phoneNumber,
                        reference: reference
                    }
                });

                mobileMoneyModal.hide();

                if (window.pendingSaleId) {
                    completePendingSale();
                } else {
                    showPaymentSuccess();
                }

                // showPaymentSuccess();
            });

            // Complete PoS Payment
            document.getElementById('completePosPayment').addEventListener('click', function() {
                const cardType = document.getElementById('cardType').value;
                const reference = document.getElementById('posReference').value;

                if (!cardType || !reference) {
                    alert('Please fill in all required fields.');
                    return;
                }

                setCurrentPaymentDetails({
                    additionalFields: {
                        cardType: cardType,
                        reference: reference
                    }
                });

                posPaymentModal.hide();

                if (window.pendingSaleId) {
                    completePendingSale();
                } else {
                    showPaymentSuccess();
                }

                // showPaymentSuccess();
            });

            // Complete Bank Transfer Payment
            document.getElementById('completeBankTransferPayment').addEventListener('click', function() {
                const bankName = document.getElementById('bankName').value;
                const reference = document.getElementById('transferReference').value;
                const accountName = document.getElementById('accountName').value;

                if (!bankName || !reference) {
                    alert('Please fill in all required fields.');
                    return;
                }

                setCurrentPaymentDetails({
                    additionalFields: {
                        bankName: bankName,
                        reference: reference,
                        accountName: accountName
                    }
                });

                bankTransferModal.hide();

                if (window.pendingSaleId) {
                    completePendingSale();
                } else {
                    showPaymentSuccess();
                }

                // showPaymentSuccess();
            });

            // New function to complete pending sales
            function completePendingSale() {
                // Set form values and submit
                document.getElementById('processSaleId').value = window.pendingSaleId;
                document.getElementById('processSaleAction').value = 'complete';
                document.getElementById('processSaleForm').submit();

                showPaymentSuccess();
                // Clear the pending sale ID
                window.pendingSaleId = null;
            }

            // Show payment success and details
            function showPaymentSuccess() {
                const currentDetails = getCurrentPaymentDetails();
                const detailsContainer = document.getElementById('paymentDetails');
                let detailsHTML = `
                    <p class="mb-2"><strong>Payment Method:</strong> ${currentDetails.method}</p>
                    <p class="mb-2"><strong>Amount:</strong> ₵${currentDetails.amount.toFixed(2)}</p>
                `;

                // Add additional fields based on payment method
                if (currentDetails.method === 'Cash') {
                    detailsHTML += `
                        <p class="mb-2"><strong>Cash Received:</strong> ₵${currentDetails.additionalFields.cashReceived}</p>
                        <p class="mb-0"><strong>Change:</strong> ₵${currentDetails.additionalFields.change}</p>
                    `;
                } else if (currentDetails.method === 'Mobile Money') {
                    detailsHTML += `
                        <p class="mb-2"><strong>Provider:</strong> ${currentDetails.additionalFields.provider}</p>
                        <p class="mb-2"><strong>Phone Number:</strong> ${currentDetails.additionalFields.phoneNumber}</p>
                        <p class="mb-0"><strong>Reference:</strong> ${currentDetails.additionalFields.reference || 'N/A'}</p>
                    `;
                } else if (currentDetails.method === 'PoS') {
                    detailsHTML += `
                        <p class="mb-2"><strong>Card Type:</strong> ${currentDetails.additionalFields.cardType}</p>
                        <p class="mb-0"><strong>Reference:</strong> ${currentDetails.additionalFields.reference}</p>
                    `;
                } else if (currentDetails.method === 'Bank Transfer') {
                    detailsHTML += `
                        <p class="mb-2"><strong>Bank:</strong> ${currentDetails.additionalFields.bankName}</p>
                        <p class="mb-2"><strong>Reference:</strong> ${currentDetails.additionalFields.reference}</p>
                        <p class="mb-0"><strong>Account Name:</strong> ${currentDetails.additionalFields.accountName || 'N/A'}</p>
                    `;
                }

                detailsContainer.innerHTML = detailsHTML;
                paymentSuccessModal.show();
            }

            // Finish payment process handler
            document.getElementById('finishPaymentProcess').addEventListener('click', function() {
                paymentSuccessModal.hide();

                // Remove any existing payment method inputs first
                const existingMethod = salesForm.querySelector('input[name="payment_method"]');
                if (existingMethod) {
                    existingMethod.remove();
                }

                const existingReference = salesForm.querySelector('input[name="payment_reference"]');
                if (existingReference) {
                    existingReference.remove();
                }

                // Add payment method to the form
                const methodInput = document.createElement('input');
                const currentDetails = getCurrentPaymentDetails();
                methodInput.type = 'hidden';
                methodInput.name = 'payment_method';
                methodInput.value = currentDetails.method;
                salesForm.appendChild(methodInput);

                // Generate a unique transaction ID
                const uniqueId = Date.now().toString() + Math.floor(Math.random() * 1000);

                // Create payment reference from relevant details with unique ID
                let paymentReference = "TXN-" + uniqueId + ": ";

                /* if (paymentDetails.method === 'Cash') {
                    paymentReference = `Cash payment: Received ₵${paymentDetails.additionalFields.cashReceived}, Change ₵${paymentDetails.additionalFields.change}`;
                } else if (paymentDetails.method === 'Mobile Money') {
                    paymentReference = `${paymentDetails.additionalFields.provider}: ${paymentDetails.additionalFields.phoneNumber}, Ref: ${paymentDetails.additionalFields.reference || 'N/A'}`;
                } else if (paymentDetails.method === 'PoS') {
                    paymentReference = `${paymentDetails.additionalFields.cardType}, Ref: ${paymentDetails.additionalFields.reference}`;
                } else if (paymentDetails.method === 'Bank Transfer') {
                    paymentReference = `${paymentDetails.additionalFields.bankName}, Ref: ${paymentDetails.additionalFields.reference}`;
                } */

                // Add payment reference to the form
                const referenceInput = document.createElement('input');
                referenceInput.type = 'hidden';
                referenceInput.name = 'payment_reference';
                referenceInput.value = paymentReference;
                salesForm.appendChild(referenceInput);

                // Ensure hidden submit button is created and submit form programmatically
                const submitBtn = document.createElement('input');
                submitBtn.type = 'hidden';
                submitBtn.name = 'save_sale';
                submitBtn.value = '1';
                salesForm.appendChild(submitBtn);

                // Log form data before submission (for debugging)
                console.log("Submitting with payment method:", currentDetails.method);
                console.log("Payment reference:", paymentReference);

                // Submit the form
                salesForm.submit();
            });
        });

        // Handle View Pending button click
        const viewPendingButton = document.querySelector('.btn-outline-secondary');
        if (viewPendingButton && viewPendingButton.textContent.includes('View Pending')) {
            viewPendingButton.addEventListener('click', function() {
                const pendingSalesModal = new bootstrap.Modal(document.getElementById('pendingSalesModal'));
                pendingSalesModal.show();
            });
        }

        // Handle view details buttons in pending sales modal
        document.addEventListener('click', function(event) {
            if (event.target.closest('.view-details-btn')) {
                const button = event.target.closest('.view-details-btn');
                const saleId = button.getAttribute('data-sale-id');

                // Fetch sale details via AJAX
                fetch(`get_sale_details.php?id=${saleId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const sale = data.sale;

                            let detailsHTML = `
                                    <div class="mb-3">
                                        <h6>Transaction: ${sale.transaction_number}</h6>
                                        <p class="text-muted mb-2">${sale.formatted_date}</p>
                                        <p><strong>Payment Method:</strong> ${sale.payment_method}</p>
                                        <p><strong>Status:</strong> <span class="status-badge status-${sale.status.toLowerCase()}">${sale.status}</span></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6>Items:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Category</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                `;

                            sale.items.forEach(item => {
                                detailsHTML += `
                                                    <tr>
                                                        <td>${item.product_name}</td>
                                                        <td>${item.category || 'N/A'}</td>
                                                        <td>${item.quantity}</td>
                                                        <td>₵${parseFloat(item.unit_price).toFixed(2)}</td>
                                                    </tr>
                                `;
                            });

                            detailsHTML += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-4">
                                            <p><strong>Subtotal:</strong> ₵${parseFloat(sale.amount).toFixed(2)}</p>
                                        </div>
                                        <div class="col-4">
                                            <p><strong>Tax:</strong> ₵${parseFloat(sale.tax_amount).toFixed(2)}</p>
                                        </div>
                                        <div class="col-4">
                                            <p><strong>Total:</strong> ₵${parseFloat(sale.total_amount).toFixed(2)}</p>
                                        </div>
                                    </div>
                            `;

                            document.getElementById('saleDetailsContent').innerHTML = detailsHTML;
                            const saleDetailsModal = new bootstrap.Modal(document.getElementById('saleDetailsModal'));
                            saleDetailsModal.show();
                        } else {
                            alert('Error loading sale details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading sale details');
                    });
            }
        });

        // Handle complete sale buttons - Updated version
        document.addEventListener('click', function(event) {
            if (event.target.closest('.complete-sale-btn')) {
                const button = event.target.closest('.complete-sale-btn');
                const saleId = button.getAttribute('data-sale-id');

                // Set the flag for pending sale completion
                isCompletingPendingSale = true;
                window.pendingSaleId = saleId;

                // Find the sale data from the pending sales
                const saleRow = button.closest('tr');
                const paymentMethodCell = saleRow.cells[4];
                const amountCell = saleRow.cells[3];

                const paymentMethod = paymentMethodCell.textContent.trim();
                const totalAmount = parseFloat(amountCell.textContent.replace(/[^\d.-]/g, ''));

                // Set pending sale payment details
                pendingSalePaymentDetails = {
                    method: paymentMethod,
                    amount: totalAmount,
                    additionalFields: {}
                };

                // Reset payment forms
                resetPaymentForms();

                // Update amount displays
                updateAmountDisplays(totalAmount);

                // Close pending sales modal first
                const pendingSalesModal = bootstrap.Modal.getInstance(document.getElementById('pendingSalesModal'));
                if (pendingSalesModal) {
                    pendingSalesModal.hide();
                }

                // Show appropriate payment modal
                showPaymentModalForMethod(paymentMethod);
            }
        });

        // Handle cancel sale buttons
        document.addEventListener('click', function(event) {
            if (event.target.closest('.cancel-sale-btn')) {
                const button = event.target.closest('.cancel-sale-btn');
                const saleId = button.getAttribute('data-sale-id');

                if (confirm('Are you sure you want to cancel this sale?')) {
                    // Set form values and submit
                    document.getElementById('processSaleId').value = saleId;
                    document.getElementById('processSaleAction').value = 'cancel';
                    document.getElementById('processSaleForm').submit();
                }
            }
        });

        // Function to reset payment form fields
        function resetPaymentForms() {
            // Reset cash form
            document.getElementById('cashReceived').value = '';
            document.getElementById('cashChange').value = '0.00';
            document.getElementById('completeCashPayment').disabled = true;

            // Reset mobile money form
            document.getElementById('mobileMoneyProvider').value = '';
            document.getElementById('mobileMoneyNumber').value = '';
            document.getElementById('mobileMoneyReference').value = '';

            // Reset PoS form
            document.getElementById('cardType').value = '';
            document.getElementById('posReference').value = '';

            // Reset bank transfer form
            document.getElementById('bankName').value = '';
            document.getElementById('transferReference').value = '';
            document.getElementById('accountName').value = '';
        }
    </script>
</body>

</html>