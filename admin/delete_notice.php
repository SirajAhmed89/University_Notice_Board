<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../includes/functions.php';
require_once '../includes/file_handler.php';

// Check if user is logged in and is an admin
if (!isAdmin()) {
    redirectTo('../auth/login.php');
}

// Get notice ID
$noticeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$noticeId) {
    $_SESSION['error'] = "Invalid notice ID.";
    redirectTo('/admin/notices.php');
}

try {
    // Get notice details first (for file deletion)
    $stmt = $db->query("SELECT * FROM notices WHERE id = ?", [$noticeId]);
    $notice = $stmt->fetch();

    if (!$notice) {
        $_SESSION['error'] = "Notice not found.";
        redirectTo('/admin/notices.php');
    }

    // Check if the current admin is the owner of the notice or is a super admin
    if ($notice['posted_by'] != $_SESSION['user_id'] && !isSuperAdmin()) {
        $_SESSION['error'] = "You don't have permission to delete this notice.";
        redirectTo('/admin/notices.php');
    }

    // Delete the attachment if it exists
    if (!empty($notice['file_path'])) {
        $file_path = '../uploads/' . $notice['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Delete the notice
    $stmt = $db->query("DELETE FROM notices WHERE id = ?", [$noticeId]);

    if ($stmt) {
        $_SESSION['success'] = "Notice deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete notice.";
    }

} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred while deleting the notice.";
    // Log the error for debugging
    error_log("Error deleting notice: " . $e->getMessage());
}

redirectTo('/admin/notices.php');
?>
