<?php
session_start();

include "../config/config.php";
include "../config/session_check.php";

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
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryTableBody">
                            <!-- Table content will be dynamically populated -->
                        </tbody>
                    </table>
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
    <script>
        // Sample inventory data
        let inventoryData = [{
                id: 1,
                name: 'iPhone 15 Pro',
                category: 'electronics',
                stock: 24,
                price: 999.99,
                status: 'in-stock',
                lastUpdated: '2024-01-30'
            },
            {
                id: 2,
                name: 'Samsung Galaxy S23',
                category: 'electronics',
                stock: 5,
                price: 899.99,
                status: 'low-stock',
                lastUpdated: '2024-01-29'
            },
            {
                id: 3,
                name: 'MacBook Pro',
                category: 'electronics',
                stock: 0,
                price: 1299.99,
                status: 'out-of-stock',
                lastUpdated: '2024-01-28'
            }
        ];

        // Function to render the inventory table
        function renderInventoryTable(data) {
            const tableBody = document.getElementById('inventoryTableBody');
            tableBody.innerHTML = '';

            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td>${item.category}</td>
                    <td>${item.stock}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td><span class="status-badge status-${item.status}">${formatStatus(item.status)}</span></td>
                    <td>${item.lastUpdated}</td>
                    <td>
                        <button class="action-btn" onclick="editProduct(${item.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteProduct(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Format status text
        function formatStatus(status) {
            return status.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        // Filter functions
        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;

            const filteredData = inventoryData.filter(item => {
                const matchesSearch = item.name.toLowerCase().includes(searchTerm);
                const matchesCategory = !category || item.category === category;
                const matchesStatus = !status || item.status === status;
                return matchesSearch && matchesCategory && matchesStatus;
            });

            renderInventoryTable(filteredData);
        }

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

        // function toggleSidebar() {
        //     console.log('Toggle sidebar called'); // Debug log
        //     const sidebar = document.getElementById('sidebar');
        //     console.log('Sidebar element:', sidebar); // Check if sidebar is found

        //     if (sidebar) {
        //         sidebar.classList.toggle('show');
        //         console.log('Sidebar classes:', sidebar.className); // Log current classes
        //     } else {
        //         console.error('Sidebar element not found');
        //     }
        // }

        // // Add click event listener as a backup
        // document.querySelector('.mobile-menu-toggle').addEventListener('click', function(event) {
        //     console.log('Mobile menu toggle clicked');
        //     event.stopPropagation();
        //     toggleSidebar();
        // });

        //         // Enhance mobile interactions
        // document.addEventListener('DOMContentLoaded', () => {
        //     const sidebar = document.getElementById('sidebar');
        //     const mobileToggle = document.querySelector('.mobile-menu-toggle');

        //     // Close sidebar when a link is clicked on mobile
        //     sidebar.querySelectorAll('.nav-link').forEach(link => {
        //         link.addEventListener('click', () => {
        //             if (window.innerWidth <= 768) {
        //                 sidebar.classList.remove('show');
        //             }
        //         });
        //     });

        //     // Prevent body scroll when sidebar is open
        //     function toggleBodyScroll() {
        //         if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
        //             document.body.style.overflow = 'hidden';
        //         } else {
        //             document.body.style.overflow = 'auto';
        //         }
        //     }

        //     // Add event listeners
        //     mobileToggle.addEventListener('click', () => {
        //         sidebar.classList.toggle('show');
        //         toggleBodyScroll();
        //     });

        //     // Recheck responsive behavior on resize
        //     window.addEventListener('resize', () => {
        //         if (window.innerWidth > 768) {
        //             sidebar.classList.remove('show');
        //             document.body.style.overflow = 'auto';
        //         }
        //     });
        // });

        // Initial render
        renderInventoryTable(inventoryData);
        renderCategoriesTable(categoriesData);
    </script>
</body>

</html>