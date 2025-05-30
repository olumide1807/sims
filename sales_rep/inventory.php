<?php
session_start();

include "../config/config.php";
include "../config/session_check.php";
include "../config/notification_functions.php";
include "../config/dashboard_functions.php";

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];

// Get notification data
$notification_count = getNotificationCount($user_id, $connect);
$notifications = getUserNotifications($user_id, $connect, 5); // Get 5 latest notifications
$notification_stats = getNotificationStats(getUserNotificationSettings($user_id, $connect), $connect);

// Get dashboard data
$dashboard_stats = getDashboardStats($connect);
$recent_activities = getRecentActivities($connect, 5);
$active_alerts = getActiveAlerts($connect);
$inventory_data = getInventoryOverviewData($connect, 'weekly');

/* // Check user role
if ($_SESSION['role'] !== 'sales_rep') {
    header("Location: ../unauthorized.php");
    exit();
} */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory View | Sales Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">
    <?php echo getNotificationDropdownCSS(); ?>
    <style>
        /* Additional styles for inventory view */
        .inventory-card {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        .inventory-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .low-stock {
            border-left-color: #ffc107;
        }
        .critical-stock {
            border-left-color: #dc3545;
        }
        .expiring-soon {
            border-left-color: #fd7e14;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: contain;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .barcode-scan-btn {
            cursor: pointer;
        }
        .stock-level {
            width: 100px;
        }
        .table-responsive {
            min-height: 400px;
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="layout-container">
        <!-- Sidebar (same as sales rep dashboard) -->
        <div class="sidebar" id="sidebar">
            <div class="logo-section">
                <h4 class="d-flex align-items-center gap-2">
                    <i class="fas fa-cubes text-primary"></i>
                    SIMS Sales
                </h4>
            </div>

            <nav>
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <a href="#" class="nav-link active">
                    <i class="fas fa-box"></i> View Inventory
                </a>

                <a href="#" class="nav-link" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-shopping-cart"></i> Sales
                </a>
                <div class="submenu" id="sales">
                    <a href="logsales.php" class="nav-link"><i class="fas fa-cash-register"></i> New Sale</a>
                    <a href="viewsales.php" class="nav-link"><i class="fas fa-chart-bar"></i> Sales History</a>
                </div>

                <a href="../logout/" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="search-box">
                    <i class="fas fa-search text-muted me-2"></i>
                    <input type="text" id="inventory-search" placeholder="Search products..." onkeyup="searchInventory()">
                    <!-- <button class="btn btn-sm btn-outline-secondary ms-2 barcode-scan-btn" title="Scan Barcode">
                        <i class="fas fa-barcode"></i>
                    </button> -->
                </div>
                <div class="user-section">
                    <?php echo generateNotificationDropdown($user_id, $connect); ?>
                    <div class="user-info ms-3">
                        <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['firstname']); ?></span>
                        <small class="d-block text-muted"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></small>
                    </div>
                </div>
            </div>

            <!-- Inventory Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Product Inventory</h3>
                <div class="btn-group">
                    <button class="btn btn-outline-primary active" onclick="filterInventory('all')">All</button>
                    <button class="btn btn-outline-primary" onclick="filterInventory('low_stock')">Low Stock</button>
                    <button class="btn btn-outline-primary" onclick="filterInventory('expiring')">Expiring Soon</button>
                </div>
            </div>

            <!-- Inventory Summary Cards -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="mb-1" id="total-items">0</h3>
                    <p class="text-muted mb-0">Total Products</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1" id="in-stock">0</h3>
                    <p class="text-muted mb-0">In Stock</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="mb-1" id="low-stock-count">0</h3>
                    <p class="text-muted mb-0">Low Stock</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1" id="expiring-count">0</h3>
                    <p class="text-muted mb-0">Expiring Soon</p>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="content-card">
                <div class="table-responsive">
                    <table class="table table-hover" id="inventory-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>SKU/Barcode</th>
                                <th>Category</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-data">
                            <!-- Data will be loaded here -->
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading inventory data...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Inventory pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickViewModalLabel">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="quick-view-content">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="add-to-sale-btn">
                        <i class="fas fa-cart-plus me-2"></i>Add to Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
    <script>
        // DOM elements
        const inventoryTable = document.getElementById('inventory-table');
        const inventoryData = document.getElementById('inventory-data');
        const quickViewModal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        
        // Sample data - in a real app, this would come from an API
        const sampleProducts = [
            {
                id: 1,
                name: "Paracetamol 500mg (100 tablets)",
                sku: "PHM-PARA-500",
                barcode: "123456789012",
                category: "Pain Relief",
                stock: 42,
                threshold: 10,
                price: 8.99,
                image: "../images/placeholder-drug.png",
                expiry: "2024-12-31",
                status: "in_stock"
            },
            {
                id: 2,
                name: "Ibuprofen 200mg (30 tablets)",
                sku: "PHM-IBU-200",
                barcode: "234567890123",
                category: "Pain Relief",
                stock: 5,
                threshold: 5,
                price: 5.49,
                image: "../images/placeholder-drug.png",
                expiry: "2025-03-15",
                status: "low_stock"
            },
            {
                id: 3,
                name: "Amoxicillin 250mg Capsules",
                sku: "PHM-AMOX-250",
                barcode: "345678901234",
                category: "Antibiotics",
                stock: 18,
                threshold: 5,
                price: 12.99,
                image: "../images/placeholder-drug.png",
                expiry: "2024-09-30",
                status: "in_stock"
            },
            {
                id: 4,
                name: "Cetirizine Hydrochloride 10mg",
                sku: "PHM-CETI-10",
                barcode: "456789012345",
                category: "Allergy",
                stock: 0,
                threshold: 5,
                price: 6.99,
                image: "../images/placeholder-drug.png",
                expiry: "2025-06-30",
                status: "out_of_stock"
            },
            {
                id: 5,
                name: "Vitamin C 1000mg Tablets",
                sku: "PHM-VITC-1000",
                barcode: "567890123456",
                category: "Vitamins",
                stock: 24,
                threshold: 10,
                price: 9.99,
                image: "../images/placeholder-drug.png",
                expiry: "2024-08-15",
                status: "expiring_soon"
            }
        ];

        // Load inventory data
        function loadInventoryData(products) {
            // Update summary cards
            document.getElementById('total-items').textContent = products.length;
            document.getElementById('in-stock').textContent = products.filter(p => p.status === 'in_stock').length;
            document.getElementById('low-stock-count').textContent = products.filter(p => p.status === 'low_stock').length;
            document.getElementById('expiring-count').textContent = products.filter(p => p.status === 'expiring_soon').length;
            
            // Populate table
            inventoryData.innerHTML = '';
            
            products.forEach(product => {
                const row = document.createElement('tr');
                row.className = `inventory-card ${product.status}`;
                row.onclick = () => showQuickView(product);
                
                // Stock level indicator
                let stockClass = '';
                let stockText = '';
                if (product.stock === 0) {
                    stockClass = 'status-badge bg-danger-subtle text-danger';
                    stockText = 'Out of Stock';
                } else if (product.stock <= product.threshold) {
                    stockClass = 'status-badge bg-warning-subtle text-warning';
                    stockText = 'Low Stock';
                } else {
                    stockClass = 'status-badge bg-success-subtle text-success';
                    stockText = 'In Stock';
                }
                
                // Expiry warning
                const expiryDate = new Date(product.expiry);
                const today = new Date();
                const diffTime = expiryDate - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                let expiryBadge = '';
                if (diffDays <= 30) {
                    expiryBadge = `<span class="status-badge bg-danger-subtle text-danger ms-2">Expires in ${diffDays} days</span>`;
                }
                
                row.innerHTML = `
                    <!-- <td>
                        <img src="${product.image}" alt="${product.name}" class="product-image">
                    </td> -->
                    <td>
                        <strong>${product.name}</strong>
                        ${expiryBadge}
                    </td>
                    <td>${product.sku}<br><small class="text-muted">${product.barcode}</small></td>
                    <td>${product.category}</td>
                    <td class="text-center">
                        <span class="status-badge ${stockClass} stock-level">${product.stock} units</span>
                    </td>
                    <td class="text-center">$${product.price.toFixed(2)}</td>
                    <td class="text-center">
                        ${stockText}
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();addToSale(${product.id})">
                            <i class="fas fa-cart-plus"></i> Add
                        </button>
                    </td>
                `;
                
                inventoryData.appendChild(row);
            });
        }
        
        // Show quick view modal
        function showQuickView(product) {
            const modalContent = document.getElementById('quick-view-content');
            
            // Format expiry date
            const expiryDate = new Date(product.expiry);
            const formattedExpiry = expiryDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="${product.image}" alt="${product.name}" class="img-fluid mb-3" style="max-height: 200px;">
                        <div class="d-grid">
                            <button class="btn btn-primary" onclick="addToSale(${product.id})">
                                <i class="fas fa-cart-plus me-2"></i>Add to Sale
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h4>${product.name}</h4>
                        <table class="table table-sm">
                            <tr>
                                <th width="30%">SKU:</th>
                                <td>${product.sku}</td>
                            </tr>
                            <tr>
                                <th>Barcode:</th>
                                <td>${product.barcode}</td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>${product.category}</td>
                            </tr>
                            <tr>
                                <th>Current Stock:</th>
                                <td>
                                    <span class="fw-bold">${product.stock} units</span>
                                    <span class="ms-2">(Reorder at ${product.threshold} units)</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Price:</th>
                                <td>$${product.price.toFixed(2)}</td>
                            </tr>
                            <tr>
                                <th>Expiry Date:</th>
                                <td>${formattedExpiry}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            
            // Set the product ID on the Add to Sale button
            document.getElementById('add-to-sale-btn').onclick = function() {
                addToSale(product.id);
            };
            
            quickViewModal.show();
        }
        
        // Add product to sale (simplified for demo)
        function addToSale(productId) {
            // In a real app, this would add to a cart or redirect to sales page
            alert(`Product ${productId} added to sale. Redirecting to sales page...`);
            // window.location.href = '../sales/logsales.php?product_id=' + productId;
            quickViewModal.hide();
        }
        
        // Filter inventory
        function filterInventory(filterType) {
            let filteredProducts = [...sampleProducts];
            
            switch(filterType) {
                case 'low_stock':
                    filteredProducts = sampleProducts.filter(p => p.status === 'low_stock');
                    break;
                case 'expiring':
                    filteredProducts = sampleProducts.filter(p => p.status === 'expiring_soon');
                    break;
                // 'all' shows all products
            }
            
            loadInventoryData(filteredProducts);
        }
        
        // Search inventory
        function searchInventory() {
            const searchTerm = document.getElementById('inventory-search').value.toLowerCase();
            
            if (searchTerm === '') {
                loadInventoryData(sampleProducts);
                return;
            }
            
            const filteredProducts = sampleProducts.filter(product => 
                product.name.toLowerCase().includes(searchTerm) ||
                product.sku.toLowerCase().includes(searchTerm) ||
                product.barcode.includes(searchTerm) ||
                product.category.toLowerCase().includes(searchTerm)
            );
            
            loadInventoryData(filteredProducts);
        }
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Load sample data
            setTimeout(() => {
                loadInventoryData(sampleProducts);
            }, 800);
            
            // Barcode scanner button
            document.querySelector('.barcode-scan-btn').addEventListener('click', function() {
                alert("Barcode scanner would activate here. For demo, enter a barcode manually.");
            });
        });

        // Sidebar functions (same as dashboard)
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            if (submenu) {
                submenu.classList.toggle('show');
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

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