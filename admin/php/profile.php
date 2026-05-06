<?php
$conn = mysqli_connect("localhost","root","","shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// For now, we'll use a basic admin profile
$admin_id = 1; // Assume admin user ID is 1

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
        $admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
        $admin_phone = mysqli_real_escape_string($conn, $_POST['admin_phone']);
        
        // Update admin settings table or create if not exists
        $settings = [
            'admin_name' => $admin_name,
            'admin_email' => $admin_email,
            'admin_phone' => $admin_phone
        ];
        
        foreach ($settings as $setting_key => $setting_value) {
            $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES ('$setting_key', '$setting_value') 
                    ON DUPLICATE KEY UPDATE setting_value = '$setting_value'";
            mysqli_query($conn, $sql);
        }
        
        $success_message = "Profile updated successfully!";
    }
}

// Get current profile settings
function getSetting($conn, $key, $default = '') {
    $sql = "SELECT setting_value FROM site_settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }
    return $default;
}

$admin_name = getSetting($conn, 'admin_name', 'Admin User');
$admin_email = getSetting($conn, 'admin_email', 'admin@shopsphere.com');
$admin_phone = getSetting($conn, 'admin_phone', '+1 (555) 123-4567');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/adminDashboard.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .profile-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
                    <a href="profile.php" style="background: #f1f3f6; color: #2874f0;">Profile</a>
                    <a href="settings.php">Settings</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="fas fa-user-circle"></i> Admin Profile</h1>
            <p>Manage your admin account information</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="admin_name">Full Name</label>
                    <input type="text" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_email">Email Address</label>
                    <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin_email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_phone">Phone Number</label>
                    <input type="text" id="admin_phone" name="admin_phone" value="<?php echo htmlspecialchars($admin_phone); ?>" required>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
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
    </script>
</body>
</html>