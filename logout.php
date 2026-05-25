<?php
session_start();
$_SESSION['pending_2fa'] = null;
unset($_SESSION['pending_2fa']);
$_SESSION['pending_2fa_username'] = null;
unset($_SESSION['pending_2fa_username']);
session_unset();
session_destroy();

$redirect = isset($_GET['timeout']) ? "login.php?timeout=1" : "login.php";
header("Location: " . $redirect);
exit();
?>