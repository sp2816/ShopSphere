<?php
// Database setup script for admin functionality
$conn = mysqli_connect("localhost", "root", "", "shopsphere_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Setting up database tables for admin functionality...</h2>";

// Read the SQL file
$sql_file = 'setup_admin_tables.sql';
if (!file_exists($sql_file)) {
    die("SQL file not found: $sql_file");
}

$sql_content = file_get_contents($sql_file);

// Split SQL commands by semicolon
$sql_commands = explode(';', $sql_content);

$success_count = 0;
$error_count = 0;

foreach ($sql_commands as $command) {
    $command = trim($command);
    
    // Skip empty commands and comments
    if (empty($command) || strpos($command, '--') === 0) {
        continue;
    }
    
    echo "<p>Executing: " . substr($command, 0, 50) . "...</p>";
    
    if (mysqli_query($conn, $command)) {
        $success_count++;
        echo "<p style='color: green;'>✓ Success</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
    }
}

echo "<hr>";
echo "<h3>Setup Summary:</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<p style='color: green; font-weight: bold;'>✓ Database setup completed successfully!</p>";
    echo "<p><a href='admin/php/dashboard.php'>Go to Admin Dashboard</a></p>";
} else {
    echo "<p style='color: orange;'>⚠ Setup completed with some errors. Check the details above.</p>";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2 { color: #333; }
        p { margin: 5px 0; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Database Setup Instructions</h2>
    <p>1. Make sure your WAMP server is running</p>
    <p>2. Access this file via: <code>http://localhost/OnlineShoppingProject/setup_database.php</code></p>
    <p>3. The script will create all necessary tables for the admin functionality</p>
    <p>4. After successful setup, you can access the admin panel</p>
</body>
</html>