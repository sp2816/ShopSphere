document.addEventListener("DOMContentLoaded", function () {
  const cartContainer = document.querySelector(".cart-container");
  const totalElement = document.querySelector(".cart-summary h2");
  const checkoutBtn = document.querySelector(".checkout-btn");

  function updateTotal() {
    const cartItems = document.querySelectorAll(".cart-item");
    let total = 0;

    cartItems.forEach(item => {
      const priceText = item.querySelector(".item-info p:nth-of-type(2)").textContent;
      const price = parseFloat(priceText.replace("₹", "").replace(",", ""));
      const qty = parseInt(item.querySelector(".qty span").textContent);
      total += price * qty;
    });

    totalElement.textContent = `Total: ₹${total.toLocaleString("en-IN")}`;

    // Disable checkout if no items
    if (cartItems.length === 0) {
      document.querySelector(".cart-summary").innerHTML = `
        <h2>Your cart is empty</h2>
        <a href="homepage.html" class="checkout-btn">Continue Shopping</a>
      `;
    }
  }

  document.querySelectorAll(".cart-item").forEach(item => {
    const qtySpan = item.querySelector(".qty span");
    const minusBtn = item.querySelector(".qty button:first-child");
    const plusBtn = item.querySelector(".qty button:last-child");
    const removeBtn = item.querySelector(".remove-btn");

    plusBtn.addEventListener("click", () => {
      let qty = parseInt(qtySpan.textContent);
      qtySpan.textContent = qty + 1;
      updateTotal();
    });

    minusBtn.addEventListener("click", () => {
      let qty = parseInt(qtySpan.textContent);
      if (qty > 1) {
        qtySpan.textContent = qty - 1;
        updateTotal();
      }
    });

    removeBtn.addEventListener("click", () => {
      item.remove();
      updateTotal();
    });
  });

  checkoutBtn.addEventListener("click", () => {
    const cartItems = document.querySelectorAll(".cart-item");
    if (cartItems.length === 0) {
      alert("Your cart is empty!");
      return;
    }
    alert("Thank you for your purchase! Redirecting to payment...");
    // You can redirect to a new page like this:
    window.location.href = "payment.php"; // or thankyou.php
  });

  updateTotal();

  // Dark Mode Toggle
  const darkToggle = document.getElementById("darkToggle");
  const menuToggle = document.getElementById("menuToggle");
  const dropdownMenu = document.getElementById("dropdownMenu");

  // Apply previously saved mode
  if (localStorage.getItem("darkMode") === "enabled") {
    document.body.classList.add("dark-mode");
    if (darkToggle) darkToggle.checked = true;
  }

  // Handle toggle
  if (darkToggle) {
    darkToggle.addEventListener("change", () => {
      if (darkToggle.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem("darkMode", "enabled");
      } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem("darkMode", "disabled");
      }
    });
  }

  // Dropdown menu toggle for 3-dot icon
  if (menuToggle && dropdownMenu) {
    menuToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle("show");
    });

    // Close dropdown on outside click
    document.addEventListener("click", (e) => {
      if (!menuToggle.contains(e.target)) {
        dropdownMenu.classList.remove("show");
      }
    });
  }
});
