document.addEventListener("DOMContentLoaded", () => {
  console.log("Mobiles.js loaded successfully!");
  const addCartButtons = document.querySelectorAll(".add-cart");
  const buyNowButtons = document.querySelectorAll(".buy-now");

  addCartButtons.forEach(button => {
    button.addEventListener("click", () => {
      const card = button.closest(".mobile-card");
      const name = card.querySelector("h3").textContent;
      const price = card.querySelector("p").textContent;

      // Simulate adding to cart (or use localStorage)
      alert(`${name} added to cart!`);
    });
  });

  buyNowButtons.forEach(button => {
    button.addEventListener("click", () => {
      const card = button.closest(".mobile-card");
      const name = card.querySelector("h3").textContent;

      // Simulate redirect to checkout
      alert(`Redirecting to checkout for ${name}`);
      window.location.href = "payment.php"; // Change this if your checkout page is named differently
    });
  });

  // Dark Mode Functionality
  const darkToggle = document.getElementById('darkToggle');
  console.log("Dark toggle element:", darkToggle);
  
  // Migrate old localStorage values to new format
  const oldDarkMode = localStorage.getItem('darkMode');
  if (oldDarkMode === 'true') {
    localStorage.setItem('darkMode', 'enabled');
  } else if (oldDarkMode === 'false') {
    localStorage.setItem('darkMode', 'disabled');
  }
  
  // Check for saved dark mode preference
  const isDarkMode = localStorage.getItem('darkMode') === 'enabled';
  console.log("Is dark mode enabled:", isDarkMode);
  
  // Apply saved preference on page load
  if (isDarkMode) {
    document.body.classList.add('dark-mode');
    if (darkToggle) darkToggle.checked = true;
  }

  // Toggle dark mode when switch is clicked
  if (darkToggle) {
    console.log("Adding event listener to dark toggle");
    darkToggle.addEventListener('change', () => {
      console.log("Dark toggle clicked, checked:", darkToggle.checked);
      if (darkToggle.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
      } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
      }
    });
  } else {
    console.log("Dark toggle element not found!");
  }

  // Dropdown menu toggle functionality
  const menuToggle = document.getElementById('menuToggle');
  const dropdownMenu = document.getElementById('dropdownMenu');
  console.log("Menu toggle element:", menuToggle);
  console.log("Dropdown menu element:", dropdownMenu);

  if (menuToggle && dropdownMenu) {
    console.log("Adding click event listener to menu toggle");
    menuToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      console.log("Menu toggle clicked!");
      console.log("Dropdown classes before toggle:", dropdownMenu.className);
      dropdownMenu.classList.toggle('show');
      console.log("Dropdown classes after toggle:", dropdownMenu.className);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
      dropdownMenu.classList.remove('show');
    });

    // Prevent dropdown from closing when clicking inside it
    dropdownMenu.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  } else {
    console.log("Menu toggle or dropdown menu not found!");
  }
});
