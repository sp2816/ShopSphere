<?php
session_start();

// Check if admin is logged in (you can modify this based on your admin authentication)
// For now, we'll assume admin access - you can add proper admin authentication later
/*
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../html/admin_login.html");
    exit();
}
*/

// Database connection
$conn = mysqli_connect("localhost", "root", "", "shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get statistics from database
$stats = [];

// Total Users
$userQuery = "SELECT COUNT(*) as total_users FROM users";
$userResult = mysqli_query($conn, $userQuery);
$stats['users'] = $userResult ? mysqli_fetch_assoc($userResult)['total_users'] : 0;

// Total Products (if you have a products table)
$productQuery = "SELECT COUNT(*) as total_products FROM products";
$productResult = mysqli_query($conn, $productQuery);
$stats['products'] = $productResult ? mysqli_fetch_assoc($productResult)['total_products'] : 0;

// If products table doesn't exist, set to 0
if (!$productResult) {
    $stats['products'] = 0;
}

// Total Orders (if you have an orders table)
$orderQuery = "SELECT COUNT(*) as total_orders FROM orders";
$orderResult = mysqli_query($conn, $orderQuery);
$stats['orders'] = $orderResult ? mysqli_fetch_assoc($orderResult)['total_orders'] : 0;

// If orders table doesn't exist, set to 0
if (!$orderResult) {
    $stats['orders'] = 0;
}

// Calculate revenue (if you have orders with amount)
$revenueQuery = "SELECT SUM(final_amount) as total_revenue FROM orders WHERE order_status = 'delivered'";
$revenueResult = mysqli_query($conn, $revenueQuery);
$stats['revenue'] = $revenueResult ? (mysqli_fetch_assoc($revenueResult)['total_revenue'] ?? 0) : 0;

// If orders table doesn't exist, set to 0
if (!$revenueResult) {
    $stats['revenue'] = 0;
}

// Get recent activities (recent user registrations)
$recentUsers = [];
$recentUserQuery = "SELECT name, email FROM users ORDER BY name ASC LIMIT 5";
$recentUserResult = mysqli_query($conn, $recentUserQuery);

if ($recentUserResult) {
    while ($row = mysqli_fetch_assoc($recentUserResult)) {
        $recentUsers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ShopSphere</title>
    <link rel="stylesheet" href="../css/navbar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/adminDashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Test styles to ensure CSS loads */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            background: #f5f7fa !important;
            line-height: 1.6 !important;
            color: #333 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .dashboard-container {
            background: transparent !important;
            padding: 30px !important;
            max-width: 100% !important;
            margin: 0px 0 0 0 !important; /* Full width, no auto centering */
        }
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            padding: 40px !important;
            border-radius: 15px !important;
            margin-bottom: 30px !important;
        }
        .stats-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
            gap: 25px !important;
        }
        .stat-card {
            background: white !important;
            padding: 30px !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
            display: flex !important;
            align-items: center !important;
            gap: 20px !important;
        }
    </style>
    <!-- Debug: CSS file paths -->
    <!-- <?php echo "CSS Path: " . realpath("../css/adminDashboard.css"); ?> -->
</head>
<body>
    <!-- Include Admin Navbar -->
    <header class="main-header">
        <div class="logo">ShopSphere<span class="plus">Admin</span></div>
        <nav style="flex:1;">
            <ul class="admin-nav-items">
                <li><a class="link" href="dashboard.php" style="background: #f1f3f6; color: #2874f0;">Dashboard</a></li>
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

    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>Welcome to Admin Dashboard</h1>
                <p>Manage your e-commerce platform efficiently</p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo number_format($stats['users']); ?></p>
                    <span class="stat-change positive">Real-time data</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3>Products</h3>
                    <p class="stat-number"><?php echo number_format($stats['products']); ?></p>
                    <span class="stat-change positive">Inventory count</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3>Orders</h3>
                    <p class="stat-number"><?php echo number_format($stats['orders']); ?></p>
                    <span class="stat-change positive">Total orders</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>Revenue</h3>
                    <p class="stat-number">₹<?php echo number_format($stats['revenue']); ?></p>
                    <span class="stat-change positive">Total earnings</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="viewUsers.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="action-content">
                        <h3>Manage Users</h3>
                        <p>View, edit, and manage user accounts</p>
                        <small><?php echo $stats['users']; ?> total users</small>
                    </div>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="products.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="action-content">
                        <h3>Manage Products</h3>
                        <p>Add, edit, and organize your inventory</p>
                        <small><?php echo $stats['products']; ?> products in stock</small>
                    </div>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="orders.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="action-content">
                        <h3>Manage Orders</h3>
                        <p>Process and track customer orders</p>
                        <small><?php echo $stats['orders']; ?> total orders</small>
                    </div>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="settings.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="action-content">
                        <h3>System Settings</h3>
                        <p>Configure platform settings and preferences</p>
                        <small>Manage configurations</small>
                    </div>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="analytics.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="action-content">
                        <h3>Analytics</h3>
                        <p>View sales reports and performance metrics</p>
                        <small>Revenue: ₹<?php echo number_format($stats['revenue']); ?></small>
                    </div>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="categories.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="action-content">
                        <h3>Categories</h3>
                        <p>Manage product categories and tags</p>
                        <small>Organize products</small>
                    </div>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <div class="activity-header">
                <h2>System Users</h2>
                <a href="viewUsers.php" class="view-all">View All</a>
            </div>
            <div class="activity-list">
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <p><strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>
                                <span class="activity-time">
                                    <?php 
                                    if (isset($user['created_at'])) {
                                        echo date('M j, Y g:i A', strtotime($user['created_at']));
                                    } else {
                                        echo 'Recently';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="activity-content">
                            <p><strong>Welcome to ShopSphere Admin!</strong> Start managing your platform</p>
                            <span class="activity-time">Get started now</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
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

        // Add hover effects to action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Simulate real-time stats updates
        function updateStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                stat.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    stat.style.transform = 'scale(1)';
                }, 200);
            });
        }

        // Auto-refresh stats every 30 seconds
        setInterval(updateStats, 30000);
    </script>
</body>
</html>

<?php
$conn->close();
?>