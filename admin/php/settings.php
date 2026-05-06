<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost","root","","shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_general':
                $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
                $site_email = mysqli_real_escape_string($conn, $_POST['site_email']);
                $site_phone = mysqli_real_escape_string($conn, $_POST['site_phone']);
                $site_address = mysqli_real_escape_string($conn, $_POST['site_address']);
                
                // Update or insert settings
                $settings = [
                    'site_name' => $site_name,
                    'site_email' => $site_email,
                    'site_phone' => $site_phone,
                    'site_address' => $site_address
                ];
                
                foreach ($settings as $setting_key => $setting_value) {
                    $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES ('$setting_key', '$setting_value') 
                            ON DUPLICATE KEY UPDATE setting_value = '$setting_value'";
                    mysqli_query($conn, $sql);
                }
                
                $success_message = "General settings updated successfully!";
                break;
                
            case 'update_payment':
                $payment_methods = $_POST['payment_methods'] ?? [];
                $payment_settings = json_encode($payment_methods);
                
                $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES ('payment_methods', '$payment_settings') 
                        ON DUPLICATE KEY UPDATE setting_value = '$payment_settings'";
                
                if (mysqli_query($conn, $sql)) {
                    $success_message = "Payment settings updated successfully!";
                } else {
                    $error_message = "Error updating payment settings: " . mysqli_error($conn);
                }
                break;
                
            case 'update_email':
                $smtp_host = mysqli_real_escape_string($conn, $_POST['smtp_host']);
                $smtp_port = mysqli_real_escape_string($conn, $_POST['smtp_port']);
                $smtp_username = mysqli_real_escape_string($conn, $_POST['smtp_username']);
                $smtp_password = mysqli_real_escape_string($conn, $_POST['smtp_password']);
                
                $email_settings = [
                    'smtp_host' => $smtp_host,
                    'smtp_port' => $smtp_port,
                    'smtp_username' => $smtp_username,
                    'smtp_password' => $smtp_password
                ];
                
                foreach ($email_settings as $setting_key => $setting_value) {
                    $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES ('$setting_key', '$setting_value') 
                            ON DUPLICATE KEY UPDATE setting_value = '$setting_value'";
                    mysqli_query($conn, $sql);
                }
                
                $success_message = "Email settings updated successfully!";
                break;
        }
    }
}

// Get current settings
function getSetting($conn, $key, $default = '') {
    $key = mysqli_real_escape_string($conn, $key);
    $sql = "SELECT setting_value FROM site_settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        error_log("Database error in getSetting: " . mysqli_error($conn));
        return $default;
    }
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }
    return $default;
}

$site_name = getSetting($conn, 'site_name', 'ShopSphere');
$site_email = getSetting($conn, 'site_email', 'admin@shopsphere.com');
$site_phone = getSetting($conn, 'site_phone', '+1 (555) 123-4567');
$site_address = getSetting($conn, 'site_address', '123 E-commerce St, Digital City');

// Safely decode payment methods JSON
$payment_methods_json = getSetting($conn, 'payment_methods', '["credit_card","paypal","bank_transfer"]');
$payment_methods = json_decode($payment_methods_json, true);
if ($payment_methods === null) {
    $payment_methods = ["credit_card","paypal","bank_transfer"]; // fallback
}

$smtp_host = getSetting($conn, 'smtp_host', 'smtp.gmail.com');
$smtp_port = getSetting($conn, 'smtp_port', '587');
$smtp_username = getSetting($conn, 'smtp_username', '');
$smtp_password = getSetting($conn, 'smtp_password', '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/adminDashboard.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <style>
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .settings-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .settings-tabs {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .tab-button {
            flex: 1;
            padding: 20px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        
        .tab-button.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background: #f8f9ff;
        }
        
        .tab-button:hover {
            background: #f5f5f5;
        }
        
        .settings-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .tab-panel {
            display: none;
        }
        
        .tab-panel.active {
            display: block;
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
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0;
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
        
        .btn-secondary {
            background: #6c757d;
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
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .setting-section {
            border-bottom: 1px solid #eee;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        
        .setting-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
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
                    <a href="settings.php" style="background: #f1f3f6; color: #2874f0;">Settings</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="settings-container">
        <div class="settings-header">
            <h1><i class="fas fa-cog"></i> System Settings</h1>
            <p>Configure your e-commerce platform settings</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="settings-tabs">
            <button class="tab-button active" onclick="showTab('general')">
                <i class="fas fa-globe"></i> General
            </button>
            <button class="tab-button" onclick="showTab('payment')">
                <i class="fas fa-credit-card"></i> Payment
            </button>
            <button class="tab-button" onclick="showTab('email')">
                <i class="fas fa-envelope"></i> Email
            </button>
            <button class="tab-button" onclick="showTab('security')">
                <i class="fas fa-shield-alt"></i> Security
            </button>
        </div>

        <div class="settings-content">
            <!-- General Settings -->
            <div id="general" class="tab-panel active">
                <form method="POST">
                    <input type="hidden" name="action" value="update_general">
                    
                    <div class="setting-section">
                        <h3 class="section-title">
                            <i class="fas fa-store"></i> Site Information
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="site_name">Site Name</label>
                                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="site_email">Contact Email</label>
                                <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($site_email); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="site_phone">Phone Number</label>
                                <input type="text" id="site_phone" name="site_phone" value="<?php echo htmlspecialchars($site_phone); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="site_address">Address</label>
                                <textarea id="site_address" name="site_address" rows="3" required><?php echo htmlspecialchars($site_address); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Save General Settings
                    </button>
                </form>
            </div>

            <!-- Payment Settings -->
            <div id="payment" class="tab-panel">
                <form method="POST">
                    <input type="hidden" name="action" value="update_payment">
                    
                    <div class="setting-section">
                        <h3 class="section-title">
                            <i class="fas fa-credit-card"></i> Payment Methods
                        </h3>
                        
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="credit_card" name="payment_methods[]" value="credit_card" 
                                       <?php echo (is_array($payment_methods) && in_array('credit_card', $payment_methods)) ? 'checked' : ''; ?>>
                                <label for="credit_card">Credit Card</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="paypal" name="payment_methods[]" value="paypal" 
                                       <?php echo (is_array($payment_methods) && in_array('paypal', $payment_methods)) ? 'checked' : ''; ?>>
                                <label for="paypal">PayPal</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="bank_transfer" name="payment_methods[]" value="bank_transfer" 
                                       <?php echo (is_array($payment_methods) && in_array('bank_transfer', $payment_methods)) ? 'checked' : ''; ?>>
                                <label for="bank_transfer">Bank Transfer</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="cash_on_delivery" name="payment_methods[]" value="cash_on_delivery" 
                                       <?php echo (is_array($payment_methods) && in_array('cash_on_delivery', $payment_methods)) ? 'checked' : ''; ?>>
                                <label for="cash_on_delivery">Cash on Delivery</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Save Payment Settings
                    </button>
                </form>
            </div>

            <!-- Email Settings -->
            <div id="email" class="tab-panel">
                <form method="POST">
                    <input type="hidden" name="action" value="update_email">
                    
                    <div class="setting-section">
                        <h3 class="section-title">
                            <i class="fas fa-server"></i> SMTP Configuration
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp_host">SMTP Host</label>
                                <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($smtp_host); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="smtp_port">SMTP Port</label>
                                <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($smtp_port); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp_username">SMTP Username</label>
                                <input type="text" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($smtp_username); ?>">
                            </div>
                            <div class="form-group">
                                <label for="smtp_password">SMTP Password</label>
                                <input type="password" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($smtp_password); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Save Email Settings
                    </button>
                </form>
            </div>

            <!-- Security Settings -->
            <div id="security" class="tab-panel">
                <div class="setting-section">
                    <h3 class="section-title">
                        <i class="fas fa-shield-alt"></i> Security Configuration
                    </h3>
                    
                    <div class="form-group">
                        <label>Two-Factor Authentication</label>
                        <button type="button" class="btn btn-secondary">
                            <i class="fas fa-mobile-alt"></i> Enable 2FA
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label>Session Timeout</label>
                        <select>
                            <option value="30">30 minutes</option>
                            <option value="60" selected>1 hour</option>
                            <option value="120">2 hours</option>
                            <option value="240">4 hours</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Backup Database</label>
                        <button type="button" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Create Backup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab panels
            const panels = document.querySelectorAll('.tab-panel');
            panels.forEach(panel => panel.classList.remove('active'));
            
            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => button.classList.remove('active'));
            
            // Show selected tab panel
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
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
    <!-- <script src="../javascript/injectNavbar.js"></script>
    <script src="../javascript/navbar.js"></script> -->
</body>
</html>