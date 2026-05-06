<?php
$conn = mysqli_connect("localhost","root","","shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $order_id = intval($_POST['order_id']);
                $new_status = mysqli_real_escape_string($conn, $_POST['order_status']);
                
                $sql = "UPDATE orders SET order_status = '$new_status', updated_at = CURRENT_TIMESTAMP WHERE order_id = $order_id";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Order status updated successfully!";
                } else {
                    $error_message = "Error updating order status: " . mysqli_error($conn);
                }
                break;
                
            case 'update_payment':
                $order_id = intval($_POST['order_id']);
                $payment_status = mysqli_real_escape_string($conn, $_POST['payment_status']);
                
                $sql = "UPDATE orders SET payment_status = '$payment_status', updated_at = CURRENT_TIMESTAMP WHERE order_id = $order_id";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Payment status updated successfully!";
                } else {
                    $error_message = "Error updating payment status: " . mysqli_error($conn);
                }
                break;
                
            case 'add_tracking':
                $order_id = intval($_POST['order_id']);
                $tracking_number = mysqli_real_escape_string($conn, $_POST['tracking_number']);
                
                $sql = "UPDATE orders SET tracking_number = '$tracking_number', updated_at = CURRENT_TIMESTAMP WHERE order_id = $order_id";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Tracking number added successfully!";
                } else {
                    $error_message = "Error adding tracking number: " . mysqli_error($conn);
                }
                break;
                
            case 'bulk_update':
                if (isset($_POST['selected_orders']) && isset($_POST['bulk_status'])) {
                    $order_ids = $_POST['selected_orders'];
                    $new_status = mysqli_real_escape_string($conn, $_POST['bulk_status']);
                    $updated_count = 0;
                    
                    foreach ($order_ids as $order_id) {
                        $order_id = intval($order_id);
                        $sql = "UPDATE orders SET order_status = '$new_status', updated_at = CURRENT_TIMESTAMP WHERE order_id = $order_id";
                        if (mysqli_query($conn, $sql)) {
                            $updated_count++;
                        }
                    }
                    
                    $success_message = "Successfully updated $updated_count orders to $new_status status!";
                }
                break;
                
            case 'delete_order':
                $order_id = intval($_POST['order_id']);
                
                // First, delete related order items if they exist
                $delete_items_sql = "DELETE FROM order_items WHERE order_id = $order_id";
                mysqli_query($conn, $delete_items_sql);
                
                // Then delete the order
                $delete_order_sql = "DELETE FROM orders WHERE order_id = $order_id";
                
                if (mysqli_query($conn, $delete_order_sql)) {
                    $success_message = "Order deleted successfully!";
                } else {
                    $error_message = "Error deleting order: " . mysqli_error($conn);
                }
                break;
        }
    }
}

// Handle GET actions (Export and Reports)
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'export':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Order ID', 'Order Number', 'Customer ID', 'Order Date', 'Status', 'Payment Status', 'Total Amount', 'Tracking Number']);
            
            $export_sql = "SELECT order_id, order_number, user_id, order_date, order_status, payment_status, final_amount, tracking_number FROM orders ORDER BY order_date DESC";
            $export_result = mysqli_query($conn, $export_sql);
            
            while ($row = mysqli_fetch_assoc($export_result)) {
                fputcsv($output, $row);
            }
            
            fclose($output);
            exit;
            break;
            
        case 'report':
            $report_type = $_GET['type'] ?? 'daily';
            
            header('Content-Type: text/html');
            echo "<h2>Order Report - " . ucfirst($report_type) . "</h2>";
            
            switch ($report_type) {
                case 'daily':
                    $report_sql = "SELECT DATE(order_date) as date, COUNT(*) as orders, SUM(final_amount) as revenue FROM orders WHERE DATE(order_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(order_date) ORDER BY date DESC";
                    break;
                case 'weekly':
                    $report_sql = "SELECT YEAR(order_date) as year, WEEK(order_date) as week, COUNT(*) as orders, SUM(final_amount) as revenue FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK) GROUP BY YEAR(order_date), WEEK(order_date) ORDER BY year DESC, week DESC";
                    break;
                case 'monthly':
                    $report_sql = "SELECT YEAR(order_date) as year, MONTH(order_date) as month, COUNT(*) as orders, SUM(final_amount) as revenue FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY YEAR(order_date), MONTH(order_date) ORDER BY year DESC, month DESC";
                    break;
                case 'status':
                    $report_sql = "SELECT order_status, COUNT(*) as orders, SUM(final_amount) as revenue FROM orders GROUP BY order_status";
                    break;
                case 'payment':
                    $report_sql = "SELECT payment_method, COUNT(*) as orders, SUM(final_amount) as revenue FROM orders GROUP BY payment_method";
                    break;
            }
            
            $report_result = mysqli_query($conn, $report_sql);
            echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
            
            if ($report_result && mysqli_num_rows($report_result) > 0) {
                $first_row = true;
                while ($row = mysqli_fetch_assoc($report_result)) {
                    if ($first_row) {
                        echo "<tr>";
                        foreach (array_keys($row) as $header) {
                            echo "<th>" . ucfirst(str_replace('_', ' ', $header)) . "</th>";
                        }
                        echo "</tr>";
                        $first_row = false;
                    }
                    
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='100%'>No data found</td></tr>";
            }
            
            echo "</table>";
            echo "<br><button onclick='window.print()'>Print Report</button>";
            echo "<script>window.focus();</script>";
            exit;
            break;
            
        case 'view_details':
            $order_id = intval($_GET['order_id']);
            
            // Get order details with customer information
            $order_sql = "SELECT o.*, 
                         CONCAT('Customer ID: ', o.user_id) as customer_name,
                         'Contact admin for details' as customer_email
                         FROM orders o 
                         WHERE o.order_id = $order_id";
            $order_result = mysqli_query($conn, $order_sql);
            $order = mysqli_fetch_assoc($order_result);
            
            if (!$order) {
                echo "<h2>Order not found</h2>";
                exit;
            }
            
            // Get order items if they exist
            $items_sql = "SELECT * FROM order_items WHERE order_id = $order_id";
            $items_result = mysqli_query($conn, $items_sql);
            
            echo "<!DOCTYPE html><html><head><title>Order Details - {$order['order_number']}</title>";
            echo "<style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .order-header { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .detail-section { margin-bottom: 20px; }
                .detail-section h3 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #f2f2f2; }
                .status { padding: 5px 10px; border-radius: 15px; color: white; }
                .status-pending { background: #ffc107; color: #212529; }
                .status-processing { background: #17a2b8; }
                .status-shipped { background: #6f42c1; }
                .status-delivered { background: #28a745; }
                .status-cancelled { background: #dc3545; }
                .print-btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; }
            </style></head><body>";
            
            echo "<div class='order-header'>";
            echo "<h1>Order Details: {$order['order_number']}</h1>";
            echo "<p><strong>Order ID:</strong> #{$order['order_id']}</p>";
            echo "<p><strong>Order Date:</strong> " . date('F j, Y g:i A', strtotime($order['order_date'])) . "</p>";
            echo "</div>";
            
            echo "<div class='detail-section'>";
            echo "<h3>Customer Information</h3>";
            echo "<table>";
            echo "<tr><td><strong>Customer:</strong></td><td>{$order['customer_name']}</td></tr>";
            echo "<tr><td><strong>Email:</strong></td><td>{$order['customer_email']}</td></tr>";
            if (isset($order['shipping_address'])) {
                echo "<tr><td><strong>Shipping Address:</strong></td><td>{$order['shipping_address']}</td></tr>";
            }
            echo "</table>";
            echo "</div>";
            
            echo "<div class='detail-section'>";
            echo "<h3>Order Information</h3>";
            echo "<table>";
            echo "<tr><td><strong>Status:</strong></td><td><span class='status status-{$order['order_status']}'>" . ucfirst($order['order_status']) . "</span></td></tr>";
            echo "<tr><td><strong>Payment Status:</strong></td><td>" . ucfirst($order['payment_status']) . "</td></tr>";
            echo "<tr><td><strong>Payment Method:</strong></td><td>" . ($order['payment_method'] ?: 'Not specified') . "</td></tr>";
            echo "<tr><td><strong>Total Amount:</strong></td><td>₹" . number_format($order['final_amount'], 2) . "</td></tr>";
            if ($order['tracking_number']) {
                echo "<tr><td><strong>Tracking Number:</strong></td><td>{$order['tracking_number']}</td></tr>";
            }
            echo "</table>";
            echo "</div>";
            
            if ($items_result && mysqli_num_rows($items_result) > 0) {
                echo "<div class='detail-section'>";
                echo "<h3>Order Items</h3>";
                echo "<table>";
                echo "<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
                while ($item = mysqli_fetch_assoc($items_result)) {
                    echo "<tr>";
                    $product_name = $item['product_name'] ?? ($item['name'] ?? 'Unknown Product');
                    $quantity = $item['quantity'] ?? 1;
                    $price = $item['price'] ?? ($item['unit_price'] ?? ($item['product_price'] ?? 0));
                    
                    echo "<td>{$product_name}</td>";
                    echo "<td>{$quantity}</td>";
                    echo "<td>₹" . number_format($price, 2) . "</td>";
                    echo "<td>₹" . number_format($quantity * $price, 2) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "</div>";
            }
            
            echo "<button class='print-btn' onclick='window.print()'>Print Details</button>";
            echo "<button class='print-btn' onclick='window.close()' style='background: #6c757d; margin-left: 10px;'>Close</button>";
            echo "</body></html>";
            exit;
            break;
            
        case 'generate_invoice':
            $order_id = intval($_GET['order_id']);
            
            // Get order details
            $order_sql = "SELECT o.*, 
                         CONCAT('Customer ID: ', o.user_id) as customer_name,
                         'Contact admin for details' as customer_email
                         FROM orders o 
                         WHERE o.order_id = $order_id";
            $order_result = mysqli_query($conn, $order_sql);
            $order = mysqli_fetch_assoc($order_result);
            
            if (!$order) {
                echo "Order not found";
                exit;
            }
            
            // Generate PDF-style invoice
            header('Content-Type: text/html');
            echo "<!DOCTYPE html><html><head><title>Invoice - {$order['order_number']}</title>";
            echo "<style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .invoice-header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
                .company-name { font-size: 28px; font-weight: bold; color: #007bff; }
                .invoice-title { font-size: 24px; margin: 10px 0; }
                .invoice-details { display: flex; justify-content: space-between; margin-bottom: 30px; }
                .invoice-section { width: 45%; }
                .invoice-section h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .total-section { text-align: right; margin-top: 20px; font-size: 18px; }
                .total-amount { font-size: 24px; font-weight: bold; color: #007bff; }
                .invoice-footer { margin-top: 40px; text-align: center; color: #666; }
                @media print { .no-print { display: none; } }
            </style></head><body>";
            
            echo "<div class='invoice-header'>";
            echo "<div class='company-name'>ShopSphere</div>";
            echo "<div class='invoice-title'>INVOICE</div>";
            echo "<p>Invoice #: INV-{$order['order_number']}</p>";
            echo "</div>";
            
            echo "<div class='invoice-details'>";
            echo "<div class='invoice-section'>";
            echo "<h3>Bill To:</h3>";
            echo "<p><strong>{$order['customer_name']}</strong><br>";
            echo "{$order['customer_email']}<br>";
            if (isset($order['shipping_address'])) {
                echo $order['shipping_address'];
            }
            echo "</p>";
            echo "</div>";
            
            echo "<div class='invoice-section'>";
            echo "<h3>Invoice Details:</h3>";
            echo "<p><strong>Invoice Date:</strong> " . date('F j, Y') . "<br>";
            echo "<strong>Order Date:</strong> " . date('F j, Y', strtotime($order['order_date'])) . "<br>";
            echo "<strong>Payment Method:</strong> " . ($order['payment_method'] ?: 'Not specified') . "<br>";
            echo "<strong>Order Status:</strong> " . ucfirst($order['order_status']) . "</p>";
            echo "</div>";
            echo "</div>";
            
            echo "<table>";
            echo "<tr><th>Description</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>";
            
            // Get order items if available
            $items_sql = "SELECT * FROM order_items WHERE order_id = $order_id";
            $items_result = mysqli_query($conn, $items_sql);
            
            if ($items_result && mysqli_num_rows($items_result) > 0) {
                while ($item = mysqli_fetch_assoc($items_result)) {
                    $product_name = $item['product_name'] ?? ($item['name'] ?? 'Unknown Product');
                    $quantity = $item['quantity'] ?? 1;
                    $price = $item['price'] ?? ($item['unit_price'] ?? ($item['product_price'] ?? 0));
                    
                    echo "<tr>";
                    echo "<td>{$product_name}</td>";
                    echo "<td>{$quantity}</td>";
                    echo "<td>₹" . number_format($price, 2) . "</td>";
                    echo "<td>₹" . number_format($quantity * $price, 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Order items not available</td></tr>";
            }
            
            echo "</table>";
            
            echo "<div class='total-section'>";
            echo "<p><strong>Subtotal: ₹" . number_format($order['final_amount'], 2) . "</strong></p>";
            echo "<p><strong>Tax: ₹0.00</strong></p>";
            echo "<p class='total-amount'><strong>Total Amount: ₹" . number_format($order['final_amount'], 2) . "</strong></p>";
            echo "</div>";
            
            echo "<div class='invoice-footer'>";
            echo "<p>Thank you for your business!</p>";
            echo "<p>For any queries, please contact our customer support.</p>";
            echo "</div>";
            
            echo "<div class='no-print' style='text-align: center; margin-top: 30px;'>";
            echo "<button onclick='window.print()' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;'>Print Invoice</button>";
            echo "<button onclick='window.close()' style='background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Close</button>";
            echo "</div>";
            
            echo "</body></html>";
            exit;
            break;
    }
        }
    


// Get orders with search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$payment_filter = isset($_GET['payment']) ? mysqli_real_escape_string($conn, $_GET['payment']) : '';

$sql = "SELECT o.*,
        CONCAT('Customer ID: ', o.user_id) as customer_name,
        'Contact admin for details' as customer_email
        FROM orders o 
        WHERE 1=1";

if ($search) {
    $sql .= " AND (o.order_number LIKE '%$search%' OR o.user_id = '$search')";
}
if ($status_filter) {
    $sql .= " AND o.order_status = '$status_filter'";
}
if ($payment_filter) {
    $sql .= " AND o.payment_status = '$payment_filter'";
}
$sql .= " ORDER BY o.order_date DESC";

$result = mysqli_query($conn, $sql);

// Get order statistics
$stats_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN payment_status = 'paid' THEN final_amount ELSE 0 END) as total_revenue
    FROM orders";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = $stats_result ? mysqli_fetch_assoc($stats_result) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders Management</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/adminOrders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            background: #f5f7fa !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .orders-container {
            max-width: 100% !important;
            margin: 40px 0 0 0 !important;
            padding: 30px !important;
            background: transparent !important;
        }
        .orders-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 30px !important;
            background: white !important;
            padding: 20px !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
        }
        .stats-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 30px !important;
        }
        .stat-card {
            background: white !important;
            padding: 20px !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            text-align: center !important;
        }
        .stat-icon {
            font-size: 2rem !important;
            margin-bottom: 10px !important;
        }
        .stat-number {
            font-size: 1.5rem !important;
            font-weight: bold !important;
            margin-bottom: 5px !important;
        }
        .search-filters {
            display: flex !important;
            gap: 15px !important;
            align-items: center !important;
            flex-wrap: wrap !important;
        }
        .search-input, .filter-select {
            padding: 10px !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
            font-size: 14px !important;
        }
        .btn {
            padding: 8px 15px !important;
            border: none !important;
            border-radius: 5px !important;
            cursor: pointer !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            display: inline-block !important;
            transition: all 0.3s ease !important;
            font-size: 12px !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
        }
        .btn-success {
            background: #28a745 !important;
            color: white !important;
        }
        .btn-warning {
            background: #ffc107 !important;
            color: #333 !important;
        }
        .btn-info {
            background: #17a2b8 !important;
            color: white !important;
        }
        .orders-table {
            background: white !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            overflow: hidden !important;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .table th, .table td {
            padding: 12px !important;
            text-align: left !important;
            border-bottom: 1px solid #eee !important;
            font-size: 14px !important;
        }
        .table th {
            background: #f8f9fa !important;
            font-weight: 600 !important;
            color: #333 !important;
        }
        .order-actions {
            display: flex !important;
            gap: 5px !important;
            flex-wrap: wrap !important;
        }
        .status-badge {
            padding: 4px 8px !important;
            border-radius: 12px !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            text-transform: uppercase !important;
        }
        .status-pending {
            background: #fff3cd !important;
            color: #856404 !important;
        }
        .status-confirmed {
            background: #d1ecf1 !important;
            color: #0c5460 !important;
        }
        .status-processing {
            background: #d4edda !important;
            color: #155724 !important;
        }
        .status-shipped {
            background: #cce7ff !important;
            color: #004085 !important;
        }
        .status-delivered {
            background: #d4edda !important;
            color: #155724 !important;
        }
        .status-cancelled {
            background: #f8d7da !important;
            color: #721c24 !important;
        }
        .payment-paid {
            background: #d4edda !important;
            color: #155724 !important;
        }
        .payment-pending {
            background: #fff3cd !important;
            color: #856404 !important;
        }
        .payment-failed {
            background: #f8d7da !important;
            color: #721c24 !important;
        }
        .alert {
            padding: 15px !important;
            border-radius: 5px !important;
            margin-bottom: 20px !important;
        }
        .alert-success {
            background: #d4edda !important;
            color: #155724 !important;
            border: 1px solid #c3e6cb !important;
        }
        .alert-danger {
            background: #f8d7da !important;
            color: #721c24 !important;
            border: 1px solid #f5c6cb !important;
        }
        .order-details {
            max-width: 200px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        
        /* Enhanced Action Button Styles */
        .enhanced-actions {
            display: flex !important;
            gap: 10px !important;
            margin-bottom: 20px !important;
            flex-wrap: wrap !important;
        }

        .enhanced-actions .btn {
            padding: 10px 20px !important;
            border: none !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            text-decoration: none !important;
            font-size: 14px !important;
        }

        .enhanced-actions .btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
        }

        .btn-export { background: #28a745 !important; color: white !important; }
        .btn-reports { background: #17a2b8 !important; color: white !important; }
        .btn-bulk { background: #ffc107 !important; color: #212529 !important; }
        .btn-print { background: #6c757d !important; color: white !important; }

        .report-options {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            margin-top: 20px !important;
        }

        .report-options .btn {
            width: 100% !important;
            justify-content: center !important;
        }

        input[type="checkbox"] {
            transform: scale(1.2) !important;
            margin-right: 5px !important;
        }
        
        /* Enhanced Order Actions */
        .order-actions {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 5px !important;
        }
        
        .order-actions .btn {
            min-width: 35px !important;
            height: 35px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 12px !important;
        }
        
        .btn-danger {
            background: #dc3545 !important;
            color: white !important;
        }
        
        .btn-secondary {
            background: #6c757d !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Include Admin Navbar -->
    <header class="main-header">
        <div class="logo">ShopSphere<span class="plus">Admin</span></div>
        <nav style="flex:1;">
            <ul class="admin-nav-items">
                <li><a class="link" href="dashboard.php">Dashboard</a></li>
                <li><a class="link" href="products.php">Products</a></li>
                <li><a class="link" href="orders.php" style="background: #f1f3f6; color: #2874f0;">Orders</a></li>
                <li><a class="link" href="users.php">Users</a></li>
            </ul>
        </nav>
        <div style="display: flex; align-items: center; gap: 20px; position: relative;">
            <div class="dropdown" style="position: relative;">
                <img src="../../images/men1.jpg" alt="Admin" id="userImg" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #2874f0; cursor:pointer;">
                <div class="dropdown-content right" id="userDropdown" style="right:0; min-width:140px;">
                    <a href="profile.php">Profile</a>
                    <a href="settings.php">Settings</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="orders-container">
        <!-- Order Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: #667eea;"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-number"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
                <div>Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #ffc107;"><i class="fas fa-clock"></i></div>
                <div class="stat-number"><?php echo number_format($stats['pending_orders'] ?? 0); ?></div>
                <div>Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #28a745;"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number"><?php echo number_format($stats['delivered_orders'] ?? 0); ?></div>
                <div>Delivered Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #dc3545;"><i class="fas fa-rupee-sign"></i></div>
                <div class="stat-number">₹<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
                <div>Total Revenue</div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="orders-header">
            <div>
                <h1><i class="fas fa-shopping-cart"></i> Orders Management</h1>
                <p>Manage customer orders and track deliveries</p>
            </div>
            <div class="search-filters">
                <form method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" name="search" class="search-input" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <select name="payment" class="filter-select">
                        <option value="">All Payments</option>
                        <option value="pending" <?php echo $payment_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $payment_filter == 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="failed" <?php echo $payment_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                </form>
                <button onclick="exportOrders()" class="btn btn-info"><i class="fas fa-download"></i> Export CSV</button>
                <button onclick="showOrderReports()" class="btn btn-warning"><i class="fas fa-chart-bar"></i> Reports</button>
                <button onclick="bulkStatusUpdate()" class="btn btn-success"><i class="fas fa-edit"></i> Bulk Update</button>
                <button onclick="printOrders()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Orders Table -->
        <div class="orders-table">
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"> Select</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Amount</th>
                        <th>Order Status</th>
                        <th>Payment</th>
                        <th>Tracking</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_orders[]" value="<?php echo $order['order_id']; ?>">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    <br><small>ID: #<?php echo $order['order_id']; ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?></strong>
                                    <br><small><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($order['order_date'])); ?>
                                    <br><small><?php echo date('g:i A', strtotime($order['order_date'])); ?></small>
                                </td>
                                <td>
                                    <strong>₹<?php echo number_format($order['final_amount'], 2); ?></strong>
                                    <?php if ($order['payment_method']): ?>
                                        <br><small><?php echo htmlspecialchars($order['payment_method']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge payment-<?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($order['tracking_number']): ?>
                                        <small><?php echo htmlspecialchars($order['tracking_number']); ?></small>
                                    <?php else: ?>
                                        <small style="color: #666;">Not assigned</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="order-actions">
                                        <button onclick="updateStatus(<?php echo $order['order_id']; ?>, '<?php echo $order['order_status']; ?>')" 
                                                class="btn btn-primary" title="Update Status">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="updatePayment(<?php echo $order['order_id']; ?>, '<?php echo $order['payment_status']; ?>')" 
                                                class="btn btn-success" title="Update Payment">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <button onclick="addTracking(<?php echo $order['order_id']; ?>, '<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>')" 
                                                class="btn btn-info" title="Add Tracking">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                        <button onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)" 
                                                class="btn btn-secondary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="generateInvoice(<?php echo $order['order_id']; ?>)" 
                                                class="btn btn-warning" title="Generate Invoice">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                        <button onclick="deleteOrder(<?php echo $order['order_id']; ?>, '<?php echo htmlspecialchars($order['order_number']); ?>')" 
                                                class="btn btn-danger" title="Delete Order">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                                <br>No orders found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('statusModal')">&times;</span>
            <h3><i class="fas fa-edit"></i> Update Order Status</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="statusOrderId">
                <div style="margin: 20px 0;">
                    <label>Order Status:</label>
                    <select name="order_status" id="statusSelect" class="filter-select" style="width: 100%; margin-top: 5px;">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div style="text-align: right;">
                    <button type="button" onclick="closeModal('statusModal')" class="btn btn-warning">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Update Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('paymentModal')">&times;</span>
            <h3><i class="fas fa-credit-card"></i> Update Payment Status</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_payment">
                <input type="hidden" name="order_id" id="paymentOrderId">
                <div style="margin: 20px 0;">
                    <label>Payment Status:</label>
                    <select name="payment_status" id="paymentSelect" class="filter-select" style="width: 100%; margin-top: 5px;">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div style="text-align: right;">
                    <button type="button" onclick="closeModal('paymentModal')" class="btn btn-warning">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tracking Update Modal -->
    <div id="trackingModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('trackingModal')">&times;</span>
            <h3><i class="fas fa-truck"></i> Add Tracking Number</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_tracking">
                <input type="hidden" name="order_id" id="trackingOrderId">
                <div style="margin: 20px 0;">
                    <label>Tracking Number:</label>
                    <input type="text" name="tracking_number" id="trackingInput" class="search-input" 
                           style="width: 100%; margin-top: 5px;" placeholder="Enter tracking number">
                </div>
                <div style="text-align: right;">
                    <button type="button" onclick="closeModal('trackingModal')" class="btn btn-warning">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Tracking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateStatus(orderId, currentStatus) {
            console.log('updateStatus called with:', orderId, currentStatus);
            try {
                const orderIdElement = document.getElementById('statusOrderId');
                const statusSelectElement = document.getElementById('statusSelect');
                const modalElement = document.getElementById('statusModal');
                
                console.log('Elements found:', {
                    orderIdElement: !!orderIdElement,
                    statusSelectElement: !!statusSelectElement,
                    modalElement: !!modalElement
                });
                
                if (!orderIdElement || !statusSelectElement || !modalElement) {
                    console.error('Modal elements not found');
                    alert('Error: Modal elements not found. Please refresh the page.');
                    return;
                }
                
                orderIdElement.value = orderId;
                statusSelectElement.value = currentStatus;
                modalElement.style.display = 'block';
                console.log('Status modal opened successfully');
            } catch (error) {
                console.error('Error in updateStatus:', error);
                alert('Error opening status modal. Please try again.');
            }
        }

        function updatePayment(orderId, currentPayment) {
            console.log('updatePayment called with:', orderId, currentPayment);
            try {
                const orderIdElement = document.getElementById('paymentOrderId');
                const paymentSelectElement = document.getElementById('paymentSelect');
                const modalElement = document.getElementById('paymentModal');
                
                console.log('Elements found:', {
                    orderIdElement: !!orderIdElement,
                    paymentSelectElement: !!paymentSelectElement,
                    modalElement: !!modalElement
                });
                
                if (!orderIdElement || !paymentSelectElement || !modalElement) {
                    console.error('Payment modal elements not found');
                    alert('Error: Payment modal elements not found. Please refresh the page.');
                    return;
                }
                
                orderIdElement.value = orderId;
                paymentSelectElement.value = currentPayment;
                modalElement.style.display = 'block';
                console.log('Payment modal opened successfully');
            } catch (error) {
                console.error('Error in updatePayment:', error);
                alert('Error opening payment modal. Please try again.');
            }
        }

        function addTracking(orderId, currentTracking) {
            console.log('addTracking called with:', orderId, currentTracking);
            try {
                const orderIdElement = document.getElementById('trackingOrderId');
                const trackingInputElement = document.getElementById('trackingInput');
                const modalElement = document.getElementById('trackingModal');
                
                console.log('Elements found:', {
                    orderIdElement: !!orderIdElement,
                    trackingInputElement: !!trackingInputElement,
                    modalElement: !!modalElement
                });
                
                if (!orderIdElement || !trackingInputElement || !modalElement) {
                    console.error('Tracking modal elements not found');
                    alert('Error: Tracking modal elements not found. Please refresh the page.');
                    return;
                }
                
                orderIdElement.value = orderId;
                trackingInputElement.value = currentTracking;
                modalElement.style.display = 'block';
                console.log('Tracking modal opened successfully');
            } catch (error) {
                console.error('Error in addTracking:', error);
                alert('Error opening tracking modal. Please try again.');
            }
        }

        function closeModal(modalId) {
            try {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                } else {
                    console.warn('Modal not found:', modalId);
                }
            } catch (error) {
                console.error('Error closing modal:', error);
            }
        }

        function toggleDropdown() {
            try {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown) {
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                } else {
                    console.error('Dropdown element not found');
                }
            } catch (error) {
                console.error('Error toggling dropdown:', error);
            }
        }

        // Navbar dropdown functionality
        document.getElementById('userImg').addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const userImg = document.getElementById('userImg');
            if (!userImg.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Logout confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const logoutLinks = document.querySelectorAll('a[href*="logout.php"]');
            logoutLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to logout?')) {
                        window.location.href = this.href;
                    }
                });
            });
        });

        // Enhanced Action Button Functions
        function exportOrders() {
            window.location.href = 'orders.php?action=export';
        }

        // Debug function - call from console to test modals
        function testModals() {
            console.log('Testing modals...');
            const modals = ['statusModal', 'paymentModal', 'trackingModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                console.log(`${modalId}:`, modal ? 'Found' : 'NOT FOUND');
            });
            
            // Test opening status modal
            console.log('Testing status modal...');
            updateStatus(1, 'pending');
        }

        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const orderCheckboxes = document.querySelectorAll('input[name="selected_orders[]"]');
            
            orderCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function showOrderReports() {
            console.log('showOrderReports called');
            // Create reports modal content
            const reportModal = document.createElement('div');
            reportModal.className = 'modal';
            reportModal.id = 'reportModal';
            reportModal.style.display = 'block';
            
            reportModal.innerHTML = `
                <div class="modal-content">
                    <span class="close" onclick="closeReportModal()">&times;</span>
                    <h2>Order Reports</h2>
                    <div class="report-options">
                        <button class="btn" onclick="generateOrderReport('daily')">Daily Orders</button>
                        <button class="btn" onclick="generateOrderReport('weekly')">Weekly Orders</button>
                        <button class="btn" onclick="generateOrderReport('monthly')">Monthly Orders</button>
                        <button class="btn" onclick="generateOrderReport('status')">Orders by Status</button>
                        <button class="btn" onclick="generateOrderReport('payment')">Payment Methods</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(reportModal);
            console.log('Reports modal created and added to page');
        }

        function closeReportModal() {
            const modal = document.getElementById('reportModal');
            if (modal) {
                modal.remove();
            }
        }

        function generateOrderReport(type) {
            window.open(`orders.php?action=report&type=${type}`, '_blank');
            closeReportModal();
        }

        function bulkStatusUpdate() {
            console.log('bulkStatusUpdate called');
            const checkboxes = document.querySelectorAll('input[name="selected_orders[]"]:checked');
            console.log('Found checkboxes:', checkboxes.length);
            
            if (checkboxes.length === 0) {
                alert('Please select orders to update');
                return;
            }

            const status = prompt('Enter new status (pending, processing, shipped, delivered, cancelled):');
            console.log('User entered status:', status);
            
            if (status && ['pending', 'processing', 'shipped', 'delivered', 'cancelled'].includes(status.toLowerCase())) {
                console.log('Valid status, creating form...');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'orders.php';
                
                checkboxes.forEach(checkbox => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_orders[]';
                    input.value = checkbox.value;
                    form.appendChild(input);
                });
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'bulk_status';
                statusInput.value = status.toLowerCase();
                form.appendChild(statusInput);
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'bulk_update';
                form.appendChild(actionInput);
                
                document.body.appendChild(form);
                console.log('Submitting form...');
                form.submit();
            } else if (status) {
                alert('Invalid status. Please use: pending, processing, shipped, delivered, or cancelled');
            }
        }

        function printOrders() {
            console.log('printOrders called');
            window.print();
        }

        // New Action Button Functions
        function viewOrderDetails(orderId) {
            // Fetch order details via AJAX and show in modal
            window.open(`orders.php?action=view_details&order_id=${orderId}`, 'orderDetails', 'width=800,height=600,scrollbars=yes');
        }

        function generateInvoice(orderId) {
            // Generate and download PDF invoice
            window.open(`orders.php?action=generate_invoice&order_id=${orderId}`, '_blank');
        }

        function deleteOrder(orderId, orderNumber) {
            if (confirm(`Are you sure you want to delete order ${orderNumber}? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'orders.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_order';
                form.appendChild(actionInput);
                
                const orderIdInput = document.createElement('input');
                orderIdInput.type = 'hidden';
                orderIdInput.name = 'order_id';
                orderIdInput.value = orderId;
                form.appendChild(orderIdInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            try {
                const modals = ['statusModal', 'paymentModal', 'trackingModal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && event.target == modal) {
                        modal.style.display = 'none';
                    }
                });

                // Handle report modal separately since it's dynamically created
                const reportModal = document.getElementById('reportModal');
                if (reportModal && event.target == reportModal) {
                    closeReportModal();
                }
            } catch (error) {
                console.error('Error in window.onclick:', error);
            }
        }
    </script>
</body>
</html>