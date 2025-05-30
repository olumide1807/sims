<?php
// This file generates an invoice for a specific sale
session_start();
include "../../config/session_check.php";
include "../../config/config.php";

// Check if sale ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Sale ID is required");
}

$saleId = mysqli_real_escape_string($connect, $_GET['id']);

// Get sale information
$saleQuery = "SELECT 
                s.id,
                s.transaction_number, 
                s.subtotal,
                s.tax_amount,
                s.total_amount,
                s.sale_status,
                s.sale_date
              FROM 
                sales s
              WHERE 
                s.id = '$saleId'";

$saleResult = mysqli_query($connect, $saleQuery);

if (!$saleResult || mysqli_num_rows($saleResult) == 0) {
    die("Error: Sale not found");
}

$sale = mysqli_fetch_assoc($saleResult);

// Get sale items
$itemsQuery = "SELECT 
                si.quantity,
                p.product_name,
                si.unit_price,
                p.category
              FROM 
                sale_details si
              JOIN 
                products p ON si.product_id = p.id
              WHERE 
                si.sale_id = '$saleId'";

$itemsResult = mysqli_query($connect, $itemsQuery);
$saleItems = [];

if ($itemsResult && mysqli_num_rows($itemsResult) > 0) {
    while ($item = mysqli_fetch_assoc($itemsResult)) {
        $saleItems[] = $item;
    }
}

/* // Get company info from settings table (if available)
$companyQuery = "SELECT * FROM settings WHERE setting_key IN ('company_name', 'company_address', 'company_phone', 'company_email', 'company_website')";
$companyResult = mysqli_query($connect, $companyQuery);
$companyInfo = [];

if ($companyResult && mysqli_num_rows($companyResult) > 0) {
    while ($row = mysqli_fetch_assoc($companyResult)) {
        $companyInfo[$row['setting_key']] = $row['setting_value'];
    }
}

// Log the invoice generation
$userId = $_SESSION['user_id']; // Assuming user ID is stored in session
$logQuery = "INSERT INTO activity_logs (user_id, activity_type, related_id, details, created_at) 
            VALUES ('$userId', 'invoice', '$saleId', 'Generated invoice for sale', NOW())";
mysqli_query($connect, $logQuery); */

// Set default company info if not found in database
$companyName = $companyInfo['company_name'] ?? 'SIMS - Shop Inventory Management System';
$companyAddress = $companyInfo['company_address'] ?? '123 Main Street, City, Country';
$companyPhone = $companyInfo['company_phone'] ?? '+1 (555) 123-4567';
$companyEmail = $companyInfo['company_email'] ?? 'info@example.com';
$companyWebsite = $companyInfo['company_website'] ?? 'www.example.com';

// Format date
$formattedDate = date('F d, Y', strtotime($sale['sale_date']));
$invoiceNumber = 'INV-' . strtoupper(substr($saleId, 0, 8));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $invoiceNumber; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
        }
        .invoice-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .invoice-header img {
            max-width: 200px;
        }
        .invoice-title {
            font-size: 28px;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .invoice-details-col {
            max-width: 45%;
        }
        .invoice-details-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 16px;
        }
        .invoice-body {
            margin-bottom: 30px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .invoice-total {
            text-align: right;
            margin-top: 20px;
        }
        .invoice-total table {
            width: 300px;
            margin-left: auto;
        }
        .invoice-total table td {
            padding: 5px 0;
        }
        .total-row {
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #eee;
        }
        .invoice-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .invoice-footer p {
            margin-bottom: 5px;
        }
        .text-right {
            text-align: right;
        }
        .print-button {
            margin-bottom: 20px;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-button text-end">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            <button onclick="window.history.back()" class="btn btn-secondary ms-2">
                Go Back
            </button>
        </div>
        
        <div class="invoice-container">
            <div class="invoice-header">
                <div class="row">
                    <div class="col-6">
                        <div class="invoice-title"><?php echo htmlspecialchars($companyName); ?></div><br>
                        <div><?php echo htmlspecialchars($companyAddress); ?></div>
                        <div>Phone: <?php echo htmlspecialchars($companyPhone); ?></div>
                        <div>Email: <?php echo htmlspecialchars($companyEmail); ?></div>
                        <div>Website: <?php echo htmlspecialchars($companyWebsite); ?></div>
                    </div>
                    <div class="col-6 text-end">
                        <h1 class="invoice-title">INVOICE</h1>
                        <div><strong>Invoice #:</strong> <?php echo $invoiceNumber; ?></div>
                        <div><strong>Date:</strong> <?php echo $formattedDate; ?></div>
                        <div><strong>Status:</strong> <?php echo ucfirst($sale['sale_status']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="invoice-details">
                <div class="invoice-details-col">
                    <div class="invoice-details-title">Billed To:</div>
                    <div><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?></div>
                    <?php if(!empty($sale['address'])): ?>
                    <div><?php echo htmlspecialchars($sale['address']); ?></div>
                    <?php endif; ?>
                    <?php if(!empty($sale['phone'])): ?>
                    <div>Phone: <?php echo htmlspecialchars($sale['phone']); ?></div>
                    <?php endif; ?>
                    <?php if(!empty($sale['email'])): ?>
                    <div>Email: <?php echo htmlspecialchars($sale['email']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="invoice-details-col text-end">
                    <div class="invoice-details-title">Payment Information:</div>
                    <div><strong>Order ID:</strong> <?php echo htmlspecialchars($sale['transaction_number']); ?></div>
                    <div><strong>Payment Status:</strong> <?php echo ucfirst($sale['sale_status']); ?></div>
                </div>
            </div>
            
            <div class="invoice-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($saleItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-right">₵<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="text-right">₵<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="invoice-total">
                    <table>
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right">₵<?php echo number_format($sale['subtotal'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Tax:</td>
                            <td class="text-right">₵<?php echo number_format($sale['tax_amount'], 2); ?></td>
                        </tr>
                        <tr class="total-row">
                            <td>Total:</td>
                            <td class="text-right">₵<?php echo number_format($sale['total_amount'], 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="invoice-footer">
                <p>Thank you for your business!</p>
                <p>This invoice was generated automatically by SIMS - Shop Inventory Management System.</p>
                <p>If you have any questions, please contact us at <?php echo htmlspecialchars($companyEmail); ?></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</body>
</html>