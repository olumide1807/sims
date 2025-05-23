<?php
session_start();
include "../config/session_check.php";
include "../config/config.php"; // Make sure you have this file for database connection

// Query to fetch sales data
$query = "SELECT 
            s.id,
            s.transaction_number, 
            COUNT(DISTINCT si.product_id) as item_count,
            s.subtotal,
            s.tax_amount,
            s.total_amount,
            s.sale_status,
            s.sale_date
          FROM 
            sales s
          LEFT JOIN 
            sale_details si ON s.id = si.sale_id
          LEFT JOIN 
            products p ON si.product_id = p.id
          GROUP BY 
            s.id
          ORDER BY 
            s.sale_date DESC";

$result = mysqli_query($connect, $query);

// Get unique categories for the filter
$categoryQuery = "SELECT DISTINCT category FROM products";
$categoryResult = mysqli_query($connect, $categoryQuery);
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
                <a href="#" class="nav-link" onclick="toggleSubmenu('inventory')">
                    <i class="fas fa-box"></i> Inventory Management
                </a>
                <div class="submenu" id="inventory">
                    <a href="#" class="nav-link"><i class="fas fa-list"></i> View Inventory</a>
                    <a href="../inventory/addproduct.php" class="nav-link"><i class="fas fa-plus"></i> Add Product</a>
                    <a href="../inventory/updateproduct.php" class="nav-link"><i class="fas fa-edit"></i> Update Inventory</a>
                </div>

                <!-- Sales Management -->
                <a href="#" class="nav-link active" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-shopping-cart"></i> Sales Management
                </a>
                <div class="submenu show" id="sales">
                    <a href="../sales/logsales.php" class="nav-link"><i class="fas fa-cash-register"></i> Log Sale</a>
                    <a href="#" class="nav-link active"><i class="fas fa-chart-bar"></i> View Sales</a>
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

            <!-- Sales Overview Section -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Sales Overview</h3>
                        <p class="mb-3">Track and analyze your sales performance</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-light rounded-pill px-4 mb-2 mb-md-0" id="exportBtn">
                            <i class="fas fa-download me-2"></i>Export Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Search sales..." id="searchInput">
                        </div>
                    </div>
                    <!-- <div class="col-md-3">
                        <select class="form-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php
                            if ($categoryResult) {
                                while ($categoryRow = mysqli_fetch_assoc($categoryResult)) {
                                    echo "<option value='" . htmlspecialchars($categoryRow['category']) . "'>" . htmlspecialchars($categoryRow['category']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div> -->
                    <div class="col-md-4">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="applyFiltersBtn">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="inventory-table">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Tax Amount</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            <?php
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $statusClass = "";
                                    switch (strtolower($row['sale_status'])) {
                                        case 'completed':
                                            $statusClass = "completed";
                                            break;
                                        case 'pending':
                                            $statusClass = "pending";
                                            break;
                                        case 'cancelled':
                                            $statusClass = "cancelled";
                                            break;
                                        default:
                                            $statusClass = "bg-secondary";
                                    }

                                    echo "<tr data-id='" . htmlspecialchars($row['id']) . "' class='sale-row'>";
                                    echo "<td>" . htmlspecialchars($row['transaction_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['item_count']) . "</td>";
                                    echo "<td>₵" . number_format($row['subtotal'], 2) . "</td>";
                                    echo "<td>₵" . number_format($row['tax_amount'], 2) . "</td>";
                                    echo "<td>₵" . number_format($row['total_amount'], 2) . "</td>";
                                    print_r("<td><span class='status-badge status-" . $statusClass . "'>" . $row['sale_status'] . "</span></td>");
                                    echo "<td>" . date('M d, Y', strtotime($row['sale_date'])) . "</td>";
                                    echo "<td>
                                            <button class='btn btn-sm btn-primary view-details' data-id='" . htmlspecialchars($row['id']) . "'>
                                                <i class='fas fa-eye'></i>
                                            </button>
                                            <button class='btn btn-sm btn-success generate-invoice' data-id='" . htmlspecialchars($row['id']) . "'>
                                                <i class='fas fa-file-invoice'></i>
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>No sales data found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sale Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetails">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <!-- <button type="button" class="btn btn-primary" id="updateStatusBtn">Update Status</button> -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            // const categoryFilter = document.getElementById('categoryFilter');
            const statusFilter = document.getElementById('statusFilter');
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            const exportBtn = document.getElementById('exportBtn');
            const salesRows = document.querySelectorAll('.sale-row');

            // Setup console logger for debugging
            function debugLog(message) {
                console.log(`[DEBUG] ${message}`);
            }

            // Apply filters function
            function applyFilters() {
                const searchTerm = searchInput.value.toLowerCase();
                // const category = categoryFilter.value.toLowerCase();
                const status = statusFilter.value.toLowerCase();

                salesRows.forEach(row => {
                    let showRow = true;

                    // Search term filter
                    if (searchTerm) {
                        const rowText = row.textContent.toLowerCase();
                        if (!rowText.includes(searchTerm)) {
                            showRow = false;
                        }
                    }

                    // Status filter
                    if (status && showRow) {
                        const statusCell = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                        if (!statusCell.includes(status)) {
                            showRow = false;
                        }
                    }

                    // Display row based on filters
                    row.style.display = showRow ? '' : 'none';
                });
            }

            // Event listeners
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', applyFilters);
            }

            // Search on keyup
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        applyFilters();
                    }
                });
            }

            // Setup view details buttons
            document.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', function() {
                    const saleId = this.getAttribute('data-id');
                    // debugLog(`View details clicked for sale ID: ${saleId}`);
                    viewSaleDetails(saleId);
                });
            });

            // Setup generate invoice buttons
            document.querySelectorAll('.generate-invoice').forEach(button => {
                button.addEventListener('click', function() {
                    const saleId = this.getAttribute('data-id');
                    generateInvoice(saleId);
                });
            });

            // Setup export button
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    exportSalesReport();
                });
            }

            /* // Setup update status button
            const updateStatusBtn = document.getElementById('updateStatusBtn');
            if (updateStatusBtn) {
                updateStatusBtn.addEventListener('click', function() {
                    debugLog('Update status button clicked');
                    updateOrderStatus();
                });
            } */
        });

        // View sale details
        function viewSaleDetails(saleId) {
            const detailsContainer = document.getElementById('orderDetails');

            // console.log(`Fetching details for sale ID: ${saleId}`);

            // Show loading spinner
            detailsContainer.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
            modal.show();

            // Create a unique timestamp to prevent caching
            const timestamp = new Date().getTime();

            // Fetch sale details via AJAX
            fetch(`get_sale_details.php?id=${saleId}&_=${timestamp}`)
                .then(response => {
                    // console.log('Response status:', response.status);
                    return response.text();
                })
                .then(text => {
                    // console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        throw new Error(`Failed to parse JSON: ${error.message}\nResponse: ${text}`);
                    }
                })
                .then(data => {
                    console.log('Parsed data:', data);

                    /* // Store debug info if available
                    if (data.debug_info) {
                        console.log('Debug info:', data.debug_info);
                    } */

                    if (data.success) {
                        displaySaleDetails(data.sale, detailsContainer);
                        // Store the sale ID for status update
                        // document.getElementById('updateStatusBtn').setAttribute('data-sale-id', saleId);
                    } else {
                        detailsContainer.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    detailsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <h5>Error loading sale details</h5>
                        </div>
                    `;
                });
        }

        // Display sale details in modal with table format
        function displaySaleDetails(sale, container) {
            // console.log('Displaying sale details:', sale);

            // Handle potentially missing data
            if (!sale) {
                container.innerHTML = `<div class="alert alert-danger">Error: Invalid sale data received</div>`;
                return;
            }

            // Create status options
            let statusOptions = '';
            const currentStatus = (sale.status || '').toLowerCase();
            // console.log('Current status:', currentStatus);

            ['Completed', 'Pending', 'Cancelled'].forEach(status => {
                const selected = status.toLowerCase() === currentStatus ? 'selected' : '';
                statusOptions += `<option value="${status.toLowerCase()}" ${selected}>${status}</option>`;
            });

            // Create sale information table
            const saleInfoTable = `
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Order ID</th>
                                <td>${sale.transaction_number || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>${sale.status}</td>
                            </tr>
                            <tr>
                                <th>Payment Method</th>
                                <td>${sale.payment_method || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>${sale.formatted_date || 'N/A'}</td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>`;

            // Format items in table
            let itemsTable = '';
            if (sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                let itemRows = '';
                let itemTotal = 0;

                // Loop through each item to create rows
                sale.items.forEach((item, index) => {
                    const quantity = parseInt(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    const rowTotal = quantity * price;
                    itemTotal += rowTotal;

                    itemRows += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.product_name || 'Unknown Product'}</td>
                            <td class="text-center">${quantity}</td>
                            <td class="text-center">₵${price.toFixed(2)}</td>
                            <td class="text-center">₵${rowTotal.toFixed(2)}</td>
                        </tr>`;
                });

                itemsTable = `
                    <h6 class="mt-4 mb-3">Items</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemRows}
                            </tbody>
                        </table>
                    </div>`;
            } else {
                itemsTable = `<div class="alert alert-info mt-3">No items found for this sale.</div>`;
            }

            // Summary table
            const summaryTable = `
                <h6 class="mt-4 mb-3">Summary</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Subtotal</th>
                                <td>$${parseFloat(sale.amount || 0).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <th>Tax</th>
                                <td>$${parseFloat(sale.tax_amount || 0).toFixed(2)}</td>
                            </tr>
                            <tr class="table-primary">
                                <th>Total</th>
                                <td><strong>$${parseFloat(sale.total_amount || 0).toFixed(2)}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>`;

            // Format the container HTML
            container.innerHTML = `
                <form id="orderDetailsForm">
                    <div class="order-details">
                        ${saleInfoTable}
                        ${itemsTable}
                        ${summaryTable}
                    </div>
                </form>`;
        }

        // Generate invoice
        function generateInvoice(saleId) {
            console.log(`Generating invoice for sale ID: ${saleId}`);
            window.location.href = `generate_invoice.php?id=${saleId}`;
        }

        // Export sales report
        function exportSalesReport() {
            // Filter criteria
            const searchTerm = document.getElementById('searchInput').value;
            // const category = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;

            console.log('Exporting sales report with filters:', {
                searchTerm,
                // category,
                status
            });

            // Redirect to export script with filters
            window.location.href = `export_sales.php?search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(status)}`;
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
    </script>
</body>

</html>