<?php
session_start();

$error_message = '';

// Database connection
$servername = "localhost";
$username = "root";  // default in WAMP
$password = "";      // default empty in WAMP
$dbname = "shopsphere_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    //  Use prepared statement (to avoid SQL injection)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Compare plain password (since you decided NOT to hash)
        if ($pass === $row['password']) {
            // Store user info in session
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];

            header("Location: homepage.php"); //  redirect to PHP homepage
            exit();
        } else {
            $error_message = "Incorrect password";
        }
    } else {
        $error_message = "User not found";
    }
}

// Get error from URL if redirected
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="../css/login.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <div class="login-container">
    <h1>Login to ShopSphere</h1>

    <?php if (!empty($error_message)): ?>
      <div style="color: red; margin-bottom: 10px;">
        <?php echo htmlspecialchars($error_message); ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="post" class="login-form">
      <label for="email">Email or Mobile Number</label>
      <input type="text" id="email" name="email" placeholder="Enter your email or phone" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required>

      <button type="submit">Login</button>

      <div class="google-login">
        <a href="https://google.com" class="google-btn">
            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg" alt="Google Logo">
            Login with Google
        </a>
      </div>

      <p class="login-extra"> 
        <a href="register.php">New User? Register</a>
      </p>
    </form>
  </div>

</body>
</html>
