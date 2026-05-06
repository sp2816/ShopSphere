<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";
$messageType = "";

// Get user data
$userEmail = $_SESSION['email'];
$sql = "SELECT * FROM users WHERE email='$userEmail'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Check if phone and address columns exist, if not create them
    $checkColumns = "SHOW COLUMNS FROM users LIKE 'phone'";
    $result_check = mysqli_query($conn, $checkColumns);
    if (mysqli_num_rows($result_check) == 0) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL");
    }
    
    $checkColumns = "SHOW COLUMNS FROM users LIKE 'address'";
    $result_check = mysqli_query($conn, $checkColumns);
    if (mysqli_num_rows($result_check) == 0) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL");
    }
    
    // Validate current password if user wants to change password
    if (!empty($newPassword)) {
        if ($currentPassword !== $user['password']) {
            $message = "Current password is incorrect!";
            $messageType = "error";
        } elseif ($newPassword !== $confirmPassword) {
            $message = "New passwords do not match!";
            $messageType = "error";
        } elseif (strlen($newPassword) < 6) {
            $message = "New password must be at least 6 characters long!";
            $messageType = "error";
        } else {
            // Update with new password
            $sql = "UPDATE users SET name='$name', email='$email', phone='$phone', address='$address', password='$newPassword' WHERE email='$userEmail'";
        }
    } else {
        // Update without changing password
        $sql = "UPDATE users SET name='$name', email='$email', phone='$phone', address='$address' WHERE email='$userEmail'";
    }
    
    // Execute update if no errors
    if (empty($message)) {
        if (mysqli_query($conn, $sql)) {
            $message = "Profile updated successfully!";
            $messageType = "success";
            $_SESSION['email'] = $email; // Update session email if changed
            
            // Refresh user data
            $sql = "SELECT * FROM users WHERE email='$email'";
            $result = mysqli_query($conn, $sql);
            $user = mysqli_fetch_assoc($result);
        } else {
            $message = "Error updating profile: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ShopSphere</title>
    <link rel="stylesheet" href="../css/homepage.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <!-- Back to Homepage Button -->
    <div class="back-to-home">
        <a href="homepage.php" class="home-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Homepage</span>
        </a>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="profile-info">
                <h1>My Profile</h1>
                <p>Manage your account information and preferences</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <span class="message-icon">
                    <?php echo $messageType == 'success' ? '✅' : '❌'; ?>
                </span>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <div class="profile-sidebar">
                <div class="sidebar-menu">
                    <div class="menu-item active">
                        <i class="fas fa-user"></i>
                        <span>Personal Information</span>
                    </div>
                    <div class="menu-item">
                        <i class="fas fa-lock"></i>
                        <span>Security</span>
                    </div>
                    <div class="menu-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Order History</span>
                    </div>
                    <div class="menu-item">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                    </div>
                </div>
                
                <div class="logout-section">
                    <a href="../php/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <div class="profile-main">
                <form method="POST" class="profile-form">
                    <div class="form-section">
                        <h3>Personal Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter your phone number">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="Enter your address">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Change Password</h3>
                        <p class="section-description">Leave blank if you don't want to change your password</p>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Profile
                        </button>
                        <a href="homepage.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }
        }, 5000);

        // Password validation
        document.querySelector('.profile-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;

            if (newPassword || confirmPassword || currentPassword) {
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Please enter your current password to change password.');
                    return false;
                }
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match.');
                    return false;
                }
                
                if (newPassword.length < 6) {
                    e.preventDefault();
                    alert('New password must be at least 6 characters long.');
                    return false;
                }
            }
        });

        // Sidebar menu interaction
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
