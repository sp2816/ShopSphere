<?php
session_start();

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$name = isset($_POST["name"]) ? trim($_POST["name"]) : '';
	$email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
	$password = isset($_POST["password"]) ? $_POST["password"] : '';
	$confirmPassword = isset($_POST["confirm-password"]) ? $_POST["confirm-password"] : '';

	// Name validation
	if (strlen($name) < 3) {
		$error_message = "Please enter a valid name (minimum 3 characters).";
	}
	// Email or Mobile validation
	elseif (!empty($email)) {
		$emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
		$phoneRegex = '/^[6-9]\d{9}$/';
		if (!preg_match($emailRegex, $email) && !preg_match($phoneRegex, $email)) {
			$error_message = "Please enter a valid email or mobile number.";
		}
	}
	// Password strength validation
	if (empty($error_message) && !empty($password)) {
		$passwordRegex = '/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
		if (!preg_match($passwordRegex, $password)) {
			$error_message = "Password must be at least 8 characters long and include at least one uppercase letter, one digit, and one special character.";
		}
	}
	// Confirm password match
	if (empty($error_message) && $password !== $confirmPassword) {
		$error_message = "Passwords do not match.";
	}
	
	if (empty($error_message)) {
		// Save to database
		include 'config.php';

		$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $name, $email, $password);

		if ($stmt->execute()) {
			echo "<script>alert('Registration Successful!'); window.location.href='login.php';</script>";
			exit();
		} else {
			$error_message = "Error: " . $stmt->error;
		}
		$conn->close();
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="../css/register.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <div class="register-container">
    <h1>Create Your Account</h1>

    <?php if (!empty($error_message)): ?>
      <div style="color: red; margin-bottom: 10px;">
        <?php echo htmlspecialchars($error_message); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
      <div style="color: green; margin-bottom: 10px;">
        <?php echo htmlspecialchars($success_message); ?>
        <br><a href="login.php">Click here to login</a>
      </div>
    <?php endif; ?>

  <form id="registerForm" action="register.php" method="post" class="register-form" target="_self">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" placeholder="Enter your name" required>

      <label for="email">Email or Mobile Number</label>
      <input type="email" id="email" name="email" placeholder="Enter your email or phone" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter a password" required>

      <label for="confirm-password">Confirm Password</label>
      <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>

      <button type="submit">Register</button>

      <p class="register-extra">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </form>
  </div>

</body>
</html>