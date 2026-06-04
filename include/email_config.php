<?php
// Email Configuration
// Replace these values with your actual email settings

// Gmail SMTP Settings (Recommended)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com'); // Replace with your Gmail
define('SMTP_PASSWORD', 'your-app-password'); // Replace with your Gmail App Password
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Alternative: If using other email providers
// For Outlook/Hotmail:
// define('SMTP_HOST', 'smtp-mail.outlook.com');
// define('SMTP_PORT', 587);

// For Yahoo:
// define('SMTP_HOST', 'smtp.mail.yahoo.com');
// define('SMTP_PORT', 587);

// For custom SMTP:
// define('SMTP_HOST', 'your-smtp-server.com');
// define('SMTP_PORT', 587);

// Sender Information
define('SENDER_EMAIL', 'your-email@gmail.com'); // Replace with your email
define('SENDER_NAME', 'Secondsight Foundation');

// Website Information
define('WEBSITE_URL', 'https://www.secondsightfoundation.com');
define('LOGO_URL', 'https://www.secondsightfoundation.com/assets/img/n-logo.png');
?> 