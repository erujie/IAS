<?php
ob_start(); // catch any accidental output that would break image headers
session_start();

// Generate code
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$code  = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $chars[random_int(0, strlen($chars) - 1)];
}
$_SESSION['captcha_code'] = $code;

// ── Check if GD is available ─────────────────────────────────────────────────
if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
    // Fallback: render captcha as plain HTML text (no image)
    ob_end_clean();
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><body style="margin:0;background:#f5f5f5;display:flex;align-items:center;justify-content:center;height:48px;">
          <span style="font-family:monospace;font-size:22px;font-weight:bold;letter-spacing:8px;color:#111;">'
          . $code .
          '</span></body></html>';
    exit();
}

// ── GD image ─────────────────────────────────────────────────────────────────
$width  = 150;
$height = 48;

$img     = imagecreatetruecolor($width, $height);
$bg      = imagecolorallocate($img, 245, 245, 245);
$noise   = imagecolorallocate($img, 190, 190, 190);
$lineCol = imagecolorallocate($img, 200, 200, 200);
$textCol = imagecolorallocate($img,  20,  20,  20);
$shadow  = imagecolorallocate($img, 160, 160, 160);

imagefilledrectangle($img, 0, 0, $width, $height, $bg);

// Noise dots
for ($i = 0; $i < 80; $i++) {
    imagesetpixel($img, random_int(0, $width - 1), random_int(0, $height - 1), $noise);
}

// Scratch lines
for ($i = 0; $i < 3; $i++) {
    imageline($img,
        random_int(0, $width), random_int(0, $height),
        random_int(0, $width), random_int(0, $height),
        $lineCol
    );
}

// Characters
$x = 12;
for ($i = 0; $i < strlen($code); $i++) {
    $offsetY = random_int(-3, 3);
    imagechar($img, 5, $x + 1, 16 + $offsetY + 1, $code[$i], $shadow);
    imagechar($img, 5, $x,     16 + $offsetY,      $code[$i], $textCol);
    $x += 26;
}

ob_end_clean(); // discard any buffered output before sending image

header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

imagepng($img);
imagedestroy($img);
?>