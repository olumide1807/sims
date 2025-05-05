<?php
session_start();

include "../config/config.php";
include "../config/session_check.php";

$sel_qry = "SELECT * FROM category";
$res = mysqli_query($connect, $sel_qry);

if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $cat[] = $row;
    }
}

if (isset($_POST['saveProduct'])) {
    $product_name   = isset($_POST['product_name']) ? mysqli_real_escape_string($connect, $_POST['product_name']) : '';
    $category       = isset($_POST['category']) ? mysqli_real_escape_string($connect, $_POST['category']) : '';
    $brand          = isset($_POST['brand']) ? mysqli_real_escape_string($connect, $_POST['brand']) : '';
    $batch_number   = isset($_POST['batch_number']) ? mysqli_real_escape_string($connect, $_POST['batch_number']) : '';
    $prod_date      = isset($_POST['prod_date']) ? trim($_POST['prod_date']) : '';
    $exp_date       = isset($_POST['exp_date']) ? trim($_POST['exp_date']) : '';
    $desc           = isset($_POST['desc']) ? mysqli_real_escape_string($connect, $_POST['desc']) : '';
    $cost_price     = isset($_POST['cost_price']) ? trim($_POST['cost_price']) : '';
    $price_per_packet = isset($_POST['price_per_packet']) ? trim($_POST['price_per_packet']) : '';
    $price_per_sachet = isset($_POST['price_per_sachet']) ? trim($_POST['price_per_sachet']) : '';
    $quantity_packet   = isset($_POST['quantity_packet']) ? trim($_POST['quantity_packet']) : '';
    $quantity_per_pack = isset($_POST['quantity_per_pack']) ? trim($_POST['quantity_per_pack']) : '';
    $low_stock_alert   = isset($_POST['low_stock_alert']) ? trim($_POST['low_stock_alert']) : '';
    $expiry_alert_days = isset($_POST['expiry_alert_days']) ? trim($_POST['expiry_alert_days']) : '';

    // Insert product into the products table
    $sql = "INSERT INTO products (`product_name`, `category`, `brand`, `batch_number`, `prod_date`, `exp_date`, `desc`, `cost_price`, `price_per_packet`, `price_per_sachet`, `quantity_packet`, `quantity_per_pack`, `low_stock_alert`, `expiry_alert_days`) 
            VALUES ('$product_name', '$category', '$brand', '$batch_number', '$prod_date', '$exp_date', 
            '$desc', '$cost_price', '$price_per_packet', '$price_per_sachet', '$quantity_packet', 
            '$quantity_per_pack', '$low_stock_alert', '$expiry_alert_days')";

    if (mysqli_query($connect, $sql)) {
        $product_id = mysqli_insert_id($connect); // Get last inserted product ID

        // Insert variants if they exist
        if (isset($_POST['v'])) {
            /* echo '<pre>';
            print_r($_POST['v']);
            echo '</pre>';
            die(); */
            foreach ($_POST['v'] as $variantId => $variantData) {
                $variant_name           = isset($variantData['name']) ? mysqli_real_escape_string($connect, $variantData['name']) : '';
                $variant_price_packet   = isset($variantData['pricePacket']) ? trim($variantData['pricePacket']) : '';
                $variant_price_sachet   = isset($variantData['priceSachet']) ? trim($variantData['priceSachet']) : '';
                $variant_quantity_packet = isset($variantData['quantity']) ? trim($variantData['quantity']) : '';
                $variant_quantity_per_pack = isset($variantData['qtyInPack']) ? trim($variantData['qtyInPack']) : '';

                $sql_variant = "INSERT INTO product_variants (product_id, variant_name, price_per_packet, price_per_sachet, qty_packet, qty_sachet) 
                                VALUES ('$product_id', '$variant_name', '$variant_price_packet', '$variant_price_sachet', '$variant_quantity_packet', '$variant_quantity_per_pack')";

                mysqli_query($connect, $sql_variant);
            }
        }
        echo '<div id="alertBox" class="alert alert-success">
                <span class="closebtn" onclick="this.parentElement.style.display=\'none\';">&times;</span> 
                <strong>Success!</strong> Product(s) added successfully!.
            </div>';
    } else {
        echo "Error: " . mysqli_error($connect);
    }

    mysqli_close($connect);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - SIMS</title>
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
                <a href="../dashboard/" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="#" class="nav-link active" onclick="toggleSubmenu('inventory')">
                    <i class="fas fa-box"></i> Inventory Management
                </a>
                <div class="submenu show" id="inventory">
                    <a href="../inventory/" class="nav-link"><i class="fas fa-list"></i> View Inventory</a>
                    <a href="#" class="nav-link active"><i class="fas fa-plus"></i> Add Product</a>
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

            <div class="header-section">
                <h3>Add New Product</h3>
                <p class="mb-0">Fill in the product details below</p>
            </div>

            <!-- Add Product Form -->
            <form id="addProductForm" method="post">
                <div class="form-card mb-4">
                    <div class="form-section">
                        <h5 class="mb-4">Basic Information</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Product Name*</label>
                                <input type="text" class="form-control" name="product_name" required>
                            </div>
                            <!-- <div class="col-md-6">
                                <label class="form-label">SKU*</label>
                                <input type="text" class="form-control" name="sku" required>
                            </div> -->
                            <div class="col-md-6">
                                <label class="form-label">Category*</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    foreach ($cat as $row) {
                                        echo "<option value='{$row["category"]}'>{$row["category"]}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Brand</label>
                                <input type="text" class="form-control" name="brand">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Batch Number</label>
                                <input type="text" class="form-control" name="batch_number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Production Date</label>
                                <input type="date" class="form-control" name="prod_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiration Date</label>
                                <input type="date" class="form-control" name="exp_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="desc" rows="4"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="mb-4">Pricing & Inventory</h5>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Cost Price*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="cost_price" step="0.01" required>
                                </div>
                            </div>
                            <!-- <div class="col-md-4">
                                <label class="form-label">Sale Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="salePrice" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Initial Stock*</label>
                                <input type="number" class="form-control" name="stock" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Low Stock Alert</label>
                                <input type="number" class="form-control" name="lowStockAlert">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" class="form-control" name="weight" step="0.01">
                            </div> -->
                            <div class="col-md-4">
                                <label class="form-label">Price Per Unit (Packet)*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="price_per_packet" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price Per Unit (Sachet)*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="price_per_sachet" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantity (Packet)*</label>
                                <input type="number" class="form-control" name="quantity_packet" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantity in a pack?*</label>
                                <input type="number" class="form-control" name="quantity_per_pack" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h5 class="mb-4">Alerts</h5>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Low Stock Alert</label>
                                <input type="number" class="form-control" name="low_stock_alert">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Upcoming Expiry Alert</label>
                                <input type="number" class="form-control" name="expiry_alert_days">
                            </div>
                        </div>
                    </div>
                    <!-- <div class="form-section">
                        <h5 class="mb-4">Product Images</h5>
                        <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                            <input type="file" id="imageInput" hidden accept="image/*" onchange="handleImageUpload(event)">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-1">Drop your images here or click to upload</p>
                            <p class="text-muted small">Supported formats: JPG, PNG, GIF (Max 5MB)</p>
                            <img id="previewImage" class="preview-image mt-3" alt="Preview">
                        </div>
                    </div> -->

                    <div class="form-section">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">Product Variants</h5>
                            <button type="button" class="btn btn-primary" onclick="addVariant()">
                                <i class="fas fa-plus me-2"></i>Add Variant
                            </button>
                        </div>
                        <div id="variantsContainer">
                            <!-- Variants will be added here dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-3 mb-4">
                    <button type="submit" class="btn btn-primary px-4" name="saveProduct">
                        <i class="fas fa-save me-2"></i>Save Product
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="resetForm()">
                        Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar submenu
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

        // Handle image upload
        function handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('previewImage');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        // Add variant
        function addVariant() {
            const variantsContainer = document.getElementById('variantsContainer');
            const variantId = Date.now();
            const variantHtml = `
                <div class="variant-card" id="variant-${variantId}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">New Variant</h6>
                        <i class="fas fa-times close-btn" onclick="removeVariant(${variantId})"></i>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Variant Name</label>
                            <input type="text" class="form-control" name="v[${variantId}][name]">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Price Packet</label>
                            <input type="number" class="form-control" name="v[${variantId}][pricePacket]" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Price Sachet</label>
                            <input type="number" class="form-control" name="v[${variantId}][priceSachet]" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity (Packet)*</label>
                            <input type="number" class="form-control" name="v[${variantId}][quantity]">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity in a pack?*</label>
                            <input type="number" class="form-control" name="v[${variantId}][qtyInPack]">
                        </div>
                    </div>
                </div>
            `;
            variantsContainer.insertAdjacentHTML('beforeend', variantHtml);
        }

        // Remove variant
        function removeVariant(variantId) {
            const variant = document.getElementById(`variant-${variantId}`);
            variant.remove();
        }

        // Form submission
        function handleSubmit(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            // In a real application, you would send this data to your backend
            console.log('Form Data:', data);
            alert('Product saved successfully!');
        }

        // Reset form
        function resetForm() {
            if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
                // Reset the form fields
                document.getElementById('addProductForm').reset();

                // Clear image preview
                const preview = document.getElementById('previewImage');
                preview.src = '';
                preview.style.display = 'none';

                // Clear all variants
                const variantsContainer = document.getElementById('variantsContainer');
                variantsContainer.innerHTML = '';
            }
        }

        // Initialize the form
        document.addEventListener('DOMContentLoaded', function() {
            // Add any initial setup here if needed

            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const mobileToggle = document.querySelector('.mobile-menu-toggle');

                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            });
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
    </script>
</body>

</html>