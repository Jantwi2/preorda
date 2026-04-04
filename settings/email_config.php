<?php
/**
 * Email Configuration
 * Update these settings with your SMTP credentials
 */

// SMTP Settings
define('SMTP_HOST', 'smtp.hostinger.com'); 
define('SMTP_PORT', 465); 
define('SMTP_USERNAME', 'support@yourdomain.com'); // Update with your Hostinger email
define('SMTP_PASSWORD', 'your_email_password'); // Update with your Hostinger email password
define('SMTP_SECURE', 'ssl'); 

// Email Settings
define('FROM_EMAIL', 'jemimaantwi47@gmail.com'); // From email address
define('FROM_NAME', 'PreOrda'); // From name
define('REPLY_TO_EMAIL', 'jemimaantwi47@gmail.com'); // Reply-to email

// OTP Settings
ini_set('display_errors', 0);
define('OTP_LENGTH', 6); // Length of OTP code
define('OTP_EXPIRY_MINUTES', 15); // OTP expiry time in minutes

// Email Templates Directory
define('EMAIL_TEMPLATES_DIR', __DIR__ . '/../templates/emails/');
?>
