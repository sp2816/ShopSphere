document.addEventListener("DOMContentLoaded", () => {
  // Existing functionality
  document.querySelectorAll('.add-cart').forEach(button => {
    button.addEventListener('click', () => {
      alert('Item added to cart!');
      // You can also store in localStorage or backend if needed
    });
  });

  document.querySelectorAll('.buy-now').forEach(button => {
    button.addEventListener('click', () => {
      alert('Redirecting to checkout...');
      window.location.href = 'payment.php'; // simulate checkout
    });
  });

  // Dark Mode Functionality
  const darkToggle = document.getElementById('darkToggle');
  
  // Migrate old localStorage values to new format
  const oldDarkMode = localStorage.getItem('darkMode');
  if (oldDarkMode === 'true') {
    localStorage.setItem('darkMode', 'enabled');
  } else if (oldDarkMode === 'false') {
    localStorage.setItem('darkMode', 'disabled');
  }
  
  // Check for saved dark mode preference
  const isDarkMode = localStorage.getItem('darkMode') === 'enabled';
  
  // Apply saved preference on page load
  if (isDarkMode) {
    document.body.classList.add('dark-mode');
    if (darkToggle) darkToggle.checked = true;
  }

  // Toggle dark mode when switch is clicked
  if (darkToggle) {
    darkToggle.addEventListener('change', () => {
      if (darkToggle.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
      } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
      }
    });
  }

  // Dropdown menu toggle functionality
  const menuToggle = document.getElementById('menuToggle');
  const dropdownMenu = document.getElementById('dropdownMenu');

  if (menuToggle && dropdownMenu) {
    menuToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
      dropdownMenu.classList.remove('show');
    });

    // Prevent dropdown from closing when clicking inside it
    dropdownMenu.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  }
});
