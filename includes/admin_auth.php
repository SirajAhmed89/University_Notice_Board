<?php
require_once 'functions.php';
require_once 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    redirectTo('/auth/login.php');
}

// Function to check if current admin is super admin
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

// Function to check if admin owns a notice
function isNoticeOwner($noticeId) {
    global $db;
    $stmt = $db->query("SELECT posted_by FROM notices WHERE id = ?", [$noticeId]);
    $notice = $stmt->fetch();
    return $notice && $notice['posted_by'] == $_SESSION['admin_id'];
}

// Function to check if admin has permission to manage a notice
function hasNoticePermission($noticeId) {
    return isSuperAdmin() || isNoticeOwner($noticeId);
}
?>
