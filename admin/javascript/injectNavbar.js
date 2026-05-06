const navbarHTML = `
<header class="main-header">
    <div class="logo">ShopSphere<span class="plus">Admin</span></div>
    <nav style="flex:1;">
        <ul class="admin-nav-items">
            <li><a class="link" href="dashboard.php">Dashboard</a></li>
            <li><a class="link" href="products.php">Products</a></li>
            <li><a class="link" href="orders.php">Orders</a></li>
            <li><a class="link" href="users.php">Users</a></li>
        </ul>
    </nav>
    <div style="display: flex; align-items: center; gap: 20px; position: relative;">
        <div class="dropdown" style="position: relative;">
            <img src="../../images/men1.jpg" alt="Admin" id="userImg" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #2874f0; cursor:pointer;">
            <div class="dropdown-content right" id="userDropdown" style="right:0; min-width:140px;">
                <a href="profile.php">Profile</a>
                <a href="settings.php">Settings</a>
                <a href="../../php/logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>`;

window.addEventListener('DOMContentLoaded', function() {
    document.body.insertAdjacentHTML('afterbegin', navbarHTML);
});
