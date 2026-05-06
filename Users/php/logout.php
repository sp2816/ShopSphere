<!-- <?php
session_start();
session_unset();
session_destroy();

if(isset($_COOKIE['email'])) {
	setcookie('email', email,time()-3600);
	header("Location: login.php");
}
?> -->

<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php?error=You+have+been+logged+out");
exit();
?>
