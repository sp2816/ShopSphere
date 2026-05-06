<?php
$host = "localhost";
$user = "root";       // default in WAMP
$pass = "";           // leave empty unless you set one
$db   = "shopsphere_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
