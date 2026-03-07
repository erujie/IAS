<?php
session_start();
$timeout = 10;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();

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

    <script>
        let timer;
        const TIMEOUT_MS = 10000;

        function resetTimer() {
            clearTimeout(timer);
            timer = setTimeout(() => {
                window.location.href = 'logout.php?timeout=1';
            }, TIMEOUT_MS);
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click']
            .forEach(evt => document.addEventListener(evt, resetTimer));

        resetTimer();
    </script>
</body>
</html>