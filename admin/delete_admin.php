<?php
require_once '../config.php';
require_once '../db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirectTo('/auth/login.php');
}

if (!isSuperAdmin()) {
    redirectTo('/admin/index.php');
}

if (!isset($_GET['id'])) {
    redirectTo('/admin/admins.php');
}

$adminId = (int)$_GET['id'];

// Prevent deleting self
if ($adminId === $_SESSION['user_id']) {
    $_SESSION['error_message'] = "You cannot delete your own account.";
    redirectTo('/admin/admins.php');
}

// Get admin details
$stmt = $db->query("SELECT * FROM users WHERE id = ?", [$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    $_SESSION['error_message'] = "Administrator not found.";
    redirectTo('/admin/admins.php');
}

try {
    // Begin transaction
    $db->getConnection()->beginTransaction();

    // Delete all notices posted by this admin
    $db->query("DELETE FROM notices WHERE posted_by = ?", [$adminId]);
    
    // Delete the admin
    $db->query("DELETE FROM users WHERE id = ?", [$adminId]);
    
    // Commit transaction
    $db->getConnection()->commit();
    
    $_SESSION['success_message'] = "Administrator deleted successfully!";
} catch (Exception $e) {
    // Rollback transaction on error
    $db->getConnection()->rollBack();
    $_SESSION['error_message'] = "Failed to delete administrator. Please try again.";
}

redirectTo('/admin/admins.php');
?>
