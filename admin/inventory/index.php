<?php
session_start();

include "../../config/config.php";
include "../../config/session_check.php";
include "../../config/notification_functions.php";

// Get the current page from the URL, defaulting to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of rows per page
$offset = ($page - 1) * $limit;

$total_qry = "SELECT COUNT(*) as total FROM products";
$total_res = mysqli_query($connect, $total_qry);
$total_row = mysqli_fetch_assoc($total_res);
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

$select_qry = "SELECT 
                p.id AS product_id, 
                p.product_name, 
                p.category,
                p.batch_number,
                p.brand, 
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
            ORDER BY p.id, v.id
            LIMIT $offset, $limit";

$row = mysqli_query($connect, $select_qry);

$products = [];
if (mysqli_num_rows($row) > 0) {
    while ($rows = mysqli_fetch_assoc($row)) {
        error_log("Variant row data: " . print_r($rows, true));
        /* echo '<pre>';
        print_r($rows);
        echo '</pre>'; */
        // $trow = $row;

        $products[$rows['product_id']]['id'] = $rows['product_id'];
        $products[$rows['product_id']]['name'] = $rows['product_name'];
        $products[$rows['product_id']]['category'] = $rows['category'];
        $products[$rows['product_id']]['batch_number'] = $rows['batch_number'];
        $products[$rows['product_id']]['brand'] = $rows['brand'];
        $products[$rows['product_id']]['stock'] = $rows['product_stock'];
        $products[$rows['product_id']]['priceSachet'] = $rows['price_per_sachet'];
        $products[$rows['product_id']]['pricePacket'] = $rows['price_per_packet'];
        $products[$rows['product_id']]['lowStock'] = $rows['low_stock_alert'];
        $products[$rows['product_id']]['lastUpdated'] = $rows['created_at'];

        if ($rows['variant_id']) {
            $products[$rows['product_id']]['variants'][] = [
                'variant_id' => $rows['variant_id'],
                'variant_name' => $rows['variant_name'],
                'qty_packet' => $rows['qty_packet'],
                'qty_sachet' => $rows['qty_sachet'],
                'packPrice' => $rows['packPrice'],
                'unitPrice' => $rows['unitPrice']
            ];
        }
    }
}

if (isset($_POST['addCat'])) {
    $category  = isset($_POST['category']) ? trim($_POST['category']) : '';
    $desc      = isset($_POST['cat_desc']) ? trim($_POST['cat_desc']) : '';

    /* if (empty($_POST['category'])) {
        $login_error = "Category is empty";
    } elseif (empty($_POST['cat_desc'])) {y";
    } else {
        die('okay');
    } */

    $sel_qry = "SELECT * FROM category WHERE category = '$category'";
    $result = mysqli_query($connect, $sel_qry);
    if (mysqli_num_rows($result) > 0) {
        echo '<div id="alertBox" class="alert alert-error">
                <span class="closebtn" onclick="this.parentElement.style.display=\'none\';">&times;</span> 
                <strong>Error!</strong> Category alredy exists!.
            </div>';
    } else {
        $cat_sql = "INSERT INTO category (`cat_id`, `category`, `description`)
                VALUES ('', '$category', '$desc')";
        $res = mysqli_query($connect, $cat_sql);
        if ($res) {
            // echo "<script> alert('Category has been created successfully.'); </script>";
            echo '<div id="alertBox" class="alert alert-success">
                <span class="closebtn" onclick="this.parentElement.style.display=\'none\';">&times;</span> 
                <strong>Success!</strong> Category created successfully!.
            </div>';
        } else {
            echo "Error: " . mysqli_error($connect);
        }
    }
}

$sel_qry = "SELECT * FROM category";
$res = mysqli_query($connect, $sel_qry);

if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $cat[] = $row;
    }
}

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
    <title>Inventory Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="../../style/css/style.css" rel="stylesheet">

    <?php echo getNotificationDropdownCSS(); ?>
    <style>
        .alert {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }

        .alert-success {
            background-color: #04AA6D;
            color: white;
        }

        .alert-error {
            background-color: #f44336;
            color: white;
        }

        .closebtn {
            margin-left: 15px;
            color: white;
            font-weight: bold;
            float: right;
            font-size: 22px;
            line-height: 20px;
            cursor: pointer;
            transition: 0.3s;
        }

        .closebtn:hover {
            color: black;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 1.5rem 0 2rem 0;
            padding: 0;
            gap: 0.25rem;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.5rem;
            height: 2.5rem;
            padding: 0.5rem 0.75rem;
            margin: 0 0.1rem;
            text-decoration: none;
            border-radius: 0.25rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .pagination a {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .pagination a:hover {
            background-color: #e9ecef;
            color: #0d6efd;
            border-color: #0d6efd;
            z-index: 1;
        }

        .pagination span {
            background-color: #0d6efd;
            color: white;
            border: 1px solid #0d6efd;
        }

        /* For previous and next buttons */
        .pagination a.prev,
        .pagination a.next {
            padding: 0.5rem 1rem;
        }

        /* For mobile responsiveness */
        @media (max-width: 576px) {
            .pagination {
                flex-wrap: wrap;
            }

            .pagination a,
            .pagination span {
                min-width: 2rem;
                height: 2rem;
                padding: 0.25rem 0.5rem;
                margin-bottom: 0.5rem;
            }
        }

        .variant-row td {
            padding-left: 2rem;
            background-color: #f8f9fa;
        }

        .expand-btn {
            cursor: pointer;
            width: 24px;
            display: inline-block;
            text-align: center;
        }

        .variant-table {
            width: 100%;
            margin-bottom: 0;
        }

        .variant-table td,
        .variant-table th {
            padding: 0.5rem;
        }

        #variant_edit_notice {
            display: none;
            margin-top: 10px;
            margin-bottom: 15px;
            padding: 10px 12px;
            border-radius: 6px;
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
            color: #084298;
            font-size: 0.9rem;
            position: relative;
            clear: both;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }

        #variant_edit_notice:before {
            content: '\f05a';
            /* Font Awesome info icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
            color: #0d6efd;
        }

        #variant_edit_notice strong {
            font-weight: 600;
        }
    </style>
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
                <a href="../dashboard/" class="nav-link" onclick="toggleSubmenu('dashboard')">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <!-- Inventory Management -->
                <a href="#" class="nav-link active" onclick="toggleSubmenu('inventory')">
                    <i class="fas fa-box"></i> Inventory Management
                </a>
                <div class="submenu show" id="inventory">
                    <a href="#" class="nav-link active"><i class="fas fa-list"></i> View Inventory</a>
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
            <!-- Header Section -->
            <div class="header">
                <div class="search-box">
                    <i class="fas fa-search text-muted me-2"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="user-section">
                    <?php echo generateNotificationDropdown($user_id, $connect); ?>
                    <div class="user-info ms-3">
                        <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['firstname']); ?></span>
                        <small class="d-block text-muted"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></small>
                    </div>
                </div>
            </div>

            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Inventory Management</h3>
                        <p class="mb-3">Manage and track your inventory items</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light rounded-pill px-4 mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-2"></i>Add Category
                        </button>
                    </div>

                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Search products..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php
                            foreach ($cat as $row) {
                                echo "<option value='{$row["category"]}'>{$row["category"]}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="in-stock">In Stock</option>
                            <option value="low-stock">Low Stock</option>
                            <option value="out-of-stock">Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="applyFilters()">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="inventory-table">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="40"></th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Batch Number</th>
                                <th>Brand</th>
                                <th>Stock</th>
                                <th>Unit Price</th>
                                <th>Pack Price</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryTableBody">
                            <?php
                            foreach ($products as $product_id => $product) {
                                // print_r($row);die();
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['variants'])): ?>
                                            <span class="expand-btn" onclick="toggleVariants(<?php echo $product_id; ?>)" id="expand-<?php echo $product_id; ?>">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['category']; ?></td>
                                    <td><?php echo $product['batch_number']; ?></td>
                                    <td><?php echo $product['brand']; ?></td>
                                    <td>
                                        <?php
                                        if (!empty($product['variants'])) {
                                            $total_stock = 0;
                                            foreach ($product['variants'] as $variant) {
                                                $total_stock += $variant['qty_packet'];
                                            }
                                            echo $total_stock;
                                        } else {
                                            echo $product['stock'];
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $product['priceSachet']; ?></td>
                                    <td><?php echo $product['pricePacket']; ?></td>
                                    <td>
                                        <?php
                                        if ($product['stock'] <= $product['lowStock']) {
                                            echo '<span class="status-badge status-low-stock">Low Stock</span>';
                                        } elseif ($product['stock'] == 0 || $product['stock'] == 1) {
                                            echo '<span class="status-badge status-out-of-stock">Out of Stock</span>';
                                        } else {
                                            echo '<span class="status-badge status-in-stock">In Stock</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $product['lastUpdated']; ?></td>
                                    <td>
                                        <button class="action-btn" onclick="editProduct(<?php echo $product_id; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn" onclick="openAddVariantModal(<?php echo $product_id; ?>, '<?php echo addslashes($product['name']); ?>')">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <button class="action-btn" onclick="confirmDeleteProduct(<?php echo $product_id; ?>, '<?php echo addslashes($product['name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <?php if (!empty($product['variants'])) { ?>
                                    <tr id="variants-<?php echo $product_id; ?>" class="variant-row" style="display: none;">
                                        <td colspan="9">
                                            <table class="variant-table">
                                                <thead>
                                                    <tr>
                                                        <th>Variant</th>
                                                        <th>QTY(packs)</th>
                                                        <th>QTY(Sachet)</th>
                                                        <th>Price Packet</th>
                                                        <th>Price Sachet</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($product['variants'] as $variant): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($variant['variant_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($variant['qty_packet']); ?></td>
                                                            <td><?php echo htmlspecialchars($variant['qty_sachet']); ?></td>
                                                            <td><?php echo htmlspecialchars($variant['packPrice']); ?></td>
                                                            <td><?php echo htmlspecialchars($variant['unitPrice']); ?></td>
                                                            <td>
                                                                <button class="action-btn" onclick="editVariant(<?php echo $variant['variant_id']; ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="action-btn" onclick="confirmDeleteVariant(<?php echo $variant['variant_id']; ?>, '<?php echo addslashes($variant['variant_name']); ?>')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    echo "<div class='pagination'>";
                    if ($page > 1) {
                        echo "<a href='?page=" . ($page - 1) . "'>&laquo; Prev</a>";
                    }

                    // Calculate range for pagination
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    // Show first page and ellipsis if needed
                    if ($start_page > 1) {
                        echo "<a href='?page=1'>1</a>";
                        if ($start_page > 2) {
                            echo "<span style='background-color: transparent; border: none; color: #495057;'>...</span>";
                        }
                    }

                    // Show the page range
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        if ($i == $page) {
                            echo "<span>$i</span>";
                        } else {
                            echo "<a href='?page=$i'>$i</a>";
                        }
                    }

                    // Show last page and ellipsis if needed
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo "<span style='background-color: transparent; border: none; color: #495057;'>...</span>";
                        }
                        echo "<a href='?page=$total_pages'>$total_pages</a>";
                    }

                    if ($page < $total_pages) {
                        echo "<a href='?page=" . ($page + 1) . "'>Next &raquo;</a>";
                    }
                    echo "</div>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form class="modal-body" method="post">
                    <?php if (isset($login_error)) { ?>
                        <div class="alert alert-danger border border-danger text-danger text-center px-4 py-3 rounded mb-4" role="alert">
                            <?php echo htmlspecialchars($login_error); ?>
                        </div>
                    <?php } ?>
                    <div id="addCategoryForm">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="category" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="cat_desc" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input type="submit" class="btn btn-primary" name="addCat" value="Add Category">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Variant Modal -->
    <div class="modal fade" id="addVariantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Variant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addVariantForm">
                        <input type="hidden" id="add_variant_product_id" name="product_id">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="add_variant_product_name" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Variant Name</label>
                            <input type="text" class="form-control" id="add_variant_name" name="variant_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity (Packets)</label>
                            <input type="number" class="form-control" id="add_variant_qty_packet" name="qty_packet" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity (Sachets)</label>
                            <input type="number" class="form-control" id="add_variant_qty_sachet" name="qty_sachet" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price Per Packet</label>
                            <input type="number" class="form-control" id="add_variant_price_packet" name="price_per_packet" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price Per Sachet</label>
                            <input type="number" class="form-control" id="add_variant_price_sachet" name="price_per_sachet" step="0.01" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNewVariant">Add Variant</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm" method="post" action="update_product.php">
                        <input type="hidden" id="edit_product_id" name="product_id" action="update_product.php">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="edit_category" name="category" required>
                                <?php
                                foreach ($cat as $row) {
                                    echo "<option value='{$row["category"]}'>{$row["category"]}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" id="edit_brand" name="brand">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Batch Number</label>
                            <input type="text" class="form-control" id="edit_batch_number" name="batch_number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock (Packets)</label>
                            <input type="number" class="form-control" id="edit_stock" name="quantity_packet" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit Price (Per Sachet)</label>
                            <input type="number" class="form-control" id="edit_price_sachet" name="price_per_sachet" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pack Price (Per Packet)</label>
                            <input type="number" class="form-control" id="edit_price_packet" name="price_per_packet" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Low Stock Alert</label>
                            <input type="number" class="form-control" id="edit_low_stock" name="low_stock_alert" required>
                        </div>

                        <div class="mb-3" id="variant_edit_notice_container">
                            <div id="variant_edit_notice">
                                <span><strong>Notice:</strong> <br />This product has variants. Stock and pricing should be managed through individual variants. <br /> Stock is the sum of total stock left in packet and sachet of all variants.</span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveProductChanges">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Variant Modal -->
    <div class="modal fade" id="editVariantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Variant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editVariantForm">
                        <input type="hidden" id="edit_variant_id" name="variant_id">
                        <input type="hidden" id="edit_variant_product_id" name="product_id">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="edit_variant_product_name" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Variant Name</label>
                            <input type="text" class="form-control" id="edit_variant_name" name="variant_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity (Packets)</label>
                            <input type="number" class="form-control" id="edit_variant_qty_packet" name="qty_packet" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity (Sachets)</label>
                            <input type="number" class="form-control" id="edit_variant_qty_sachet" name="qty_sachet" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price Per Packet</label>
                            <input type="number" class="form-control" id="edit_variant_price_packet" name="price_per_packet" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price Per Sachet</label>
                            <input type="number" class="form-control" id="edit_variant_price_sachet" name="price_per_sachet" step="0.01" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveVariantChanges">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteConfirmMessage">Are you sure you want to delete this item?</p>
                    <input type="hidden" id="deleteItemId">
                    <input type="hidden" id="deleteItemType">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../../style/js/search.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
    <script>
        // Add category function
        function addCategory() {
            const categoryName = document.getElementById('categoryName').value;
            const categoryDescription = document.getElementById('categoryDescription').value;
            const categoryStatus = document.getElementById('categoryStatus').value;

            const newCategory = {
                id: categoriesData.length + 1,
                name: categoryName,
                description: categoryDescription,
                totalProducts: 0,
                status: categoryStatus,
                lastUpdated: new Date().toISOString().split('T')[0]
            };

            categoriesData.push(newCategory);
            renderCategoriesTable(categoriesData);

            // Close modal
            document.querySelector('#addCategoryModal').querySelector('.btn-close').click();
        }

        // Sidebar toggle function
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

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

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

        setTimeout(function() {
            var alertBox = document.getElementById("alertBox");
            if (alertBox) {
                alertBox.style.opacity = "0"; // Smooth fade out
                setTimeout(function() {
                    alertBox.style.display = "none"; // Completely hide it
                }, 500); // Give time for fade effect before hiding
            }
        }, 3000); // Auto-close after 3 seconds

        // Add this function to your JavaScript or modify existing one
        function toggleVariants(productId) {
            const variantContainer = document.getElementById(`variants-${productId}`);
            const expandBtn = document.getElementById(`expand-${productId}`);

            if (variantContainer.style.display === 'none') {
                variantContainer.style.display = 'table-row-group'; // Use table-row-group for tbody
                expandBtn.innerHTML = '<i class="fas fa-minus"></i>';

                // Store expanded state in session storage to maintain it across pagination
        sessionStorage.setItem(`expanded-${productId}`, 'true');
            } else {
                variantContainer.style.display = 'none';
                expandBtn.innerHTML = '<i class="fas fa-plus"></i>';

                // Remove expanded state from session storage
        sessionStorage.removeItem(`expanded-${productId}`);
            }
        }

        function editProduct(productId) {
            // Make a direct fetch call to get_product.php
            fetch(`get_product.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Product data:", data);

                    if (data.success) {
                        const product = data.product;

                        // Check if the product has variants
                        const hasVariants = document.getElementById(`variants-${productId}`) !== null;
                        console.log("Has variants:", hasVariants);

                        // Populate the form fields
                        document.getElementById('edit_product_id').value = product.id;
                        document.getElementById('edit_product_name').value = product.product_name;

                        // Set the category dropdown value
                        const categorySelect = document.getElementById('edit_category');
                        for (let i = 0; i < categorySelect.options.length; i++) {
                            if (categorySelect.options[i].value === product.category) {
                                categorySelect.selectedIndex = i;
                                break;
                            }
                        }

                        // Set brand and batch number
                        document.getElementById('edit_brand').value = product.brand || '';
                        document.getElementById('edit_batch_number').value = product.batch_number || '';

                        // Get references to stock and pricing fields
                        const stockField = document.getElementById('edit_stock');
                        const priceSachetField = document.getElementById('edit_price_sachet');
                        const pricePacketField = document.getElementById('edit_price_packet');
                        const stockFieldContainer = stockField.closest('.mb-3');
                        const priceSachetFieldContainer = priceSachetField.closest('.mb-3');
                        const pricePacketFieldContainer = pricePacketField.closest('.mb-3');

                        // Show or hide notification about variants
                        const variantNotice = document.getElementById('variant_edit_notice');

                        if (hasVariants) {
                            // Show the variant notice
                            variantNotice.style.display = 'block';

                            // Disable stock and price fields
                            stockField.disabled = true;
                            priceSachetField.disabled = true;
                            pricePacketField.disabled = true;

                            // Calculate total stock from variants - same as shown in the product table
                            let totalStock = 0;

                            // Debug the variants data
                            console.log("Variants data:", product.variants);

                            if (product.variants && product.variants.length > 0) {
                                product.variants.forEach(variant => {
                                    console.log("Processing variant:", variant);
                                    const qty = parseInt(variant.qty_packet || 0);
                                    console.log("Variant quantity:", qty);
                                    totalStock += qty;
                                });
                            }

                            console.log("Calculated total stock:", totalStock);

                            // If we couldn't calculate from variants, fall back to the product's stock
                            if (totalStock === 0) {
                                totalStock = parseInt(product.quantity_packet || 0);
                                console.log("Using product's own stock value:", totalStock);
                            }

                            // Set the stock field to show the calculated total stock
                            stockField.value = totalStock;

                            // Add visual indication
                            stockFieldContainer.classList.add('opacity-50');
                            priceSachetFieldContainer.classList.add('opacity-50');
                            pricePacketFieldContainer.classList.add('opacity-50');
                        } else {
                            // Hide the variant notice
                            variantNotice.style.display = 'none';

                            // Enable the fields
                            stockField.disabled = false;
                            priceSachetField.disabled = false;
                            pricePacketField.disabled = false;

                            // Remove visual indication
                            stockFieldContainer.classList.remove('opacity-50');
                            priceSachetFieldContainer.classList.remove('opacity-50');
                            pricePacketFieldContainer.classList.remove('opacity-50');

                            // Use the product's own stock value
                            stockField.value = product.quantity_packet;
                        }

                        // Always populate the fields (even if disabled)
                        // stockField.value = product.quantity_packet;
                        priceSachetField.value = product.price_per_sachet;
                        pricePacketField.value = product.price_per_packet;
                        document.getElementById('edit_low_stock').value = product.low_stock_alert;

                        // Show the modal
                        const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
                        editModal.show();
                    } else {
                        showAlert('Error fetching product details: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while fetching product details.', 'error');
                });
        }

        // Event listener for saving product changes
        document.getElementById('saveProductChanges').addEventListener('click', function() {
            const form = document.getElementById('editProductForm');
            const formData = new FormData(form);

            // Get the product ID to check if it has variants
            const productId = document.getElementById('edit_product_id').value;
            const hasVariants = document.getElementById(`variants-${productId}`) !== null;

            // If the product has variants, we need to handle the disabled fields differently
            if (hasVariants) {
                // Remove the disabled attribute temporarily so the values are included in the form submission
                // This ensures we don't lose the original values when updating only basic info
                document.getElementById('edit_stock').disabled = false;
                document.getElementById('edit_price_sachet').disabled = false;
                document.getElementById('edit_price_packet').disabled = false;
            }

            // Debug: Log the form data being sent
            console.log("Sending form data:");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            fetch('update_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log("Response status:", response.status);
                    return response.json();
                })
                .then(data => {
                    console.log("Response data:", data);
                    if (data.success) {
                        // Show success message
                        showAlert('Product updated successfully!', 'success');

                        // Close the modal
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'));
                        editModal.hide();

                        // Refresh the product list
                        window.location.reload();
                    } else {
                        showAlert('Error updating product: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while updating the product.', 'error');
                });
        });

        // Function to edit a variant
        function editVariant(variantId) {
            console.log("Editing variant with ID:", variantId);

            // Make a direct fetch call to get_product.php
            fetch(`get_product.php?variant_id=${variantId}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Variant data:", data);

                    if (data.success) {
                        const variant = data.variant;

                        // Populate the form fields
                        document.getElementById('edit_variant_id').value = variant.id;
                        document.getElementById('edit_variant_product_id').value = variant.product_id;

                        // Fetch the product name
                        fetch(`get_product.php?product_id=${variant.product_id}`)
                            .then(response => response.json())
                            .then(productData => {
                                if (productData.success) {
                                    document.getElementById('edit_variant_product_name').value = productData.product.product_name;
                                } else {
                                    document.getElementById('edit_variant_product_name').value = "Product ID: " + variant.product_id;
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching product:', error);
                                document.getElementById('edit_variant_product_name').value = "Product ID: " + variant.product_id;
                            });

                        document.getElementById('edit_variant_name').value = variant.variant_name;
                        document.getElementById('edit_variant_qty_packet').value = variant.qty_packet;
                        document.getElementById('edit_variant_qty_sachet').value = variant.qty_sachet;
                        document.getElementById('edit_variant_price_packet').value = variant.price_per_packet;
                        document.getElementById('edit_variant_price_sachet').value = variant.price_per_sachet;

                        // Show the modal
                        const editModal = new bootstrap.Modal(document.getElementById('editVariantModal'));
                        editModal.show();
                    } else {
                        showAlert('Error fetching variant details: ' + data.message, 'error');
                        console.error('Variant not found with ID:', variantId);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while fetching variant details.', 'error');
                });
        }

        function openUpdateVariantModal(variantId) {
            editVariant(variantId);
        }

        // Event listener for saving variant changes
        document.getElementById('saveVariantChanges').addEventListener('click', function() {
            const form = document.getElementById('editVariantForm');
            const formData = new FormData(form);

            // Debug: Log the form data being sent
            console.log("Sending variant form data:");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            fetch('update_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Response data:", data);
                    if (data.success) {
                        // Show success message
                        showAlert('Variant updated successfully!', 'success');

                        // Close the modal
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editVariantModal'));
                        editModal.hide();

                        // Refresh the product list
                        window.location.reload();
                    } else {
                        showAlert('Error updating variant: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while updating the variant.', 'error');
                });
        });

        // Function to open the Add Variant modal
        function openAddVariantModal(productId, productName) {
            // Set the product ID and name in the form
            document.getElementById('add_variant_product_id').value = productId;
            document.getElementById('add_variant_product_name').value = productName;

            // Clear other form fields
            document.getElementById('add_variant_name').value = '';
            document.getElementById('add_variant_qty_packet').value = '';
            document.getElementById('add_variant_qty_sachet').value = '';
            document.getElementById('add_variant_price_packet').value = '';
            document.getElementById('add_variant_price_sachet').value = '';

            // Show the modal
            const addModal = new bootstrap.Modal(document.getElementById('addVariantModal'));
            addModal.show();
        }

        // Event listener for saving new variant
        document.getElementById('saveNewVariant').addEventListener('click', function() {
            const form = document.getElementById('addVariantForm');
            const formData = new FormData(form);

            // Add a flag to indicate this is a new variant
            formData.append('action', 'add_variant');

            // Debug: Log the form data being sent
            console.log("Sending new variant form data:");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            fetch('update_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Response data:", data);
                    if (data.success) {
                        // Show success message
                        showAlert('Variant added successfully!', 'success');

                        // Close the modal
                        const addModal = bootstrap.Modal.getInstance(document.getElementById('addVariantModal'));
                        addModal.hide();

                        // Refresh the product list
                        window.location.reload();
                    } else {
                        showAlert('Error adding variant: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while adding the variant.', 'error');
                });
        });

        // Function to confirm deletion of a product
        function confirmDeleteProduct(productId, productName) {
            // Check if the product has variants by looking for the variants row
            const variantsRow = document.getElementById(`variants-${productId}`);
            const hasVariants = variantsRow !== null;

            if (hasVariants) {
                document.getElementById('deleteConfirmMessage').innerHTML =
                    `<strong>Warning:</strong> This process will delete the product "${productName}" and all its variants. 
             This action is not recoverable. Do you wish to continue?`;
            } else {
                document.getElementById('deleteConfirmMessage').innerHTML =
                    `Are you sure you want to delete the product "${productName}"?<br /><strong>Warning:</strong> This action is not recoverable.`;
            }

            document.getElementById('deleteItemId').value = productId;
            document.getElementById('deleteItemType').value = 'product';

            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        }

        // Event listener for the delete confirmation button
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const itemId = document.getElementById('deleteItemId').value;
            const itemType = document.getElementById('deleteItemType').value;

            // Create form data for the delete request
            const formData = new FormData();
            formData.append('action', 'delete_item');
            formData.append('item_type', itemType);
            formData.append('item_id', itemId);

            // Send delete request to the server
            fetch('update_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showAlert(`${itemType.charAt(0).toUpperCase() + itemType.slice(1)} deleted successfully!`, 'success');

                        // Close the modal
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                        deleteModal.hide();

                        // Refresh the page to update the product list
                        window.location.reload();
                    } else {
                        showAlert(`Error deleting ${itemType}: ${data.message}`, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert(`An error occurred while deleting the ${itemType}.`, 'error');
                });
        });

        // Function to display alert messages
        function showAlert(message, type) {
            const alertBox = document.createElement('div');
            alertBox.id = 'alertBox';
            alertBox.className = `alert alert-${type === 'success' ? 'success' : 'error'}`;
            alertBox.innerHTML = `
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
    `;

            document.body.appendChild(alertBox);

            // Auto-close after 3 seconds
            setTimeout(function() {
                alertBox.style.opacity = "0";
                setTimeout(function() {
                    if (alertBox.parentNode) {
                        alertBox.parentNode.removeChild(alertBox);
                    }
                }, 500);
            }, 3000);
        }
    </script>
</body>

</html>