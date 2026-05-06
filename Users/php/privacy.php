<?php
session_start();
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
  <title>Privacy Policy</title>
  <link rel="stylesheet" href="../css/privacy.css">
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
    </div>
  </header>
  

  <a href="homepage.php" class="back-btn">← Back to Home</a>
  <main class="privacy-container">
    <h1>Privacy Policy</h1>
    <p>At ShopSphere, we value your privacy. This policy outlines how we collect, use, and protect your personal information.</p>

    <section>
      <h2>1. Information We Collect</h2>
      <p>We collect personal details like your name, email address, phone number, and address when you register or make a purchase on our platform.</p>
    </section>

    <section>
      <h2>2. Use of Your Information</h2>
      <p>We use your information to process orders, improve customer service, personalize your experience, and communicate important updates and offers.</p>
    </section>

    <section>
      <h2>3. Cookies</h2>
      <p>We use cookies to understand user behavior, track preferences, and enhance your overall experience on our website.</p>
    </section>

    <section>
      <h2>4. Data Security</h2>
      <p>We implement secure protocols to protect your data. However, no method of transmission over the Internet is 100% secure.</p>
    </section>

    <section>
      <h2>5. Sharing of Information</h2>
      <p>We do not sell or rent your personal information to third parties. We may share data with trusted service providers under strict confidentiality agreements.</p>
    </section>

    <section>
      <h2>6. Your Choices</h2>
      <p>You can update your preferences or delete your account at any time. You may also opt out of marketing emails.</p>
    </section>

    <section>
      <h2>7. Changes to Policy</h2>
      <p>This policy may be updated periodically. Changes will be posted on this page with a revised effective date.</p>
    </section>

    <section>
      <h2>8. Contact Us</h2>
      <p>If you have questions about this Privacy Policy, email us at <strong>support@shopsphere.com</strong>.</p>
    </section>
  </main>
  <footer>
    <p>&copy; 2025 ShopSphere. All rights reserved.</p>
  </footer>
</body>
</html>



