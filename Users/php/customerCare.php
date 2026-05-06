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
  <title>Customer Care - ShopSphere</title>
  <link rel="stylesheet" href="../css/customerCare.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <!-- Header -->
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

  <div class="customer-care-container">
    <h1>Customer Care</h1>
    <p>We are here to assist you 24/7. Choose an option below or contact our support team directly.</p>

    <div class="help-options">
      <div class="help-card" onclick="fn1()">
        <i class="fas fa-box"></i>
        <h3>Track Your Order</h3>
        <p>Check the status of your order and delivery updates.</p>
      </div>

      <div class="help-card" onclick="fn2()">
        <i class="fas fa-sync-alt"></i>
        <h3>Returns & Refunds</h3>
        <p>Request a return, refund or exchange easily.</p>
      </div>

      <div class="help-card" onclick="fn3()">
        <i class="fas fa-lock"></i>
        <h3>Account Security</h3>
        <p>Manage passwords, privacy settings and security options.</p>
      </div>

      <div class="help-card" onclick="fn4()">
        <i class="fas fa-headset"></i>
        <h3>Contact Support</h3>
        <p>Call us at 1800-123-4567 or email support@shopsphere.com</p>
      </div>
    </div>

    <div class="contact-info">
      <h2>Contact Information</h2>
      <div class="contact-details">
        <div class="contact-item">
          <i class="fas fa-phone"></i>
          <span>1800-123-4567 (Toll Free)</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-envelope"></i>
          <span>support@shopsphere.com</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-clock"></i>
          <span>Available 24/7</span>
        </div>
      </div>
    </div>
  </div>

  <footer class="site-footer">
    <p>&copy; 2025 ShopSphere. All Rights Reserved. | <a href="privacy.php">Privacy Policy</a> | <a href="terms.php">Terms of Service</a></p>
  </footer>
  
  <script src="../js/darkMode.js"></script>
  <script>
    function fn1(){
      window.location.href = "orders.php";
    }
    
    function fn2(){
      // Returns & Refunds functionality
      alert("Returns & Refunds feature coming soon!");
    }
    
    function fn3(){
      // Account Security functionality
      window.location.href = "profile.php";
    }
    
    function fn4(){
      window.location.href = "contactUs.php";
    }
    
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