<?php
session_start();
session_unset();
session_destroy();

$redirect = isset($_GET['timeout']) ? "login.php?timeout=1" : "login.php";
header("Location: " . $redirect);
exit();
?>