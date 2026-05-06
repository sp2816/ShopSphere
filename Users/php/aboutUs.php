<?php
session_start();

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$userName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - ShopSphere</title>
    <link rel="stylesheet" href="../css/aboutUs.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    
    <!-- Main Content -->
    <main class="about-container">
    <h1>About Us</h1>
    <p>Welcome to ShopSphere – your trusted online shopping destination!</p>

    <section class="section">
      <h2>Our Mission</h2>
      <p>To make online shopping accessible, affordable, and enjoyable for everyone across India by delivering top-quality products with excellent service.</p>
    </section>

    <section class="section">
      <h2>What We Offer</h2>
      <ul>
        <li>Wide range of electronics, fashion, and home products</li>
        <li>Fast delivery and easy returns</li>
        <li>Safe and secure payment methods</li>
        <li>24/7 customer support</li>
      </ul>
    </section>

    <section class="section">
      <h2>Meet the Team</h2>
      <p>We're a passionate group of tech enthusiasts, designers, and developers working together to build an easy, powerful, and engaging shopping platform.</p>
    </section>
  </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <p>&copy; 2025 ShopSphere. All rights reserved.</p>
            <div class="footer-links">
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Service</a>
                <a href="contactUs.php">Contact</a>
            </div>
        </div>
    </footer>
  <script src="../js/infoPages.js"></script>
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