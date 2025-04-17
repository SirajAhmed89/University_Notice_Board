<?php
require_once 'config.php';
require_once 'db.php';

try {
    // Test database connection
    $db->getConnection()->query("SELECT 1");
    echo "✅ Database connection successful\n";
    
    // Check if admin user exists
    $stmt = $db->query("SELECT * FROM users WHERE email = ?", ['admin@university.com']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Admin user found:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Password Hash: " . $user['password'] . "\n";
        
        // Test password verification
        $testPassword = 'admin123';
        if (password_verify($testPassword, $user['password'])) {
            echo "✅ Password verification successful\n";
        } else {
            echo "❌ Password verification failed\n";
            echo "Let's create a new password hash for testing:\n";
            echo password_hash($testPassword, PASSWORD_DEFAULT) . "\n";
        }
    } else {
        echo "❌ Admin user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
