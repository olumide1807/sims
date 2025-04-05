<?php
session_start();

include "../config/config.php";
include "../config/session_check.php";

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
                p.quantity_packet AS product_stock,
                p.price_per_sachet,
                p.price_per_packet,
                p.low_stock_alert,
                p.created_at, 
                v.id AS variant_id, 
                v.variant_name, 
                v.quantity_packet AS variant_stock
            FROM products p
            LEFT JOIN product_variants v ON p.id = v.product_id
            ORDER BY p.id, v.id
            LIMIT $offset, $limit";

$row = mysqli_query($connect, $select_qry);

$products = [];
if (mysqli_num_rows($row) > 0) {
    while ($rows = mysqli_fetch_assoc($row)) {
        /* echo '<pre>';
        print_r($rows);
        echo '</pre>'; */
        // $trow = $row;

        $products[$rows['product_id']]['name'] = $rows['product_name'];
        $products[$rows['product_id']]['category'] = $rows['category'];
        $products[$rows['product_id']]['stock'] = $rows['product_stock'];
        $products[$rows['product_id']]['priceSachet'] = $rows['price_per_sachet'];
        $products[$rows['product_id']]['pricePacket'] = $rows['price_per_packet'];
        $products[$rows['product_id']]['lowStock'] = $rows['low_stock_alert'];
        $products[$rows['product_id']]['lastUpdated'] = $rows['created_at'];

        if ($rows['variant_id']) {
            $products[$rows['product_id']]['variants'][] = [
                'variant_name' => $rows['variant_name'],
                'stock' => $rows['variant_stock']
            ];
        }
    }
    // die();
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="../style/css/style.css" rel="stylesheet">

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
                    <a href="../inventory/updateproduct.php" class="nav-link"><i class="fas fa-edit"></i> Update Inventory</a>
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
                <a href="#" class="nav-link" onclick="toggleSubmenu('ai-insights')">
                    <i class="fas fa-robot"></i> AI-Powered Insights
                </a>
                <div class="submenu" id="ai-insights">
                    <a href="#" class="nav-link"><i class="fas fa-bell"></i> Reordering Suggestions</a>
                    <a href="#" class="nav-link"><i class="fas fa-clock"></i> Expiration Alerts</a>
                    <a href="#" class="nav-link"><i class="fas fa-chart-line"></i> Low-Demand Products</a>
                </div>

                <!-- Reports -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('reports')">
                    <i class="fas fa-file-alt"></i> Reports
                </a>
                <div class="submenu" id="reports">
                    <a href="#" class="nav-link"><i class="fas fa-file-export"></i> Generate Reports</a>
                    <a href="#" class="nav-link"><i class="fas fa-file-import"></i> View Reports</a>
                </div>

                <!-- Settings -->
                <a href="#" class="nav-link" onclick="toggleSubmenu('settings')">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="submenu" id="settings">
                    <a href="#" class="nav-link"><i class="fas fa-user"></i> User Profile</a>
                    <a href="#" class="nav-link"><i class="fas fa-users-cog"></i> Manage Users</a>
                    <a href="#" class="nav-link"><i class="fas fa-sliders-h"></i> System Settings</a>
                    <a href="#" class="nav-link"><i class="fas fa-bell"></i> Notifications</a>
                </div>

                <!-- Help/Support -->
                <a href="#" class="nav-link">
                    <i class="fas fa-question-circle"></i> Help/Support
                </a>

                <!-- Logout -->
                <a href="../logout/" class="nav-link">
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
                    <div class="notification-badge">
                        <i class="fas fa-bell text-muted"></i>
                        <span class="badge rounded-pill bg-danger">3</span>
                    </div>
                    <img src="/placeholder.svg?height=40&width=40" class="rounded-circle" alt="User avatar">
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
                                <th>Stock</th>
                                <th>Unit Price</th>
                                <th>Pack Price</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryTableBody">
                            <!-- Table content will be dynamically populated -->
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
                                    <td><?php echo $product['stock']; ?></td>
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
                                        <button class="action-btn" onclick="">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn" onclick="">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <?php if (!empty($product['variants'])) { ?>
                                    <tr id="variants-<?php echo $product_id; ?>" class="variant-row" style="display: none;">
                                        <td colspan="5">
                                            <table class="variant-table">
                                                <thead>
                                                    <tr>
                                                        <th>Variant</th>
                                                        <th>Stock</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($product['variants'] as $variant): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($variant['variant_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($variant['stock']); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                    onclick="openUpdateVariantModal(<?php echo $product_id; ?>, '<?php echo $variant['variant_name']; ?>', <?php echo $variant['stock']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                                                    <i class="fas fa-edit me-1"></i>Update
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

    <!-- Add Product Modal -->
    <!-- <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" required>
                                <option value="electronics">Electronics</option>
                                <option value="clothing">Clothing</option>
                                <option value="furniture">Furniture</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" step="0.01" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addProduct()">Add Product</button>
                </div>
            </div>
        </div>
    </div> -->

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
                        <!-- <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="categoryStatus" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div> -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <!-- <button type="button" class="btn btn-primary" name="addCat">Add Category</button> -->
                        <input type="submit" class="btn btn-primary" name="addCat" value="Add Category">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../style/js/search.js"></script>
    <script>
        // Add product function
        function addProduct() {
            const form = document.getElementById('addProductForm');
            const formData = new FormData(form);

            // In a real application, you would send this data to a server
            alert('Product added successfully!');
            document.querySelector('#addProductModal').querySelector('.btn-close').click();
        }

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

        // Edit product function
        function editProduct(id) {
            // Implementation would go here
            alert(`Editing product ${id}`);
        }

        // Delete product function
        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                inventoryData = inventoryData.filter(item => item.id !== id);
                renderInventoryTable(inventoryData);
            }
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

        function toggleVariants(productId) {
            const variantsRow = document.getElementById(`variants-${productId}`);
            const expandBtn = document.getElementById(`expand-${productId}`);

            if (variantsRow) {
                if (variantsRow.style.display === 'none' || variantsRow.style.display === '') {
                    variantsRow.style.display = 'table-row';
                    expandBtn.innerHTML = '<i class="fas fa-minus"></i>';
                } else {
                    variantsRow.style.display = 'none';
                    expandBtn.innerHTML = '<i class="fas fa-plus"></i>';
                }
            }
        }
    </script>
</body>

</html>