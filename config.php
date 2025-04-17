<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP MySQL has no password
define('DB_NAME', 'university_notice_board');

// Email configuration for password reset
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');  // Replace with your Gmail
define('SMTP_PASS', 'your-app-password');     // Replace with your Gmail app password

// Base URL of your application
define('BASE_URL', 'http://localhost/university_notice_board');

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);  // Set to 1 if using HTTPS
session_start();
?>
