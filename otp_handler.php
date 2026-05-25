<?php
require_once __DIR__ . '/connection.php';

/**
 * Generate a 6-digit OTP code
 * 
 * @return string 6-digit numeric code
 */
function generateOTP() {
    // Generate 6-digit code (000000-999999)
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Store OTP in database and send via email
 * 
 * @param int $userId User ID
 * @param string $email User email
 * @param string $purpose Purpose of OTP (login, reset, etc.)
 * @return array Result with success flag and OTP code or error
 */
function sendOtpEmail($userId, $email, $purpose = 'login_2fa') {
    global $conn;
    
    // Generate OTP
    $otpCode = generateOTP();
    
    // Calculate expiry (5 minutes from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    // Insert OTP record
    $stmt = $conn->prepare("INSERT INTO otps (user_id, code, expires_at, purpose) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $otpCode, $expiresAt, $purpose);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'error' => 'Database error'];
    }
    
    $otpId = $stmt->insert_id;
    $stmt->close();
    
    // Send email
    $emailSent = sendOTP($email, '', $otpCode);
    
    if (!$emailSent) {
        // Mark as used/failed if email failed to send
        $stmt = $conn->prepare("UPDATE otps SET used_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $otpId);
        $stmt->execute();
        $stmt->close();
        
        return ['success' => false, 'error' => 'Failed to send email'];
    }
    
    return ['success' => true, 'code' => $otpCode];
}

/**
 * Verify OTP code
 * 
 * @param int $userId User ID
 * @param string $code OTP code to verify
 * @param string $purpose Purpose of OTP (login, reset, etc.)
 * @return boolean True if OTP is valid and not expired/used
 */
function verifyOtp($userId, $code, $purpose = 'login_2fa') {
    global $conn;
    
    // Find valid, unused OTP for this user and purpose
    $stmt = $conn->prepare("SELECT id, code, expires_at FROM otps 
                           WHERE user_id = ? AND code = ? AND purpose = ? 
                             AND used_at IS NULL 
                             AND expires_at > NOW()");
    $stmt->bind_param("iss", $userId, $code, $purpose);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $otp = $result->fetch_assoc();
        
        // Mark as used
        $updateStmt = $conn->prepare("UPDATE otps SET used_at = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $otp['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        $stmt->close();
        return true;
    }
    
    $stmt->close();
    return false;
}

/**
 * Check if user can resend OTP (cooldown check)
 * 
 * @param int $userId User ID
 * @param int $cooldownSeconds Cooldown period in seconds (default: 30)
 * @return array Result with canSend flag and seconds remaining
 */
function canResendOtp($userId, $cooldownSeconds = 30) {
    global $conn;
    
    // Check most recent OTP for this user
    $stmt = $conn->prepare("SELECT created_at FROM otps 
                           WHERE user_id = ? 
                           ORDER BY created_at DESC 
                           LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['canSend' => true, 'secondsRemaining' => 0];
    }
    
    $row = $result->fetch_assoc();
    $lastSent = new DateTime($row['created_at']);
    $now = new DateTime();
    $interval = $lastSent->diff($now);
    $secondsElapsed = ($interval->days * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    
    $secondsRemaining = max(0, $cooldownSeconds - $secondsElapsed);
    
    $stmt->close();
    
    return [
        'canSend' => $secondsRemaining <= 0,
        'secondsRemaining' => (int)$secondsRemaining
    ];
}