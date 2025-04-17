<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';

echo "University Notice Board Feature Verification\n";
echo "=========================================\n\n";

function checkFeature($feature, $condition) {
    echo str_pad($feature . ": ", 50, ".");
    echo $condition ? "✅ PASS" : "❌ FAIL";
    echo "\n";
}

// Database Connection
try {
    $db->getConnection()->query("SELECT 1");
    checkFeature("Database Connection", true);
} catch (Exception $e) {
    checkFeature("Database Connection", false);
}

// Check Tables
$tables = [
    'users' => [
        'id', 'username', 'email', 'password', 'role', 
        'reset_token', 'reset_expiry', 'created_at', 'updated_at'
    ],
    'notices' => [
        'id', 'title', 'description', 'category', 'posted_by',
        'file_path', 'created_at', 'updated_at'
    ]
];

foreach ($tables as $table => $columns) {
    $stmt = $db->query("SHOW COLUMNS FROM $table");
    $dbColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    checkFeature("Table: $table", count(array_intersect($columns, $dbColumns)) === count($columns));
}

// Check Default Admin
$stmt = $db->query("SELECT * FROM users WHERE role = 'super_admin' LIMIT 1");
checkFeature("Default Super Admin Account", $stmt->rowCount() > 0);

// Check File Permissions
$uploadDir = __DIR__ . '/uploads';
checkFeature("Uploads Directory Exists", is_dir($uploadDir));
checkFeature("Uploads Directory Writable", is_writable($uploadDir));

// Check Required PHP Extensions
checkFeature("PDO Extension", extension_loaded('pdo'));
checkFeature("PDO MySQL Extension", extension_loaded('pdo_mysql'));
checkFeature("FileInfo Extension", extension_loaded('fileinfo'));
checkFeature("OpenSSL Extension", extension_loaded('openssl'));

// Check Required Files
$requiredFiles = [
    'config.php',
    'db.php',
    'includes/functions.php',
    'admin/index.php',
    'admin/notices.php',
    'admin/admins.php',
    'admin/add_notice.php',
    'admin/edit_notice.php',
    'admin/delete_notice.php',
    'auth/login.php',
    'auth/logout.php',
    'auth/reset_password.php'
];

foreach ($requiredFiles as $file) {
    checkFeature("File: $file", file_exists(__DIR__ . '/' . $file));
}

// Check Email Configuration
$emailConfigured = defined('SMTP_HOST') && defined('SMTP_PORT') && 
                  defined('SMTP_USER') && defined('SMTP_PASS');
checkFeature("Email Configuration", $emailConfigured);

echo "\nSecurity Checks\n";
echo "===============\n\n";

// Check Password Hashing
$stmt = $db->query("SELECT password FROM users LIMIT 1");
$password = $stmt->fetch()['password'];
checkFeature("Password Hashing", strlen($password) > 50);

// Check Session Configuration
$sessionConfig = session_get_cookie_params();
checkFeature("Session Secure Cookie", $sessionConfig['secure']);
checkFeature("Session HTTPOnly Cookie", $sessionConfig['httponly']);

echo "\nFeature Implementation\n";
echo "=====================\n\n";

// Check Admin Features
$features = [
    'Authentication' => [
        'Login Form' => file_exists(__DIR__ . '/auth/login.php'),
        'Logout' => file_exists(__DIR__ . '/auth/logout.php'),
        'Password Reset' => file_exists(__DIR__ . '/auth/reset_password.php')
    ],
    'Admin Management' => [
        'Admin List' => file_exists(__DIR__ . '/admin/admins.php'),
        'Add Admin' => strpos(file_get_contents(__DIR__ . '/admin/admins.php'), 'add_admin'),
        'Edit Admin' => file_exists(__DIR__ . '/admin/edit_admin.php'),
        'Delete Admin' => file_exists(__DIR__ . '/admin/delete_admin.php')
    ],
    'Notice Management' => [
        'Notice List' => file_exists(__DIR__ . '/admin/notices.php'),
        'Add Notice' => file_exists(__DIR__ . '/admin/add_notice.php'),
        'Edit Notice' => file_exists(__DIR__ . '/admin/edit_notice.php'),
        'Delete Notice' => file_exists(__DIR__ . '/admin/delete_notice.php'),
        'File Upload' => strpos(file_get_contents(__DIR__ . '/admin/add_notice.php'), 'file_path')
    ],
    'Public Interface' => [
        'Notice Board' => file_exists(__DIR__ . '/index.php'),
        'Search Function' => strpos(file_get_contents(__DIR__ . '/index.php'), 'search'),
        'Category Filter' => strpos(file_get_contents(__DIR__ . '/index.php'), 'category')
    ]
];

foreach ($features as $category => $items) {
    echo "\n$category:\n";
    foreach ($items as $feature => $implemented) {
        checkFeature($feature, $implemented);
    }
}

echo "\nVerification Complete!\n";
?>
