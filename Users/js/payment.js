document.getElementById("paymentForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const selected = document.querySelector('input[name="payment"]:checked');
  if (!selected) {
    alert("Please select a payment method.");
    return;
  }

  const method = selected.value;

  // Validation based on selected method
  if (method === "card") {
    const cardNumber = document.querySelector(".card-details input[placeholder='Card Number']").value.trim();
    const cardName = document.querySelector(".card-details input[placeholder='Cardholder Name']").value.trim();
    const expiry = document.querySelector(".card-details input[placeholder='MM/YY']").value.trim();
    const cvv = document.querySelector(".card-details input[placeholder='CVV']").value.trim();

    if (!cardNumber || !cardName || !expiry || !cvv) {
      alert("Please fill all card details.");
      return;
    }

    if (!/^\d{16}$/.test(cardNumber.replace(/\s+/g, ''))) {
      alert("Please enter a valid 16-digit card number.");
      return;
    }

    if (!/^\d{2}\/\d{2}$/.test(expiry)) {
      alert("Enter expiry in MM/YY format.");
      return;
    }

    if (!/^\d{3}$/.test(cvv)) {
      alert("Enter a valid 3-digit CVV.");
      return;
    }

  } else if (method === "upi") {
    const upiId = document.querySelector(".upi-details input").value.trim();
    if (!upiId) {
      alert("Please enter your UPI ID.");
      return;
    }

    if (!/^[\w.-]+@[\w.-]+$/.test(upiId)) {
      alert("Enter a valid UPI ID (e.g., yourname@bank).");
      return;
    }
  }

  // If validations pass
  alert("Payment Successful using " + method.toUpperCase() + "!");
  window.location.href = "orders.html";
});

// Dark Mode Functionality
document.addEventListener('DOMContentLoaded', function() {
  const darkModeToggle = document.getElementById('darkModeToggle');
  
  // Load saved dark mode preference
  if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    if (darkModeToggle) {
      darkModeToggle.checked = true;
    }
  }
  
  // Toggle dark mode
  if (darkModeToggle) {
    darkModeToggle.addEventListener('change', function() {
      if (this.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
      } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
      }
    });
  }
});
