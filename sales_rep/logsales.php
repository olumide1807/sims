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

// In a real application, you would include database connection and functions here
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log New Sale | Sales Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/css/style.css">

    <?php echo getNotificationDropdownCSS(); ?>
    <style>
        /* Custom styles for sales logging */
        .sale-item-card {
            transition: all 0.2s ease;
            border-left: 3px solid #0d6efd;
        }
        .sale-item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .product-search-container {
            position: relative;
        }
        .search-results {
            position: absolute;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0 0 5px 5px;
            display: none;
        }
        .search-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .search-item:hover {
            background-color: #f8f9fa;
        }
        .quantity-control {
            width: 100px;
        }
        .payment-method-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .payment-method-card:hover {
            background-color: #f8f9fa;
        }
        .payment-method-card.active {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }
        .transaction-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .barcode-scan-btn {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="layout-container">
        <!-- Sidebar (consistent with other sales rep screens) -->
        <div class="sidebar" id="sidebar">
            <div class="logo-section">
                <h4 class="d-flex align-items-center gap-2">
                    <i class="fas fa-cubes text-primary"></i>
                    SIMS Sales
                </h4>
            </div>

            <nav>
                <a href="../dashboard/" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>

                <a href="../inventory/" class="nav-link">
                    <i class="fas fa-box"></i> View Inventory
                </a>

                <a href="#" class="nav-link active" onclick="toggleSubmenu('sales')">
                    <i class="fas fa-shopping-cart"></i> Sales
                </a>
                <div class="submenu show" id="sales">
                    <a href="logsales.php" class="nav-link active"><i class="fas fa-cash-register"></i> New Sale</a>
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

            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>New Sale Transaction</h3>
                <div>
                    <button class="btn btn-outline-secondary me-2" id="hold-sale-btn">
                        <i class="fas fa-pause me-2"></i>Hold Sale
                    </button>
                    <button class="btn btn-outline-danger" id="cancel-sale-btn">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="row">
                <!-- Left Column - Product Entry -->
                <div class="col-lg-8">
                    <!-- Product Search -->
                    <div class="content-card mb-4">
                        <h5 class="mb-3">Add Products</h5>
                        <div class="product-search-container mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Scan barcode or search products..." 
                                       id="product-search" autocomplete="off" autofocus>
                                <button class="btn btn-outline-secondary barcode-scan-btn" type="button">
                                    <i class="fas fa-barcode"></i>
                                </button>
                            </div>
                            <div class="search-results" id="search-results"></div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Tip: Press F2 to focus on search, or scan barcode to add items quickly.
                        </div>
                    </div>

                    <!-- Sale Items List -->
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Sale Items</h5>
                            <span class="status-badge bg-primary rounded-pill" id="item-count">0 items</span>
                        </div>
                        
                        <div id="sale-items-container">
                            <!-- Sale items will be added here -->
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-shopping-basket fa-2x mb-3"></i>
                                <p>No items added yet. Search for products to begin.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Transaction Summary -->
                <div class="col-lg-4">
                    <div class="content-card transaction-summary sticky-top" style="top: 20px;">
                        <h5 class="mb-3">Transaction Summary</h5>
                        
                        <!-- Customer Selection -->
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select class="form-select" id="customer-select">
                                <option value="0">Walk-in Customer</option>
                                <option value="1">John Smith (Member #1001)</option>
                                <option value="2">Sarah Johnson (Member #1002)</option>
                                <option value="3">Michael Brown (Member #1003)</option>
                            </select>
                            <div class="text-end mt-1">
                                <button class="btn btn-sm btn-link p-0" id="new-customer-btn">+ New Customer</button>
                            </div>
                        </div>
                        
                        <!-- Summary Details -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Subtotal:</span>
                                <span id="subtotal-amount">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Tax (7%):</span>
                                <span id="tax-amount">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Discount:</span>
                                <span id="discount-amount">-$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total:</span>
                                <span id="total-amount">$0.00</span>
                            </div>
                        </div>
                        
                        <!-- Discount Application -->
                        <div class="mb-3">
                            <label class="form-label">Apply Discount</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Amount or %" id="discount-input">
                                <button class="btn btn-outline-secondary" type="button" id="apply-discount-btn">Apply</button>
                            </div>
                        </div>
                        
                        <!-- Payment Methods -->
                        <div class="mb-4">
                            <label class="form-label">Payment Method</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="payment-method-card p-3 border rounded active" data-method="cash">
                                        <div class="text-center">
                                            <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                            <p class="mb-0">Cash</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="payment-method-card p-3 border rounded" data-method="card">
                                        <div class="text-center">
                                            <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                            <p class="mb-0">Card</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="payment-method-card p-3 border rounded" data-method="mobile">
                                        <div class="text-center">
                                            <i class="fas fa-mobile-alt fa-2x text-info mb-2"></i>
                                            <p class="mb-0">Mobile Pay</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="payment-method-card p-3 border rounded" data-method="other">
                                        <div class="text-center">
                                            <i class="fas fa-wallet fa-2x text-warning mb-2"></i>
                                            <p class="mb-0">Other</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Complete Sale Button -->
                        <div class="d-grid">
                            <button class="btn btn-primary btn-lg" id="complete-sale-btn" disabled>
                                <i class="fas fa-check-circle me-2"></i>Complete Sale ($0.00)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Customer Modal -->
    <div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCustomerModalLabel">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="new-customer-form">
                        <div class="mb-3">
                            <label for="customer-name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="customer-name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer-phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="customer-phone">
                        </div>
                        <div class="mb-3">
                            <label for="customer-email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="customer-email">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-customer-btn">Save Customer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Processing Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cash-payment" class="payment-method-content">
                        <div class="mb-3">
                            <label class="form-label">Amount Received</label>
                            <input type="number" class="form-control" id="amount-received" step="0.01" min="0">
                        </div>
                        <div class="alert alert-info">
                            <strong>Change Due:</strong> <span id="change-due">$0.00</span>
                        </div>
                    </div>
                    
                    <div id="card-payment" class="payment-method-content" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" placeholder="MM/YY">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" placeholder="123">
                            </div>
                        </div>
                    </div>
                    
                    <div id="mobile-payment" class="payment-method-content" style="display: none;">
                        <div class="text-center py-3">
                            <i class="fas fa-qrcode fa-4x mb-3 text-info"></i>
                            <p>Scan the QR code with your mobile payment app</p>
                            <img src="../images/qr-placeholder.png" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                        </div>
                    </div>
                    
                    <div id="other-payment" class="payment-method-content" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Payment Notes</label>
                            <textarea class="form-control" rows="3" placeholder="Enter payment details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirm-payment-btn">
                        <i class="fas fa-check me-2"></i>Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Sale Completed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4>Transaction Complete</h4>
                        <p class="text-muted">Receipt #<span id="receipt-number">10025</span></p>
                    </div>
                    
                    <div class="receipt-container bg-light p-3 mb-3">
                        <div class="text-center mb-3">
                            <h5>Your Pharmacy Name</h5>
                            <p class="small text-muted mb-0">123 Main St, City</p>
                            <p class="small text-muted">Tel: (123) 456-7890</p>
                        </div>
                        
                        <div class="receipt-details">
                            <div class="d-flex justify-content-between small">
                                <span>Date:</span>
                                <span id="receipt-date"><?php echo date('m/d/Y H:i'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Cashier:</span>
                                <span><?php echo htmlspecialchars($_SESSION['firstname']); ?></span>
                            </div>
                            <hr class="my-2">
                            
                            <div id="receipt-items">
                                <!-- Items will be added here -->
                            </div>
                            
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="receipt-subtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tax:</span>
                                <span id="receipt-tax">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Discount:</span>
                                <span id="receipt-discount">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span id="receipt-total">$0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span>Payment Method:</span>
                                <span id="receipt-payment-method">Cash</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Amount Received:</span>
                                <span id="receipt-amount-received">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Change:</span>
                                <span id="receipt-change">$0.00</span>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p class="small text-muted">Thank you for your purchase!</p>
                            <p class="small text-muted mb-0"><?php echo date('m/d/Y H:i'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="print-receipt-btn">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <?php echo getNotificationDropdownJS(); ?>
    <script>
        // DOM Elements
        const productSearch = document.getElementById('product-search');
        const searchResults = document.getElementById('search-results');
        const saleItemsContainer = document.getElementById('sale-items-container');
        const itemCount = document.getElementById('item-count');
        const subtotalAmount = document.getElementById('subtotal-amount');
        const taxAmount = document.getElementById('tax-amount');
        const discountAmount = document.getElementById('discount-amount');
        const totalAmount = document.getElementById('total-amount');
        const completeSaleBtn = document.getElementById('complete-sale-btn');
        const paymentMethodCards = document.querySelectorAll('.payment-method-card');
        const discountInput = document.getElementById('discount-input');
        const applyDiscountBtn = document.getElementById('apply-discount-btn');
        const amountReceived = document.getElementById('amount-received');
        const changeDue = document.getElementById('change-due');
        
        // Modals
        const newCustomerModal = new bootstrap.Modal('#newCustomerModal');
        const paymentModal = new bootstrap.Modal('#paymentModal');
        const receiptModal = new bootstrap.Modal('#receiptModal');
        
        // Sample product data (in real app, this would come from API)
        const sampleProducts = [
            { id: 1, name: "Paracetamol 500mg (100 tablets)", price: 8.99, stock: 42, barcode: "123456789012" },
            { id: 2, name: "Ibuprofen 200mg (30 tablets)", price: 5.49, stock: 5, barcode: "234567890123" },
            { id: 3, name: "Amoxicillin 250mg Capsules", price: 12.99, stock: 18, barcode: "345678901234" },
            { id: 4, name: "Cetirizine Hydrochloride 10mg", price: 6.99, stock: 0, barcode: "456789012345" },
            { id: 5, name: "Vitamin C 1000mg Tablets", price: 9.99, stock: 24, barcode: "567890123456" },
            { id: 6, name: "Multivitamin Complex", price: 14.99, stock: 15, barcode: "678901234567" },
            { id: 7, name: "Bandages (Pack of 10)", price: 3.99, stock: 32, barcode: "789012345678" }
        ];
        
        // Sale data
        let saleItems = [];
        let selectedPaymentMethod = 'cash';
        let currentDiscount = 0;
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Set up keyboard shortcut (F2 to focus on search)
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F2') {
                    e.preventDefault();
                    productSearch.focus();
                }
            });
            
            // Barcode scanner button
            document.querySelector('.barcode-scan-btn').addEventListener('click', function() {
                alert("Barcode scanner would activate here. For demo, enter a barcode manually.");
            });
            
            // New customer button
            document.getElementById('new-customer-btn').addEventListener('click', function() {
                newCustomerModal.show();
            });
            
            // Save customer button
            document.getElementById('save-customer-btn').addEventListener('click', function() {
                // In a real app, this would save to database
                alert("Customer saved successfully!");
                newCustomerModal.hide();
            });
            
            // Cancel sale button
            document.getElementById('cancel-sale-btn').addEventListener('click', function() {
                if (confirm("Are you sure you want to cancel this sale? All items will be removed.")) {
                    resetSale();
                }
            });
            
            // Hold sale button
            document.getElementById('hold-sale-btn').addEventListener('click', function() {
                if (saleItems.length > 0) {
                    alert("Sale held successfully. You can retrieve it from the sales history later.");
                    resetSale();
                } else {
                    alert("No items to hold. Please add items first.");
                }
            });
            
            // Apply discount button
            applyDiscountBtn.addEventListener('click', applyDiscount);
            
            // Payment method selection
            paymentMethodCards.forEach(card => {
                card.addEventListener('click', function() {
                    paymentMethodCards.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    selectedPaymentMethod = this.dataset.method;
                });
            });
            
            // Complete sale button
            completeSaleBtn.addEventListener('click', function() {
                if (saleItems.length === 0) {
                    alert("Please add items to the sale first.");
                    return;
                }
                paymentModal.show();
            });
            
            // Amount received calculation
            amountReceived.addEventListener('input', calculateChange);
            
            // Confirm payment button
            document.getElementById('confirm-payment-btn').addEventListener('click', function() {
                const total = parseFloat(document.getElementById('total-amount').textContent.replace('$', ''));
                const received = parseFloat(amountReceived.value) || 0;
                
                if (selectedPaymentMethod === 'cash' && received < total) {
                    alert("Amount received cannot be less than the total amount.");
                    return;
                }
                
                processPayment();
            });
            
            // Print receipt button
            document.getElementById('print-receipt-btn').addEventListener('click', function() {
                window.print();
            });
        });
        
        // Product search functionality
        productSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            if (searchTerm.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            const filteredProducts = sampleProducts.filter(product => 
                product.name.toLowerCase().includes(searchTerm) ||
                product.barcode.includes(searchTerm)
            );
            
            displaySearchResults(filteredProducts);
        });
        
        // Display search results
        function displaySearchResults(products) {
            if (products.length === 0) {
                searchResults.innerHTML = '<div class="search-item">No products found</div>';
                searchResults.style.display = 'block';
                return;
            }
            
            searchResults.innerHTML = '';
            products.forEach(product => {
                const item = document.createElement('div');
                item.className = 'search-item';
                item.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <strong>${product.name}</strong>
                        <span>$${product.price.toFixed(2)}</span>
                    </div>
                    <small class="text-muted">SKU: ${product.barcode}</small>
                `;
                item.addEventListener('click', function() {
                    addToSale(product);
                    productSearch.value = '';
                    searchResults.style.display = 'none';
                    productSearch.focus();
                });
                searchResults.appendChild(item);
            });
            
            searchResults.style.display = 'block';
        }
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!productSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
        
        // Add product to sale
        function addToSale(product) {
            // Check if product already exists in sale
            const existingItem = saleItems.find(item => item.id === product.id);
            
            if (existingItem) {
                // Increase quantity if already in sale
                existingItem.quantity += 1;
            } else {
                // Add new item to sale
                saleItems.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1,
                    stock: product.stock
                });
            }
            
            updateSaleDisplay();
        }
        
        // Update sale display
        function updateSaleDisplay() {
            if (saleItems.length === 0) {
                saleItemsContainer.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-shopping-basket fa-2x mb-3"></i>
                        <p>No items added yet. Search for products to begin.</p>
                    </div>
                `;
                itemCount.textContent = '0 items';
                completeSaleBtn.disabled = true;
                updateTotals();
                return;
            }
            
            saleItemsContainer.innerHTML = '';
            saleItems.forEach((item, index) => {
                const itemCard = document.createElement('div');
                itemCard.className = 'sale-item-card p-3 mb-2';
                itemCard.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">$${item.price.toFixed(2)} each</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="input-group quantity-control">
                                <button class="btn btn-outline-secondary" type="button" onclick="adjustQuantity(${index}, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="text" class="form-control text-center" value="${item.quantity}" 
                                       onchange="updateQuantity(${index}, this.value)">
                                <button class="btn btn-outline-secondary" type="button" onclick="adjustQuantity(${index}, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <h5 class="ms-3 mb-0" style="min-width: 80px; text-align: right;">
                                $${(item.price * item.quantity).toFixed(2)}
                            </h5>
                            <button class="btn btn-link text-danger ms-2" onclick="removeItem(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                saleItemsContainer.appendChild(itemCard);
            });
            
            itemCount.textContent = `${saleItems.reduce((sum, item) => sum + item.quantity, 0)} items`;
            completeSaleBtn.disabled = false;
            updateTotals();
        }
        
        // Adjust item quantity
        function adjustQuantity(index, change) {
            const newQuantity = saleItems[index].quantity + change;
            
            if (newQuantity < 1) {
                removeItem(index);
                return;
            }
            
            if (newQuantity > saleItems[index].stock) {
                alert(`Only ${saleItems[index].stock} units available in stock.`);
                return;
            }
            
            saleItems[index].quantity = newQuantity;
            updateSaleDisplay();
        }
        
        // Update item quantity
        function updateQuantity(index, value) {
            const quantity = parseInt(value) || 1;
            
            if (quantity < 1) {
                removeItem(index);
                return;
            }
            
            if (quantity > saleItems[index].stock) {
                alert(`Only ${saleItems[index].stock} units available in stock.`);
                saleItems[index].quantity = 1;
                updateSaleDisplay();
                return;
            }
            
            saleItems[index].quantity = quantity;
            updateSaleDisplay();
        }
        
        // Remove item from sale
        function removeItem(index) {
            saleItems.splice(index, 1);
            updateSaleDisplay();
        }
        
        // Update totals
        function updateTotals() {
            const subtotal = saleItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.07; // 7% tax for example
            const total = subtotal + tax - currentDiscount;
            
            subtotalAmount.textContent = `$${subtotal.toFixed(2)}`;
            taxAmount.textContent = `$${tax.toFixed(2)}`;
            discountAmount.textContent = `-$${currentDiscount.toFixed(2)}`;
            totalAmount.textContent = `$${total.toFixed(2)}`;
            
            completeSaleBtn.textContent = `Complete Sale ($${total.toFixed(2)})`;
        }
        
        // Apply discount
        function applyDiscount() {
            const discountValue = discountInput.value.trim();
            
            if (!discountValue) {
                currentDiscount = 0;
                updateTotals();
                return;
            }
            
            if (discountValue.endsWith('%')) {
                // Percentage discount
                const percent = parseFloat(discountValue) || 0;
                const subtotal = parseFloat(subtotalAmount.textContent.replace('$', ''));
                currentDiscount = subtotal * (percent / 100);
            } else {
                // Fixed amount discount
                currentDiscount = parseFloat(discountValue) || 0;
            }
            
            updateTotals();
            discountInput.value = '';
        }
        
        // Calculate change
        function calculateChange() {
            const total = parseFloat(document.getElementById('total-amount').textContent.replace('$', ''));
            const received = parseFloat(amountReceived.value) || 0;
            const change = received - total;
            
            changeDue.textContent = `$${Math.max(0, change).toFixed(2)}`;
        }
        
        // Process payment
        function processPayment() {
            // In a real app, this would process payment with your payment gateway
            // For demo, we'll just show the receipt
            
            // Generate receipt content
            const receiptItems = document.getElementById('receipt-items');
            receiptItems.innerHTML = '';
            
            saleItems.forEach(item => {
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between small mb-1';
                row.innerHTML = `
                    <span>${item.name} x${item.quantity}</span>
                    <span>$${(item.price * item.quantity).toFixed(2)}</span>
                `;
                receiptItems.appendChild(row);
            });
            
            // Set receipt values
            document.getElementById('receipt-subtotal').textContent = subtotalAmount.textContent;
            document.getElementById('receipt-tax').textContent = taxAmount.textContent;
            document.getElementById('receipt-discount').textContent = `-${discountAmount.textContent}`;
            document.getElementById('receipt-total').textContent = totalAmount.textContent;
            document.getElementById('receipt-payment-method').textContent = selectedPaymentMethod.charAt(0).toUpperCase() + selectedPaymentMethod.slice(1);
            
            if (selectedPaymentMethod === 'cash') {
                document.getElementById('receipt-amount-received').textContent = `$${parseFloat(amountReceived.value).toFixed(2)}`;
                document.getElementById('receipt-change').textContent = changeDue.textContent;
            } else {
                document.getElementById('receipt-amount-received').textContent = totalAmount.textContent;
                document.getElementById('receipt-change').textContent = '$0.00';
            }
            
            // Show receipt
            paymentModal.hide();
            receiptModal.show();
            
            // Reset sale after showing receipt
            setTimeout(resetSale, 100);
        }
        
        // Reset sale
        function resetSale() {
            saleItems = [];
            currentDiscount = 0;
            discountInput.value = '';
            selectedPaymentMethod = 'cash';
            paymentMethodCards.forEach(card => card.classList.remove('active'));
            document.querySelector('.payment-method-card[data-method="cash"]').classList.add('active');
            amountReceived.value = '';
            changeDue.textContent = '$0.00';
            updateSaleDisplay();
            productSearch.focus();
        }
        
        // Sidebar functions
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