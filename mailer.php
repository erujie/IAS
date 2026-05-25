<?php
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USERNAME', 'ldcs.shop@gmail.com');
define('MAIL_PASSWORD', 'rwdtteuebrulrhuu');
define('MAIL_FROM',     'ldcs.shop@gmail.com');
define('MAIL_FROM_NAME','IAS Security');

/**
 * Send OTP code via email
 * 
 * @param string $toEmail Recipient email address
 * @param string $toName Recipient name
 * @param string $otpCode The OTP code to send
 * @return boolean True if email sent successfully
 */
function sendOTP($toEmail, $toName, $otpCode) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Host       = MAIL_HOST;
        $mail->Port       = MAIL_PORT;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = 'Your OTP Code — IAS';
        $mail->Body    = "Your one-time code is: $otpCode\n\nThis code expires in 5 minutes. Do not share it.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}