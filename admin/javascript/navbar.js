// Navbar dropdown logic for admin pages
window.addEventListener('DOMContentLoaded', function() {
    var userImg = document.getElementById('userImg');
    var userDropdown = document.getElementById('userDropdown');
    if (userImg && userDropdown) {
        userDropdown.style.display = 'none';
        userImg.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.style.display = (userDropdown.style.display === 'block') ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (userDropdown.style.display === 'block') {
                userDropdown.style.display = 'none';
            }
        });
    }
    
    // Logout confirmation
    var logoutLinks = document.querySelectorAll('a[href*="logout.php"]');
    logoutLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = this.href;
            }
        });
    });
});
