<?php
session_start();

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$userName = isset($_SESSION['name']) ? $_SESSION['name'] : 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <link rel="stylesheet" href="../css/orders.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <header class="main-header">
    <div class="logo">ShopSphere <span class="plus">Explore Plus ✨</span></div>

    <div class="search-bar">
      <input type="text" placeholder="Search for Products, Brands and More">
      <i class="fas fa-search"></i>
    </div>

    <div class="header-icons">
      <a class="link" href="profile.php">
        <div class="icon-item">
          <i class="fas fa-user"></i>
          <span><?php echo htmlspecialchars($userName); ?></span>
        </div>
      </a>

      <a class="link" href="cart.php">
        <div class="icon-item">
          <i class="fas fa-shopping-cart"></i>
          <span>Cart</span>
        </div>
      </a>

      <a class="link" href="orders.php">
        <div class="icon-item">
          <i class="fas fa-box"></i>
          <span>Orders</span>
        </div>
      </a>

      <a class="link" href="wishlist.php">
        <div class="icon-item">
          <i class="fas fa-heart"></i>
          <span>Wishlist</span>
        </div>
      </a>
 
      <!-- Three Dots with dropdown -->
      <div class="icon-item dropdown" id="menuToggle" onclick="toggleDropdown()">
        <i class="fas fa-ellipsis-v"></i>
        <div class="dropdown-content right" id="dropdownMenu">
          <a href="customerCare.php">Customer Care</a>
          <a href="aboutUs.php">About Us</a>
          <a href="contactUs.php">Contact Us</a>
          <a href="../prac5/events.html">Events</a>
          <a href="logout.php">Logout</a>

          <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 16px;">
            <p style="margin: 0; font-size: 14px;">Dark Mode</p>
            <label class="switch">
              <input type="checkbox" id="darkToggle" onchange="toggleDarkMode()">
              <span class="slider round"></span>
            </label>
          </div>
        </div>
      </div>
    </div>
  </header>

  <a href="homepage.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>

  <main class="orders-container">
    <h1>My Orders</h1>

    <div class="order-card">
      <img src="../images/watch.jpg" alt="Smartwatch">
      <div class="order-info">
        <h3>Noise Smartwatch</h3>
        <p>Order ID: #ORD123456</p>
        <p>Status: <span class="status delivered">Delivered</span></p>
        <button class="btn">View Details</button>
      </div>
    </div>

    <div class="order-card">
      <img src="../images/earpods.jpg" alt="Earbuds">
      <div class="order-info">
        <h3>Wireless Earbuds</h3>
        <p>Order ID: #ORD789012</p>
        <p>Status: <span class="status shipping">In Transit</span></p>
        <button class="btn">Track Order</button>
      </div>
    </div>

    <div class="order-card">
      <img src="../images/mobile1.jpg" alt="Printer">
      <div class="order-info">
        <h3>Canon Printer</h3>
        <p>Order ID: #ORD345678</p>
        <p>Status: <span class="status pending">Pending</span></p>
        <button class="btn cancel">Cancel Order</button>
      </div>
    </div>
  </main>

  <footer class="site-footer">
    <p>&copy; 2025 ShopSphere. All rights reserved.</p>
    <p>
      <a href="aboutUs.php">About</a> |
      <a href="customerCare.php">Contact</a> |
      <a href="terms.php">Terms</a> |
      <a href="privacy.php">Privacy</a>
    </p>
  </footer>
   
  <script src="../js/orders.js"></script>
  <script>
    function toggleDropdown() {
      const dropdown = document.getElementById('dropdownMenu');
      if (dropdown) {
        dropdown.classList.toggle('show');
      }
    }
    
    function toggleDarkMode() {
      const body = document.body;
      const darkToggle = document.getElementById('darkToggle');
      
      if (body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
        if (darkToggle) darkToggle.checked = false;
      } else {
        body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
        if (darkToggle) darkToggle.checked = true;
      }
    }
    
    // Apply saved dark mode preference on page load
    document.addEventListener('DOMContentLoaded', function() {
      const isDarkMode = localStorage.getItem('darkMode') === 'enabled';
      const darkToggle = document.getElementById('darkToggle');
      
      if (isDarkMode) {
        document.body.classList.add('dark-mode');
        if (darkToggle) darkToggle.checked = true;
      }
    });
  </script>
  
</body>
</html>
