<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get item details from URL parameters
$item_name = isset($_GET['name']) ? $_GET['name'] : '';
$item_price = isset($_GET['price']) ? $_GET['price'] : 0;
$item_image = isset($_GET['image']) ? $_GET['image'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment - ShopSphere</title>
  <link rel="stylesheet" href="../css/payment.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <header class="main-header">
    <div class="logo">ShopSphere <span class="plus">Explore Plus ✨</span></div>
    <div class="header-right">
      <div class="dark-mode-toggle">
        <label class="switch">
          <input type="checkbox" id="darkToggle" onchange="toggleDarkMode()">
          <span class="slider round"></span>
        </label>
        <span>Dark Mode</span>
      </div>
    </div>
  </header>

  <a href="cart.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Cart</a>

  <main class="payment-container">
    <h1>Choose Payment Method</h1>

    <form id="paymentForm">
      <!-- Card Payment -->
      <div class="payment-option">
        <h3><input type="radio" name="payment" value="card" required> Credit/Debit Card</h3>
        <div class="card-details">
          <input type="text" placeholder="Card Number" maxlength="19">
          <input type="text" placeholder="Cardholder Name">
          <div class="card-row">
            <input type="text" placeholder="MM/YY" maxlength="5">
            <input type="text" placeholder="CVV" maxlength="3">
          </div>
        </div>
      </div>

      <!-- UPI -->
      <div class="payment-option">
        <h3><input type="radio" name="payment" value="upi"> UPI</h3>
        <div class="upi-details">
          <input type="text" placeholder="yourupi@bank">
        </div>
      </div>

      <!-- Cash on Delivery -->
      <div class="payment-option">
        <h3><input type="radio" name="payment" value="cod"> Cash on Delivery (COD)</h3>
      </div>

      <button type="submit" class="pay-btn">Confirm Payment</button>
    </form>
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
   
  <script src="../js/payment.js"></script>
  <script>
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