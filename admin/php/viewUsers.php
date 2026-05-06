<?php
$conn = mysqli_connect("localhost","root","","shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$sql = "select * from users ORDER BY name asc";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Users Management</title>
  <link rel="stylesheet" href="../css/navbar.css">
  <link rel="stylesheet" href="../css/adminUsers.css">
  <link rel="stylesheet" href="../css/viewUsers.css">
</head>
<body>
  <!-- Include Admin Navbar -->
  <header class="main-header">
      <div class="logo">ShopSphere<span class="plus">Admin</span></div>
      <nav style="flex:1;">
          <ul class="admin-nav-items">
              <li><a class="link" href="dashboard.php">Dashboard</a></li>
              <li><a class="link" href="products.php">Products</a></li>
              <li><a class="link" href="orders.php">Orders</a></li>
              <li><a class="link" href="users.php" style="background: #f1f3f6; color: #2874f0;">Users</a></li>
          </ul>
      </nav>
      <div style="display: flex; align-items: center; gap: 20px; position: relative;">
          <div class="dropdown" style="position: relative;">
              <img src="../../images/men1.jpg" alt="Admin" id="userImg" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #2874f0; cursor:pointer;">
              <div class="dropdown-content right" id="userDropdown" style="right:0; min-width:140px;">
                  <a href="profile.php">Profile</a>
                  <a href="settings.php">Settings</a>
                  <a href="logout.php">Logout</a>
              </div>
          </div>
      </div>
  </header>

  <div class="container">
    <div class="page-header">
      <h1>Users Management</h1>
      <p>Manage and monitor all registered users</p>
    </div>

    <div class="users-stats">
      <div class="stat-card">
        <h3><?php echo $result->num_rows; ?></h3>
        <p>Total Users</p>
      </div>
      <div class="stat-card">
        <h3>0</h3>
        <p>Active Today</p>
      </div>
      <div class="stat-card">
        <h3>0</h3>
        <p>New This Month</p>
      </div>
    </div>

    <div class="table-container">
      <div class="table-header">
        <h2>All Users</h2>
        <input type="text" class="search-box" placeholder="Search users..." id="searchInput">
      </div>
      <table id="usersTable">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Password</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  $initials = strtoupper(substr($row['name'], 0, 2));
                  echo "<tr>
                          <td>
                            <div class='user-info'>
                              <div class='user-avatar'>".$initials."</div>
                              <div>
                                <strong>".$row['name']."</strong>
                              </div>
                            </div>
                          </td>
                          <td>".$row['email']."</td>
                          <td>".$row['password']."</td>   
                          <td>
                            <div class='action-buttons'>
                              <a href='editUser.php?id={$row['name']}' class='btn btn-edit'>✏️ Edit</a>
                              <a href='deleteUser.php?id={$row['name']}' class='btn btn-delete' onclick=\"return confirm('Are you sure you want to delete this user?');\">🗑️ Delete</a>
                            </div>
                          </td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='4' class='no-users'>
                      <i>👥</i>
                      <div>No users found</div>
                      <small>Users will appear here once they register</small>
                    </td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Navbar dropdown functionality
    document.getElementById('userImg').addEventListener('click', function() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const userImg = document.getElementById('userImg');
        if (!userImg.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Logout confirmation
    document.addEventListener('DOMContentLoaded', function() {
        const logoutLinks = document.querySelectorAll('a[href*="logout.php"]');
        logoutLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = this.href;
                }
            });
        });
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#usersTable tbody tr');
        
        tableRows.forEach(row => {
            const userName = row.querySelector('.user-info strong').textContent.toLowerCase();
            const userEmail = row.cells[1].textContent.toLowerCase();
            
            if (userName.includes(searchValue) || userEmail.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
  </script>
</body>
</html>
<?php
$conn->close();     
?>