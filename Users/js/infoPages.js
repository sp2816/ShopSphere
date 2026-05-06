document.addEventListener("DOMContentLoaded", () => {
  // Dark Mode Toggle
  const darkToggle = document.getElementById("darkToggle");
  const body = document.body;

  // Apply previously saved mode
  if (localStorage.getItem("darkMode") === "enabled") {
    body.classList.add("dark-mode");
    if (darkToggle) darkToggle.checked = true;
  }

  // Handle toggle
  if (darkToggle) {
    darkToggle.addEventListener("change", () => {
      if (darkToggle.checked) {
        body.classList.add("dark-mode");
        localStorage.setItem("darkMode", "enabled");
      } else {
        body.classList.remove("dark-mode");
        localStorage.setItem("darkMode", "disabled");
      }
    });
  }

  // Dropdown menu toggle for 3-dot icon
  const menuToggle = document.getElementById("menuToggle");
  const dropdownMenu = document.getElementById("dropdownMenu");

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

  // Optional: Highlight current nav link
  const currentPath = window.location.pathname.split("/").pop();
  const links = document.querySelectorAll("a");

  links.forEach(link => {
    if (link.getAttribute("href") === currentPath) {
      link.style.fontWeight = "bold";
    }
  });

});
