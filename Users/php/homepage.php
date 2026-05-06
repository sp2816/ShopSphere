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
  <title>ShopSphere</title>
  
  <link rel="stylesheet" href="../css/homepage.css?v=<?php echo time(); ?>">
  <!-- Font Awesome CDN -->
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
  </header>

  <!-- Category Menu Row -->
<section class="category-row">
  <div class="category-item">
    <img src="../images/grocery.jpg" alt="Grocery">
    <a href="grocery.php">Grocery</a>
  </div>
  <div class="category-item">
    <img src="../images/mobile.jpg" alt="Mobiles">
    <a href="mobiles.php">Mobiles</a>
  </div>
  <!-- Fashion Category with Dropdown -->
  <div class="category-item dropdown-category" id="fashionToggle">
    <img src="https://img.icons8.com/color/48/clothes.png" alt="Fashion">
    <p>Fashion <i class="fas fa-chevron-down small-icon"></i></p>
    
    <div class="dropdown-menu" id="fashionMenu">
      <a href="men.php">Men</a>
      <a href="women.php">Women</a>
    </div>
  </div>


  <!--<div class="category-item">
    <img src="../images/electronics.jpg" alt="Electronics">
    <a href="electronics.html">Electronics</a>
  </div>-->
  <!--<div class="category-item">
    <img src="../images/appliances.jpg" alt="Appliances">
    <a href="appliances.html">Home Appliances</a>
  </div>-->
  <div class="category-item">
    <img src="../images/beauty.jpg" alt="Beauty">
    <a href="beauty.php">Beauty</a>
  </div>

  <!--<div class="category-item">
    <img src="../images/toys.jpg" alt="Toys">
   <a href="toys.html">Toys and more</a>
  </div>-->
</section>

<!-- Main Banner -->
<section class="main-banner">
  <div class="slideshow-container">
    <img class="slide" src="../images/banner1.jpg" alt="Slide 1">
    <img class="slide" src="../images/banner2.jpg" alt="Slide 2">
    <img class="slide" src="../images/banner3.jpg" alt="Slide 3">


    <a class="prev" onclick="changeSlide(-1)">&#10094;</a>3
    <a class="next" onclick="changeSlide(1)">&#10095;</a>
  </div>
</section>

<!-- Product Section: Best of Electronics -->
<section class="product-section">
  <h2>Best of Mobiles</h2>
  <div class="product-row">
    <div class="product-card">
      <img src="../images/mobile1.jpg" alt="Product">
      <p>Earbuds</p>
    </div>
    <div class="product-card">
      <img src="../images/mobile2.jpg" alt="Product">
      <p>Noice Smartwatches</p>
    </div>
    <div class="product-card">
      <img src="../images/mobile3.jpg" alt="Product">
      <p>Printers</p>
    </div>
    <div class="product-card">
      <img src="../images/mobile4.jpg" alt="Product">
      <p>Monitor</p>
    </div>
    <div class="product-card">
      <img src="../images/mobile5.jpg" alt="Product">
      <p>Projector</p>
    </div>
    <div class="product-card">
      <img src="../images/mobile6.jpg" alt="Product">
      <p>Speaker</p>
    </div>
  </div>
</section>

<section class="product-section">
  <h2>Best of Beauty</h2>
  <div class="product-row">
    <div class="product-card">
      <img src="../images/perfume.jpg" alt="Perfume">
      <p>Fogg Perfume</p>
    </div>
    <div class="product-card">
      <img src="../images/beauty3.jpg" alt="Lip Balm">
      <p>Nivea Lip Balm</p>
    </div>
    <div class="product-card">
      <img src="../images/beauty2.jpg" alt="Moisturizer">
      <p>Pond's Moisturizer</p>
    </div>
    <div class="product-card">
      <img src="../images/beauty4.jpg" alt="Shampoo">
      <p>Dove Shampoo</p>
    </div>
    <div class="product-card">
      <img src="../images/beauty1.jpg" alt="Face Wash">
      <p>Himalaya Face Wash</p>
    </div>
    <div class="product-card">
      <img src="../images/lipstick.jpg" alt="Face Wash">
      <p>Swiss Beauty lipstick</p>
    </div>
  </div>
</section>


<!-- Footer -->
<footer class="site-footer">
  <p>&copy; 2025 ShopSphere Clone. All rights reserved.</p>
  <p>
    <a href="aboutUs.php">About</a> |
    <a href="customerCare.php">Contact</a> |
    <a href="terms.php">Terms</a> |
    <a href="privacy.php">Privacy</a>
  </p>
</footer>

<script src = "../js/homepage.js"></script>
<script src = "../js/darkMode.js"></script>
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