<?php
session_start();

// Include PHPMailer classes
// require 'phpmailer/src/Exception.php';
// require 'phpmailer/src/PHPMailer.php';
// require 'phpmailer/src/SMTP.php';

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// Get the email from the request
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? null;

if ($email) {
    // Generate a random OTP
    $otp = rand(100000, 999999);

    // Store the OTP in the session
    $_SESSION['otp'] = $otp;

    // Email content
    $to = $email;
    $subject = "Your One-Time Password (OTP) for Secondsightfoundation.com";
    $brand_logo_url = 'https://secondsightfoundation.com/assets/img/flogo.webp'; // Use absolute URL for email images
    $message = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Your OTP Code</title>
    <style>
        body { background: #f6f8fa; margin: 0; padding: 0; font-family: 'Segoe UI', 'Inter', Arial, sans-serif; }
        .container { max-width: 480px; margin: 32px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 32px 24px; }
        .logo { display: block; margin: 0 auto 16px auto; max-width: 120px; }
        .title { color: #1a237e; font-size: 1.3rem; font-weight: 600; text-align: center; margin-bottom: 16px; }
        .otp { font-size: 2rem; font-weight: bold; color: #1976d2; letter-spacing: 4px; text-align: center; margin: 24px 0; }
        .text { color: #333; font-size: 1rem; line-height: 1.6; margin-bottom: 16px; text-align: center; }
        .footer { color: #888; font-size: 0.9rem; text-align: center; margin-top: 32px; }
        @media (max-width: 600px) {
            .container { padding: 16px 4vw; }
            .otp { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class=\"container\">
        <img src=\"$brand_logo_url\" alt=\"Secondsightfoundation Logo\" class=\"logo\" width=\"120\" height=\"120\" style=\"border-radius: 50%; background: #e3f2fd;\">
        <div class=\"title\">Your One-Time Password (OTP)</div>
        <div class=\"text\">Dear User,<br><br>To proceed with your request on <b>Secondsightfoundation.com</b>, please use the following OTP. This code is valid for a limited time and should not be shared with anyone.</div>
        <div class=\"otp\">$otp</div>
        <div class=\"text\">If you did not request this code, you can safely ignore this email.<br><br>Thank you,<br>The Secondsightfoundation Team</div>
        <div class=\"footer\">&copy; ".date('Y')." Secondsightfoundation.com</div>
    </div>
</body>
</html>";

    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: Secondsightfoundation.com <no-reply@secondsightfoundation.com>\r\n";
    $headers .= "Reply-To: support@secondsightfoundation.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    // Optionally, add List-Unsubscribe header for bulk mail compliance
    // $headers .= "List-Unsubscribe: <mailto:support@secondsightfoundation.com>\r\n";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
}

?>