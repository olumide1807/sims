<?php
session_start();

include "../config/session_check.php";
include "../config/config.php";

// Get the current page from the URL, defaulting to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of rows per page
$offset = ($page - 1) * $limit;

$total_qry = "SELECT COUNT(*) as total FROM products";
$total_res = mysqli_query($connect, $total_qry);
$total_row = mysqli_fetch_assoc($total_res);
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);


/* $sel_qry = "SELECT * FROM products LIMIT $offset, $limit";
$res = mysqli_query($connect, $sel_qry);

if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $cat[] = $row;
    }
} */

$sel_qry = "SELECT 
                p.id AS product_id, 
                p.product_name, 
                p.category, 
                p.quantity_packet AS product_stock, 
                v.id AS variant_id, 
                v.variant_name, 
                v.qty_packet AS variant_stock
            FROM products p
            LEFT JOIN product_variants v ON p.id = v.product_id
            ORDER BY p.id, v.id
            LIMIT $offset, $limit";

$res = mysqli_query($connect, $sel_qry);

$products = [];
if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $products[$row['product_id']]['name'] = $row['product_name'];
        $products[$row['product_id']]['category'] = $row['category'];
        $products[$row['product_id']]['stock'] = $row['product_stock'];

        if ($row['variant_id']) {
            $products[$row['product_id']]['variants'][] = [
                'variant_name' => $row['variant_name'],
                'stock' => $row['variant_stock']
            ];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Inventory - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="../style/css/style.css" rel="stylesheet">
    <style>
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

        .highlight-row:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .low-stock {
            background-color: rgba(255, 0, 0, 0.1);
        }

        .variant-table {
            width: 100%;
            margin-bottom: 0;
        }

        .variant-table td,
        .variant-table th {
            padding: 0.5rem;
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

        /* Container to ensure pagination appears below table */
        .inventory-container {
            display: flex;
            flex-direction: column;
        }

        .inventory-table {
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="layout-container">
        <!-- Sidebar (Same as previous page) -->
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
                    <a href="../inventory/addproduct.php" class="nav-link"><i class="fas fa-plus"></i> Add Product</a>
                    <a href="#" class="nav-link active"><i class="fas fa-edit"></i> Update Inventory</a>
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
                    <input type="text" placeholder="Search products..." id="searchInput">
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
                <h3>Update Inventory</h3>
                <p class="mb-0">Modify stock levels and product details</p>
            </div>

            <!-- Inventory Update Table -->
            <div class="inventory-table">
                <table class="table table-hover" id="inventoryTable">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product_id => $product): ?>
                            <tr class="highlight-row <?php echo ($product['stock'] <= 5) ? 'low-stock' : ''; ?>">
                                <td>
                                    <?php if (!empty($product['variants'])): ?>
                                        <span class="expand-btn" onclick="toggleVariants(<?php echo $product_id; ?>)" id="expand-<?php echo $product_id; ?>">
                                            <i class="fas fa-plus"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="openUpdateModal(<?php echo $product_id; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['stock']; ?>)">
                                        <i class="fas fa-edit me-1"></i>Update
                                    </button>
                                </td>
                            </tr>

                            <?php if (!empty($product['variants'])): ?>
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
                            <?php endif; ?>
                        <?php endforeach; ?>
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

    <!-- Modal for Stock Update -->
    <div class="modal fade" id="updateStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStockForm">
                        <input type="hidden" id="productId">
                        <input type="hidden" id="variantId">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" readonly>
                        </div>
                        <div id="variantInfoDiv" class="mb-3 d-none">
                            <label class="form-label">Variant</label>
                            <input type="text" class="form-control" id="variantInfo" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Stock</label>
                            <input type="number" class="form-control" id="currentStock" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Adjustment</label>
                            <div class="input-group">
                                <span class="input-group-text">±</span>
                                <input type="number" class="form-control" id="stockAdjustment" required>
                            </div>
                            <small class="form-text text-muted">Enter positive number to add stock, negative to reduce</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Adjustment</label>
                            <select class="form-select" id="adjustmentReason" required>
                                <option value="">Select Reason</option>
                                <option value="restock">Restock</option>
                                <option value="sales">Sales</option>
                                <option value="damage">Damaged Goods</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveStockUpdate()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mock inventory data (in a real app, this would come from a backend)
        const inventoryData = [{
                id: 1,
                name: 'Smartphone X',
                category: 'Electronics',
                sku: 'PHONE-001',
                stock: 15,
                lowStockThreshold: 20,
                hasVariants: true,
                variants: [{
                        id: 101,
                        name: 'Black / 128GB',
                        stock: 5,
                        sku: 'PHONE-001-BLK-128'
                    },
                    {
                        id: 102,
                        name: 'Black / 256GB',
                        stock: 3,
                        sku: 'PHONE-001-BLK-256'
                    },
                    {
                        id: 103,
                        name: 'White / 128GB',
                        stock: 4,
                        sku: 'PHONE-001-WHT-128'
                    },
                    {
                        id: 104,
                        name: 'White / 256GB',
                        stock: 3,
                        sku: 'PHONE-001-WHT-256'
                    }
                ]
            },
            {
                id: 2,
                name: 'Laptop Pro',
                category: 'Computers',
                sku: 'LAPTOP-002',
                stock: 8,
                lowStockThreshold: 10,
                hasVariants: true,
                variants: [{
                        id: 201,
                        name: 'i5 / 8GB / 256GB',
                        stock: 3,
                        sku: 'LAPTOP-002-I5-8-256'
                    },
                    {
                        id: 202,
                        name: 'i7 / 16GB / 512GB',
                        stock: 5,
                        sku: 'LAPTOP-002-I7-16-512'
                    }
                ]
            },
            {
                id: 3,
                name: 'Wireless Headphones',
                category: 'Audio',
                sku: 'HEAD-003',
                stock: 25,
                lowStockThreshold: 20,
                hasVariants: false,
                variants: []
            }
        ];

        // Render inventory table
        function renderInventoryTable(data) {
            const tableBody = document.getElementById('inventoryTableBody');
            tableBody.innerHTML = '';

            data.forEach(product => {
                // Main product row
                const row = document.createElement('tr');
                row.classList.add('highlight-row');

                // Add low stock class if necessary
                if (product.stock <= product.lowStockThreshold) {
                    row.classList.add('low-stock');
                }

                const expandIcon = product.hasVariants ? '<i class="fas fa-plus"></i>' : '';

                row.innerHTML = `
                    <td>
                        ${product.hasVariants ? 
                          `<span class="expand-btn" onclick="toggleVariants(${product.id})" id="expand-${product.id}">${expandIcon}</span>` : 
                          ''}
                    </td>
                    <td>${product.name}</td>
                    <td>${product.category}</td>
                    <td>${product.stock}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="openUpdateModal(${product.id})">
                            <i class="fas fa-edit me-1"></i>Update
                        </button>
                    </td>
                `;

                tableBody.appendChild(row);

                // Create hidden variants rows
                if (product.hasVariants) {
                    const variantRow = document.createElement('tr');
                    variantRow.id = `variants-${product.id}`;
                    variantRow.style.display = 'none';
                    variantRow.classList.add('variant-row');

                    let variantContent = `
                        <td colspan="5">
                            <table class="variant-table">
                                <thead>
                                    <tr>
                                        <th>Variant</th>
                                        <th>SKU</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    product.variants.forEach(variant => {
                        variantContent += `
                            <tr>
                                <td>${variant.name}</td>
                                <td>${variant.sku}</td>
                                <td>${variant.stock}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="openUpdateVariantModal(${product.id}, ${variant.id})">
                                        <i class="fas fa-edit me-1"></i>Update
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    variantContent += `
                                </tbody>
                            </table>
                        </td>
                    `;

                    variantRow.innerHTML = variantContent;
                    tableBody.appendChild(variantRow);
                }
            });
        }

        // Toggle variants visibility
        function toggleVariants(productId) {
            const variantsRow = document.getElementById(`variants-${productId}`);
            const expandBtn = document.getElementById(`expand-${productId}`);

            if (variantsRow.style.display === 'none') {
                variantsRow.style.display = '';
                expandBtn.innerHTML = '<i class="fas fa-minus"></i>';
            } else {
                variantsRow.style.display = 'none';
                expandBtn.innerHTML = '<i class="fas fa-plus"></i>';
            }
        }

        // Open stock update modal for main product
        function openUpdateModal(productId) {
            const product = inventoryData.find(p => p.id === productId);

            document.getElementById('productId').value = product.id;
            document.getElementById('variantId').value = '';
            document.getElementById('productName').value = product.name;
            document.getElementById('currentStock').value = product.stock;
            document.getElementById('stockAdjustment').value = '';
            document.getElementById('adjustmentReason').value = '';

            // Hide variant info
            document.getElementById('variantInfoDiv').classList.add('d-none');
            document.getElementById('variantInfo').value = '';

            new bootstrap.Modal(document.getElementById('updateStockModal')).show();
        }

        // Open stock update modal for variant
        function openUpdateVariantModal(productId, variantId) {
            const product = inventoryData.find(p => p.id === productId);
            const variant = product.variants.find(v => v.id === variantId);

            document.getElementById('productId').value = product.id;
            document.getElementById('variantId').value = variant.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('currentStock').value = variant.stock;
            document.getElementById('stockAdjustment').value = '';
            document.getElementById('adjustmentReason').value = '';

            // Show variant info
            document.getElementById('variantInfoDiv').classList.remove('d-none');
            document.getElementById('variantInfo').value = variant.name;

            new bootstrap.Modal(document.getElementById('updateStockModal')).show();
        }

        // Save stock update
        function saveStockUpdate() {
            const productId = parseInt(document.getElementById('productId').value);
            const variantId = document.getElementById('variantId').value;
            const adjustment = parseInt(document.getElementById('stockAdjustment').value);
            const reason = document.getElementById('adjustmentReason').value;

            if (!reason) {
                alert('Please select a reason for stock adjustment');
                return;
            }

            // Find product
            const productIndex = inventoryData.findIndex(p => p.id === productId);

            if (variantId === '') {
                // Update main product stock
                inventoryData[productIndex].stock += adjustment;

                // If product has variants, we can optionally update all variant stocks proportionally
                // This is just one approach - you might want different behavior
                if (inventoryData[productIndex].hasVariants && inventoryData[productIndex].variants.length > 0) {
                    const variantsCount = inventoryData[productIndex].variants.length;
                    const adjustmentPerVariant = Math.floor(adjustment / variantsCount);

                    if (adjustmentPerVariant !== 0) {
                        inventoryData[productIndex].variants.forEach(variant => {
                            variant.stock += adjustmentPerVariant;
                            if (variant.stock < 0) variant.stock = 0;
                        });
                    }
                }
            } else {
                // Update specific variant
                const variantIdInt = parseInt(variantId);
                const variantIndex = inventoryData[productIndex].variants.findIndex(v => v.id === variantIdInt);

                if (variantIndex !== -1) {
                    inventoryData[productIndex].variants[variantIndex].stock += adjustment;
                    if (inventoryData[productIndex].variants[variantIndex].stock < 0) {
                        inventoryData[productIndex].variants[variantIndex].stock = 0;
                    }

                    // Recalculate total stock
                    inventoryData[productIndex].stock = inventoryData[productIndex].variants.reduce(
                        (total, v) => total + v.stock, 0
                    );
                }
            }

            // Re-render table
            renderInventoryTable(inventoryData);

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('updateStockModal')).hide();

            // Show success message
            const itemName = variantId ?
                `${inventoryData[productIndex].name} (${inventoryData[productIndex].variants.find(v => v.id === parseInt(variantId)).name})` :
                inventoryData[productIndex].name;

            alert(`Stock for ${itemName} updated successfully!`);
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const filteredData = inventoryData.filter(product =>
                product.name.toLowerCase().includes(searchTerm) ||
                product.category.toLowerCase().includes(searchTerm) ||
                (product.hasVariants && product.variants.some(v =>
                    v.name.toLowerCase().includes(searchTerm) ||
                    v.sku.toLowerCase().includes(searchTerm)
                ))
            );
            renderInventoryTable(filteredData);
        });

        // Initial render
        document.addEventListener('DOMContentLoaded', () => {
            renderInventoryTable(inventoryData);
        });

        // Sidebar toggle functions (same as previous page)
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