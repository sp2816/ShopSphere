
  let slideIndex = 0;
  const slides = document.querySelectorAll('.slide');
  const totalSlides = slides.length;

  // Show the current slide
  function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    slides[index].classList.add('active');
  }

  // Auto-slide every 3s
  function autoSlide() {
    slideIndex = (slideIndex + 1) % totalSlides;
    showSlide(slideIndex);
  }

  // Manual slide control
  function changeSlide(n) {
    slideIndex = (slideIndex + n + totalSlides) % totalSlides;
    showSlide(slideIndex);
  }

  // Initialize first slide
  showSlide(slideIndex);
  setInterval(autoSlide, 3000);


//Dropdown three dots

  const menuToggle = document.getElementById("menuToggle");
  const dropdownMenu = document.getElementById("dropdownMenu");

  menuToggle.addEventListener("click", function (e) {
    e.stopPropagation(); // prevent bubbling
    dropdownMenu.classList.toggle("show");
  });

  // Close dropdown if clicking outside
  document.addEventListener("click", function (e) {
    if (!menuToggle.contains(e.target)) {
      dropdownMenu.classList.remove("show");
    }
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


//Category row dropdown

  const fashionToggle = document.getElementById("fashionToggle");
  const fashionMenu = document.getElementById("fashionMenu");

  fashionToggle.addEventListener("click", function (e) {
    e.stopPropagation(); // Prevent closing immediately
    fashionMenu.classList.toggle("show");
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", function (e) {
    if (!fashionToggle.contains(e.target)) {
      fashionMenu.classList.remove("show");
    }
  });



