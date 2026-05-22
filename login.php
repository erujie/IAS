<?php
session_start();
include "connection.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_submit'])) {
    $username_input = trim($_POST['username']      ?? '');
    $password_input =      $_POST['password']      ?? '';
    $captcha_input  = strtoupper(trim($_POST['captcha_input'] ?? ''));

    //Commented to bypass captcha for testing, will be re-enabled in production
    // if (empty($_SESSION['captcha_code']) || $captcha_input !== $_SESSION['captcha_code']) {
    //     $error = "Incorrect security code.";
    //     unset($_SESSION['captcha_code']);
    // } else
    if (empty($username_input) || empty($password_input)) {
        $error = "Please fill in all fields.";
        unset($_SESSION['captcha_code']);
    } else {
        unset($_SESSION['captcha_code']);
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE AES_DECRYPT(username, ?) = ?");
        $key  = ENCRYPT_KEY;
        $stmt->bind_param("ss", $key, $username_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password_input, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $username_input;
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    }
}

$formStmt = $conn->prepare("SELECT form_html FROM forms WHERE form_name = 'login'");
$formStmt->execute();
$form_html = $formStmt->get_result()->fetch_assoc()['form_html'] ?? '';
$formStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — IAS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #111;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 40px 36px;
            width: 100%;
            max-width: 380px;
        }

        .brand {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.15em;
            color: #007bff;
            margin-bottom: 6px;
        }

        h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 13px;
            color: #888;
            margin-bottom: 28px;
        }

        .error-msg {
            font-size: 13px;
            color: #cc0000;
            border: 1px solid #f5c2c2;
            background: #fff5f5;
            border-radius: 4px;
            padding: 9px 12px;
            margin-bottom: 18px;
        }

        .form-group { margin-bottom: 16px; }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            color: #111;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-input:focus { border-color: #007bff; }

        .pw-wrap { position: relative; }
        .pw-wrap .form-input { padding-right: 40px; }
        .toggle-pw {
            position: absolute; right: 10px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; font-size: 15px; color: #888;
        }

        .captcha-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .captcha-img { border: 1px solid #ccc; border-radius: 4px; height: 48px; width: 150px; display: block; }
        .captcha-refresh {
            background: none; border: 1px solid #ccc; border-radius: 4px;
            width: 36px; height: 36px; font-size: 18px;
            cursor: pointer; color: #555;
            display: flex; align-items: center; justify-content: center;
        }
        .captcha-refresh:hover { border-color: #007bff; color: #007bff; }
        .captcha-input { letter-spacing: 0.18em; }

        .btn-login {
            width: 100%;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 11px;
            font-size: 14px;
            font-weight: 600;
            font-family: Arial, sans-serif;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.15s;
        }
        .btn-login:hover { background: #0069d9; }

        .form-footer {
            font-size: 13px;
            color: #888;
            text-align: center;
            margin-top: 20px;
        }
        .form-footer a { color: #007bff; text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }

        .hint { font-size: 11px; color: #aaa; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="card">

        <?php if (isset($_GET['timeout'])): ?>
            <div class="error-msg">Session expired due to inactivity.</div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= $form_html ?>
        </form>
    </div>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            pw.type = (pw.type === 'password') ? 'text' : 'password';
        }
        function refreshCaptcha() {
            document.getElementById('captcha-img').src = 'captcha.php?' + Date.now();
        }
    </script>
</body>
</html>