<?php
$conn = mysqli_connect("localhost","root","","shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get date range for analytics
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="analytics_report_' . $start_date . '_to_' . $end_date . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Sales data
    fputcsv($output, array('SALES ANALYTICS'));
    fputcsv($output, array('Date', 'Order Count', 'Total Sales', 'Average Order Value'));
    
    $sales_sql = "SELECT 
        DATE(order_date) as date,
        COUNT(*) as order_count,
        SUM(final_amount) as total_sales,
        AVG(final_amount) as avg_order_value
    FROM orders 
    WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' 
    AND order_status != 'cancelled'
    GROUP BY DATE(order_date)
    ORDER BY date DESC";
    
    $sales_result = mysqli_query($conn, $sales_sql);
    while ($row = mysqli_fetch_assoc($sales_result)) {
        fputcsv($output, array($row['date'], $row['order_count'], '$' . number_format($row['total_sales'], 2), '$' . number_format($row['avg_order_value'], 2)));
    }
    
    fputcsv($output, array(''));
    fputcsv($output, array('PRODUCT PERFORMANCE'));
    fputcsv($output, array('Product Name', 'Category', 'Units Sold', 'Revenue'));
    
    $product_sql = "SELECT 
        p.name,
        p.category,
        COALESCE(SUM(oi.quantity), 0) as units_sold,
        COALESCE(SUM(oi.total_price), 0) as revenue
    FROM products p
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.order_id
    WHERE (DATE(o.order_date) BETWEEN '$start_date' AND '$end_date' OR o.order_date IS NULL)
    AND (o.order_status != 'cancelled' OR o.order_status IS NULL)
    GROUP BY p.product_id
    ORDER BY revenue DESC
    LIMIT 10";
    
    $product_result = mysqli_query($conn, $product_sql);
    while ($row = mysqli_fetch_assoc($product_result)) {
        fputcsv($output, array($row['name'], $row['category'], $row['units_sold'], '$' . number_format($row['revenue'], 2)));
    }
    
    fclose($output);
    exit;
}

// Sales Analytics
$sales_sql = "SELECT 
    DATE(order_date) as date,
    COUNT(*) as order_count,
    SUM(final_amount) as total_sales,
    AVG(final_amount) as avg_order_value
FROM orders 
WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' 
AND order_status != 'cancelled'
GROUP BY DATE(order_date)
ORDER BY date DESC";
$sales_result = mysqli_query($conn, $sales_sql);

// Product Performance
$product_sql = "SELECT 
    p.name,
    p.category,
    COALESCE(SUM(oi.quantity), 0) as units_sold,
    COALESCE(SUM(oi.total_price), 0) as revenue,
    p.stock_quantity
FROM products p
LEFT JOIN order_items oi ON p.product_id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.order_id
WHERE (DATE(o.order_date) BETWEEN '$start_date' AND '$end_date' OR o.order_date IS NULL)
AND (o.order_status != 'cancelled' OR o.order_status IS NULL)
GROUP BY p.product_id
ORDER BY revenue DESC
LIMIT 10";
$product_result = mysqli_query($conn, $product_sql);

// Category Performance
$category_sql = "SELECT 
    p.category,
    COALESCE(SUM(oi.quantity), 0) as units_sold,
    COALESCE(SUM(oi.total_price), 0) as revenue,
    COUNT(DISTINCT p.product_id) as product_count
FROM products p
LEFT JOIN order_items oi ON p.product_id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.order_id
WHERE (DATE(o.order_date) BETWEEN '$start_date' AND '$end_date' OR o.order_date IS NULL)
AND (o.order_status != 'cancelled' OR o.order_status IS NULL)
GROUP BY p.category
ORDER BY revenue DESC";
$category_result = mysqli_query($conn, $category_sql);

// User Analytics
$user_sql = "SELECT 
    COUNT(*) as total_users,
    0 as new_users,
    0 as active_users
FROM users";
$user_result = mysqli_query($conn, $user_sql);
$user_stats = mysqli_fetch_assoc($user_result);

// Order Status Distribution
$status_sql = "SELECT 
    order_status,
    COUNT(*) as count,
    SUM(final_amount) as total_amount
FROM orders 
WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'
GROUP BY order_status";
$status_result = mysqli_query($conn, $status_sql);

// Revenue Summary
$revenue_sql = "SELECT 
    SUM(final_amount) as total_revenue,
    COUNT(*) as total_orders,
    AVG(final_amount) as avg_order_value,
    SUM(CASE WHEN order_status = 'completed' THEN final_amount ELSE 0 END) as completed_revenue
FROM orders 
WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'";
$revenue_result = mysqli_query($conn, $revenue_sql);
$revenue_stats = mysqli_fetch_assoc($revenue_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/adminDashboard.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .analytics-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .date-filter {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .date-filter input {
            padding: 10px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .date-filter button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #667eea;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .data-tables {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-size: 18px;
            font-weight: 700;
        }
        
        .analytics-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .analytics-table th,
        .analytics-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .analytics-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .analytics-table tr:hover {
            background: #f8f9ff;
        }
        
        .revenue-highlight {
            color: #28a745;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #0056b3; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .data-tables {
                grid-template-columns: 1fr;
            }
            
            .date-filter {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navbar -->
    <header class="main-header">
        <div class="logo">ShopSphere<span class="plus">Admin</span></div>
        <nav style="flex:1;">
            <ul class="admin-nav-items">
                <li><a class="link" href="dashboard.php">Dashboard</a></li>
                <li><a class="link" href="products.php">Products</a></li>
                <li><a class="link" href="orders.php">Orders</a></li>
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

    <div class="analytics-container">
        <div class="analytics-header">
            <h1><i class="fas fa-chart-line"></i> Analytics Dashboard</h1>
            <p>Comprehensive insights into your business performance</p>
        </div>

        <div class="date-filter">
            <label><strong>Date Range:</strong></label>
            <input type="date" id="start_date" value="<?php echo $start_date; ?>">
            <span>to</span>
            <input type="date" id="end_date" value="<?php echo $end_date; ?>">
            <button onclick="applyDateFilter()">
                <i class="fas fa-filter"></i> Apply Filter
            </button>
            <button onclick="exportReport()" style="background: #28a745;">
                <i class="fas fa-download"></i> Export Report
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($revenue_stats['total_revenue'] ?? 0, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo number_format($revenue_stats['total_orders'] ?? 0); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($revenue_stats['avg_order_value'] ?? 0, 2); ?></div>
                <div class="stat-label">Avg Order Value</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($user_stats['new_users'] ?? 0); ?></div>
                <div class="stat-label">New Customers</div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="fas fa-line-chart"></i> Sales Trend
                </div>
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>
            
            <div class="chart-card">
                <div class="chart-title">
                    <i class="fas fa-pie-chart"></i> Order Status Distribution
                </div>
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="data-tables">
            <div class="table-card">
                <div class="table-header">
                    <i class="fas fa-trophy"></i> Top Performing Products
                </div>
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($product_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td><?php echo number_format($product['units_sold'] ?? 0); ?></td>
                            <td class="revenue-highlight">$<?php echo number_format($product['revenue'] ?? 0, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <i class="fas fa-tags"></i> Category Performance
                </div>
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Products</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['category']); ?></td>
                            <td><?php echo number_format($category['product_count'] ?? 0); ?></td>
                            <td><?php echo number_format($category['units_sold'] ?? 0); ?></td>
                            <td class="revenue-highlight">$<?php echo number_format($category['revenue'] ?? 0, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesData = {
            <?php 
            mysqli_data_seek($sales_result, 0);
            $sales_labels = [];
            $sales_values = [];
            while ($row = mysqli_fetch_assoc($sales_result)) {
                $sales_labels[] = "'" . $row['date'] . "'";
                $sales_values[] = $row['total_sales'];
            }
            echo "labels: [" . implode(',', array_reverse($sales_labels)) . "],";
            echo "data: [" . implode(',', array_reverse($sales_values)) . "]";
            ?>
        };
        
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesData.labels,
                datasets: [{
                    label: 'Daily Sales',
                    data: salesData.data,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = {
            <?php 
            mysqli_data_seek($status_result, 0);
            $status_labels = [];
            $status_values = [];
            $colors = [
                'rgba(255, 193, 7, 0.8)',
                'rgba(0, 123, 255, 0.8)', 
                'rgba(23, 162, 184, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ];
            $i = 0;
            while ($row = mysqli_fetch_assoc($status_result)) {
                $status_labels[] = "'" . ucfirst($row['order_status']) . "'";
                $status_values[] = $row['count'];
                $i++;
            }
            echo "labels: [" . implode(',', $status_labels) . "],";
            echo "data: [" . implode(',', $status_values) . "],";
            echo "colors: [" . implode(',', array_map(function($c) { return "'$c'"; }, array_slice($colors, 0, $i))) . "]";
            ?>
        };
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.data,
                    backgroundColor: statusData.colors,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function applyDateFilter() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            console.log('Applying filter:', startDate, 'to', endDate);
            window.location.href = `analytics.php?start_date=${startDate}&end_date=${endDate}`;
        }

        function exportReport() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            console.log('Exporting report:', startDate, 'to', endDate);
            window.open(`analytics.php?start_date=${startDate}&end_date=${endDate}&export=csv`, '_blank');
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
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
    </script>
</body>
</html>