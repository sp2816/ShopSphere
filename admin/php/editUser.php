<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";
$user = null;

// Get user ID from URL
$userId = isset($_GET['id']) ? $_GET['id'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Update user in database
    $sql = "UPDATE users SET name='$name', email='$email', password='$password' WHERE name='$id'";
    
    if (mysqli_query($conn, $sql)) {
        $message = "User updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating user: " . mysqli_error($conn);
        $messageType = "error";
    }
}

// Fetch user data
if ($userId) {
    $sql = "SELECT * FROM users WHERE name='$userId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = "User not found!";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/editUser.css">
</head>
<body>
    <!-- Include Admin Navbar -->
    <header class="main-header">
        <div class="logo">ShopSphere<span class="plus">Admin</span></div>
        <nav style="flex:1;">
            <ul class="admin-nav-items">
                <li><a class="link" href="dashboard.php">Dashboard</a></li>
                <li><a class="link" href="products.php">Products</a></li>
                <li><a class="link" href="orders.php">Orders</a></li>
                <li><a class="link" href="users.php" style="background: #f1f3f6; color: #2874f0;">Users</a></li>
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

    <div class="container">
        <div class="page-header">
            <h1>Edit User</h1>
            <p>Update user information</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <span class="message-icon"><?php echo $messageType == 'success' ? '✅' : '❌'; ?></span>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="form-header">
                <h2>User Information</h2>
                <a href="viewUsers.php" class="back-btn">← Back to Users</a>
            </div>

            <?php if ($user): ?>
                <form method="POST" class="edit-form">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['name']); ?>">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        <span class="form-hint">Enter the user's full name</span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        <span class="form-hint">Enter a valid email address</span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                        <span class="form-hint">Enter a secure password</span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span>💾</span>
                            Update User
                        </button>
                        <a href="viewUsers.php" class="btn btn-secondary">
                            <span>❌</span>
                            Cancel
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="error-container">
                    <div class="error-icon">⚠️</div>
                    <h3>User Not Found</h3>
                    <p>The requested user could not be found in the database.</p>
                    <a href="viewUsers.php" class="btn btn-primary">Return to Users List</a>
                </div>
            <?php endif; ?>
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

        // Form validation
        document.querySelector('.edit-form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!name || !email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return false;
            }

            return confirm('Are you sure you want to update this user?');
        });

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }
        }, 5000);
    </script>
</body>
</html>

<?php
$conn->close();
?>