<?php
session_start();
include "connection.php";
require_once __DIR__ . '/otp_handler.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$showOtpStep = isset($_SESSION['pending_2fa']) && !empty($_SESSION['pending_2fa']);
$pendingUserId = $showOtpStep ? (int)$_SESSION['pending_2fa'] : 0;
$otpError = "";
$otpSuccess = "";

// Handle OTP submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['otp_submit'])) {
    if (!$showOtpStep) {
        header("Location: login.php");
        exit();
    }
    
    $otpInput = trim($_POST['otp_code'] ?? '');
    $resend = isset($_POST['resend_otp']);
    
    if ($resend) {
        // Handle resend OTP
        $canResend = canResendOtp($pendingUserId, 30);
        if ($canResend['canSend']) {
            // Get user email
            $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->bind_param("i", $pendingUserId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $email = $user['email'];
                
                // Generate and send new OTP
                $otpResult = sendOtpEmail($pendingUserId, $email);
                if ($otpResult['success']) {
                    $otpSuccess = "New OTP sent to your email.";
                } else {
                    $otpError = "Failed to send OTP: " . $otpResult['error'];
                }
            } else {
                $otpError = "User not found.";
            }
            $stmt->close();
        } else {
            $otpError = "Please wait " . $canResend['secondsRemaining'] . " seconds before requesting a new code.";
        }
    } elseif (!empty($otpInput)) {
        // Verify OTP
        if (verifyOtp($pendingUserId, $otpInput)) {
            // OTP valid - complete login
            // Get user details
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
            $stmt->bind_param("i", $pendingUserId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                unset($_SESSION['pending_2fa']);
                header("Location: index.php");
                exit();
            }
            $stmt->close();
        } else {
            $otpError = "Invalid or expired OTP code.";
        }
    } else {
        $otpError = "Please enter the OTP code.";
    }
} 
// Handle regular login submission (step 1)
else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_submit'])) {
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
        $stmt = $conn->prepare("SELECT id, password, username FROM users WHERE AES_DECRYPT(username, ?) = ?");
        $key  = ENCRYPT_KEY;
        $stmt->bind_param("ss", $key, $username_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password_input, $user['password'])) {
                // Password valid - initiate 2FA
                $_SESSION['pending_2fa'] = $user['id'];
                // Store username for display in OTP step
                $_SESSION['pending_2fa_username'] = $username_input;
                
                // Get user email and send OTP
                $email = $user['email'] ?? '';
                if (!empty($email)) {
                    $otpResult = sendOtpEmail($user['id'], $email);
                    if (!$otpResult['success']) {
                        $error = "Failed to send OTP: " . $otpResult['error'];
                        unset($_SESSION['pending_2fa']);
                        unset($_SESSION['pending_2fa_username']);
                    }
                } else {
                    $error = "No email found for user.";
                    unset($_SESSION['pending_2fa']);
                    unset($_SESSION['pending_2fa_username']);
                }
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
    <title><?= $showOtpStep ? 'Verify OTP — IAS' : 'Login — IAS' ?></title>
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

        .btn-secondary {
            width: 100%;
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: Arial, sans-serif;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.15s;
        }
        .btn-secondary:hover { background: #e9ecef; }

        .form-footer {
            font-size: 13px;
            color: #888;
            text-align: center;
            margin-top: 20px;
        }
        .form-footer a { color: #007bff; text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }

        .hint { font-size: 11px; color: #aaa; margin-top: 4px; }

        .otp-info {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .otp-inputs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .otp-input {
            width: 40px;
            height: 48px;
            font-size: 24px;
            text-align: center;
            font-feature-settings: 'tnum';
        }

        .resend-link {
            font-size: 13px;
            color: #007bff;
            text-decoration: none;
            display: block;
            margin-top: 10px;
        }
        .resend-link:hover { text-decoration: underline; }
        .resend-link:disabled { color: #ccc; cursor: not-allowed; }

        .timer {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
            font-family: monospace;
        }
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

        <?php if (!empty($otpError)): ?>
            <div class="error-msg"><?= htmlspecialchars($otpError) ?></div>
        <?php endif; ?>

        <?php if (!empty($otpSuccess)): ?>
            <div class="success-msg"><?= htmlspecialchars($otpSuccess) ?></div>
        <?php endif; ?>

        <?php if ($showOtpStep): ?>
            <!-- OTP Verification Step -->
            <div class="brand">IAS</div>
            <h1>Verify Your Identity</h1>
            <p class="subtitle">Enter the 6-digit code sent to your email</p>
            
            <div class="otp-info">
                We've sent a 6-digit code to <strong><?= htmlspecialchars($_SESSION['pending_2fa_username'] ?? 'your email') ?></strong>. 
                Please check your inbox (and spam folder) and enter the code below.
            </div>

            <form method="POST" action="login.php" autocomplete="off">
                <div class="form-group">
                    <label for="otp_code">OTP Code</label>
                    <div class="otp-inputs">
                        <input type="text" class="otp-input form-input" id="otp1" maxlength="1" autocomplete="off" autofocus>
                        <input type="text" class="otp-input form-input" id="otp2" maxlength="1" autocomplete="off">
                        <input type="text" class="otp-input form-input" id="otp3" maxlength="1" autocomplete="off">
                        <input type="text" class="otp-input form-input" id="otp4" maxlength="1" autocomplete="off">
                        <input type="text" class="otp-input form-input" id="otp5" maxlength="1" autocomplete="off">
                        <input type="text" class="otp-input form-input" id="otp6" maxlength="1" autocomplete="off">
                    </div>
                    <input type="hidden" name="otp_code" id="otp_code_hidden">
                </div>

                <button type="submit" name="otp_submit" class="btn-login">Verify Code</button>
                <button type="submit" name="otp_submit" value="1" name="resend_otp" class="btn-secondary">
                    Resend Code
                </button>
                <div id="timer" class="timer"></div>

                <div class="form-footer">
                    Back to <a href="login.php">Login Page</a>
                </div>
            </form>
        <?php else: ?>
            <!-- Regular Login Form -->
            <div class="brand">IAS</div>
            <h1>Login to your account</h1>
            <p class="subtitle">Secure access to your dashboard</p>

            <form method="POST" action="login.php">
                <?= $form_html ?>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // OTP input handling - auto-tab between fields
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-input');
            const hiddenInput = document.getElementById('otp_code_hidden');
            
            inputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    if (e.target.value.length >= 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    // Update hidden input with combined value
                    const values = Array.from(inputs).map(i => i.value).join('');
                    hiddenInput.value = values;
                });
                
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });
            
            // Focus first input on load
            if (inputs.length > 0) {
                inputs[0].focus();
            }
        });

        // Timer for resend cooldown
        const timerElement = document.getElementById('timer');
        if (timerElement) {
            let timeLeft = <?= isset($_SESSION['pending_2fa']) ? json_encode(canResendOtp((int)$_SESSION['pending_2fa'], 30)['secondsRemaining']) : 0 ?>;
            
            function updateTimer() {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerElement.textContent = `Resend available in: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    timerElement.textContent = 'You can now resend the OTP';
                    clearInterval(timerInterval);
                }
                timeLeft--;
            }
            
            updateTimer();
            const timerInterval = setInterval(updateTimer, 1000);
        }
    </script>
</body>
</html>