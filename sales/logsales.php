<?php
    session_start();
    
    include "../config/session_check.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Sale - SIMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">
    <!-- <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --sidebar-width: 295px;
        }

        body {
            background-color: #f3f4f6;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
        }

        .layout-container {
            display: flex;
            gap: 24px;
            padding: 24px;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            border-radius: 16px;
            padding: 24px;
            position: fixed;
            height: calc(100vh - 48px);
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .logo-section {
            padding-bottom: 24px;
            margin-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .nav-link {
            color: #4b5563;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 4px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(4px);
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
        }

        /* Submenu Styles */
        .submenu {
            margin-left: 32px;
            display: none;
        }

        .submenu.show {
            display: block;
        }

        .submenu .nav-link {
            font-size: 0.9rem;
            padding: 8px 16px;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 1001;
            background: white;
            border: none;
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding-left: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px;
            border-color: #e5e7eb;
        }

        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .product-row {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .summary-section {
            background: var(--primary-gradient);
            color: white;
            border-radius: 12px;
            padding: 24px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .mobile-menu-toggle {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding-left: 0;
            }

            .product-row {
                margin-bottom: 24px;
            }
        }
    </style> -->
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
                    <a href="../inventory/updateproduct.php" class="nav-link"><i class="fas fa-edit"></i> Update Inventory</a>
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
            <!-- Header -->
            <div class="header">
                <h4 class="mb-0">Log New Sale</h4>
                <div class="d-flex gap-3">
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-clock me-2"></i>Recent Sales
                    </button>
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Sale
                    </button>
                </div>
            </div>

            <!-- Sales Form -->
            <div class="content-card">
                <form id="salesForm">
                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" placeholder="Enter customer name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Email</label>
                            <input type="email" class="form-control" placeholder="Enter customer email">
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Products</h5>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addProductRow()">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </button>
                        </div>
                        
                        <div id="productsContainer">
                            <div class="product-row">
                            <button type="button" style="float: right;" class="btn btn-outline-danger btn-sm remove-product-btn" onclick="removeProductRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Product</label>
                                        <select class="form-select">
                                            <option value="">Select Product</option>
                                            <option value="1">iPhone 15 Pro</option>
                                            <option value="2">Samsung Galaxy S23</option>
                                            <option value="3">MacBook Pro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" min="1" value="1">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Unit Price</label>
                                        <input type="number" class="form-control" value="999.99" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Total</label>
                                        <input type="number" class="form-control" value="999.99" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Section -->
                    <div class="summary-section">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h6 class="mb-2">Subtotal</h6>
                                <h4>$999.99</h4>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h6 class="mb-2">Tax (10%)</h6>
                                <h4>$99.99</h4>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-2">Total Amount</h6>
                                <h4>$1,099.98</h4>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
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
            newRow.querySelector('select').value = '';
            
            container.appendChild(newRow);
        }

        function removeProductRow(button) {
            const container = document.getElementById('productsContainer');
            // Prevent removing the last row
            if (container.children.length > 1) {
                button.closest('.product-row').remove();
            }
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