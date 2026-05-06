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
  <title>Terms & Conditions</title>
  <link rel="stylesheet" href="../css/terms.css">
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
  <main class="terms-container">
    <h1>Terms & Conditions</h1>
    <p>Welcome to ShopSphere! By accessing and using our website, you agree to comply with the following terms and conditions.</p>

    <section>
      <h2>1. Acceptance of Terms</h2>
      <p>By using our platform, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
    </section>

    <section>
      <h2>2. User Responsibilities</h2>
      <p>You are responsible for maintaining the confidentiality of your account and password, and for restricting access to your device. You agree to accept responsibility for all activities that occur under your account.</p>
    </section>

    <section>
      <h2>3. Product Information</h2>
      <p>We strive to be as accurate as possible in product descriptions. However, we do not guarantee that all content on the site is accurate, complete, or error-free.</p>
    </section>

    <section>
      <h2>4. Intellectual Property</h2>
      <p>All content on this site including logos, images, text, graphics, and software is the property of ShopSphere and is protected by copyright laws.</p>
    </section>

    <section>
      <h2>5. Limitation of Liability</h2>
      <p>ShopSphere shall not be liable for any direct or indirect damages arising out of the use or inability to use our platform.</p>
    </section>

    <section>
      <h2>6. Modifications</h2>
      <p>We reserve the right to change these terms at any time without prior notice. Continued use of the site constitutes your acceptance of any changes.</p>
    </section>

    <section>
      <h2>7. Contact Us</h2>
      <p>If you have any questions about these Terms, please contact us at <strong>support@shopsphere.com</strong>.</p>
    </section>
  </main>
  <footer>
    <p>&copy; 2025 ShopSphere. All rights reserved.</p>
  </footer>
</body>
</html>