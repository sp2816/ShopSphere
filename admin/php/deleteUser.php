<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";
$deleted = false;

// Get user ID from URL
$userId = isset($_GET['id']) ? $_GET['id'] : '';

// Handle deletion - direct deletion if confirm parameter is present
if (isset($_GET['confirm']) && $_GET['confirm'] == 'true' && $userId) {
    // Delete user from database
    $sql = "DELETE FROM users WHERE name='$userId'";
    
    if (mysqli_query($conn, $sql)) {
        $message = "User deleted successfully!";
        $messageType = "success";
        $deleted = true;
    } else {
        $message = "Error deleting user: " . mysqli_error($conn);
        $messageType = "error";
    }
}

// Fetch user data if not deleted and user ID exists
$user = null;
if ($userId && !$deleted) {
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
    <title>Delete User - Admin Panel</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/deleteUser.css">
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
            <h1>Delete User</h1>
            <p>Remove user from the system</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <span class="message-icon"><?php echo $messageType == 'success' ? '✅' : '❌'; ?></span>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="delete-container">
            <?php if ($deleted): ?>
                <!-- Success State -->
                <div class="success-container">
                    <div class="success-icon">✅</div>
                    <h2>User Deleted Successfully</h2>
                    <p>The user has been permanently removed from the system.</p>
                    <div class="action-buttons">
                        <a href="viewUsers.php" class="btn btn-primary">
                            <span>👥</span>
                            Back to Users List
                        </a>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <span>🏠</span>
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            <?php elseif ($user): ?>
                <!-- Simple Confirmation with JavaScript -->
                <div class="confirmation-container">
                    <div class="user-details">
                        <div class="user-card">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                            </div>
                            <div class="user-info">
                                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                                <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                <p class="user-meta">Registered User</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button onclick="confirmDeletion()" class="btn btn-danger">
                            <span>🗑️</span>
                            Delete User
                        </button>
                        <a href="viewUsers.php" class="btn btn-secondary">
                            <span>❌</span>
                            Cancel
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Error State -->
                <div class="error-container">
                    <div class="error-icon">❌</div>
                    <h2>User Not Found</h2>
                    <p>The requested user could not be found in the database.</p>
                    <div class="action-buttons">
                        <a href="viewUsers.php" class="btn btn-primary">
                            <span>👥</span>
                            Back to Users List
                        </a>
                    </div>
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

        // Deletion confirmation with redirect
        function confirmDeletion() {
            const userName = '<?php echo addslashes($user['name'] ?? ''); ?>';
            const confirmed = confirm(`Are you sure you want to delete the user "${userName}"?\n\nThis action cannot be undone and will permanently remove:\n- User account\n- All user data\n\nClick OK to delete or Cancel to abort.`);
            
            if (confirmed) {
                // Redirect to delete with confirmation
                window.location.href = `deleteUser.php?id=${encodeURIComponent('<?php echo addslashes($user['name'] ?? ''); ?>')}&confirm=true`;
            }
        }

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }
        }, 5000);

        // Add animation to warning elements
        document.addEventListener('DOMContentLoaded', function() {
            const warningElements = document.querySelectorAll('.warning-icon, .user-card, .warning-message');
            warningElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.2}s`;
                element.classList.add('fade-in');
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>