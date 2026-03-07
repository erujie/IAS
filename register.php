<?php
session_start();
include "connection.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register_submit'])) {
    $username_input = trim($_POST['username']         ?? '');
    $email_input    = trim($_POST['email']            ?? '');
    $password_input =      $_POST['password']         ?? '';
    $confirm_input  =      $_POST['confirm_password'] ?? '';

    if (empty($username_input) || empty($email_input) || empty($password_input) || empty($confirm_input)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password_input) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password_input !== $confirm_input) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE AES_DECRYPT(username, ?) = ? OR email = ?");
        $key  = ENCRYPT_KEY;
        $stmt->bind_param("sss", $key, $username_input, $email_input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or email is already taken.";
            $stmt->close();
        } else {
            $stmt->close();
            $hashed = password_hash($password_input, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("INSERT INTO users (username, email, password) VALUES (AES_ENCRYPT(?, ?), ?, ?)");
            $stmt->bind_param("ssss", $username_input, $key, $email_input, $hashed);
            if ($stmt->execute()) {
                $success = "Account created. <a href='login.php'>Sign in</a>.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
}

$formStmt = $conn->prepare("SELECT form_html FROM forms WHERE form_name = 'register'");
$formStmt->execute();
$form_html = $formStmt->get_result()->fetch_assoc()['form_html'] ?? '';
$formStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — IAS</title>
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
            padding: 24px;
        }

        .card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 40px 36px;
            width: 100%;
            max-width: 400px;
        }

        .brand {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.15em;
            color: #007bff;
            margin-bottom: 6px;
        }

        h1 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }

        .subtitle { font-size: 13px; color: #888; margin-bottom: 28px; }

        .error-msg {
            font-size: 13px;
            color: #cc0000;
            border: 1px solid #f5c2c2;
            background: #fff5f5;
            border-radius: 4px;
            padding: 9px 12px;
            margin-bottom: 18px;
        }

        .success-msg {
            font-size: 13px;
            color: #1a7a3c;
            border: 1px solid #b6e4c8;
            background: #f0faf4;
            border-radius: 4px;
            padding: 9px 12px;
            margin-bottom: 18px;
        }
        .success-msg a { color: #1a7a3c; }

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
        <h1>Create account</h1>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-msg"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <?= $form_html ?>
        </form>
    </div>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            pw.type = (pw.type === 'password') ? 'text' : 'password';
        }
    </script>
</body>
</html>