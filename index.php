<?php
session_start();

// Guard — redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "connection.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <meta name="ias-user" content="<?= htmlspecialchars($_SESSION['username']) ?>">
</head>
<body>
    <?php echo $menubar["menu"]; ?>
</body>
</html>