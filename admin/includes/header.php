<?php
require_once '../config.php';
require_once '../db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirectTo('/auth/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - University Notice Board</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin-components.css">
    <style>
        .admin-wrapper {
            display: flex;
            height: 100vh;
        }
        .admin-sidebar {
            background-color: #f8f9fa;
            padding: 20px;
            width: 250px;
            min-height: 100vh;
        }
        .sidebar-brand {
            margin-bottom: 20px;
        }
        .sidebar-brand h2 {
            font-size: 18px;
            margin: 0;
        }
        .admin-user {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .admin-user-avatar {
            font-size: 36px;
            margin-right: 10px;
        }
        .admin-user-info {
            display: flex;
            flex-direction: column;
        }
        .admin-name {
            font-size: 16px;
            font-weight: bold;
        }
        .admin-role {
            font-size: 14px;
            color: #666;
        }
        .menu-items {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .menu-items li {
            margin-bottom: 10px;
        }
        .menu-items a {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .menu-items a:hover {
            background-color: #e9ecef;
        }
        .menu-items a.active {
            background-color: #0d6efd;
            color: white;
        }
        .menu-divider {
            height: 1px;
            background-color: #ccc;
            margin: 10px 0;
        }
        .logout-link {
            color: #dc3545;
        }
        .admin-main {
            flex: 1;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .menu-toggle {
            font-size: 18px;
            margin-right: 10px;
        }
        .page-title {
            font-size: 18px;
            margin: 0;
        }
        .admin-quick-nav {
            display: flex;
            align-items: center;
        }
        .admin-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <h2><i class="fas fa-university"></i>Quest Nawabshah</h2>
            </div>
            
            <nav class="sidebar-menu">
                <div class="admin-user">
                    <div class="admin-user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="admin-user-info">
                        <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                        <span class="admin-role"><?php echo ucfirst($_SESSION['role'] ?? 'Administrator'); ?></span>
                    </div>
                </div>

                <ul class="menu-items">
                    <li>
                        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="notices.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'notices.php' ? 'active' : ''; ?>">
                            <i class="fas fa-bullhorn"></i>
                            <span>Manage Notices</span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                    <li>
                        <a href="admins.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'admins.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users-cog"></i>
                            <span>View All Admins</span>
                        </a>
                    </li>
                    <li>
                        <a href="add_admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'add_admin.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-plus"></i>
                            <span>Add New Admin</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="menu-divider"></li>
                    <li>
                        <a href="../auth/logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="btn btn-light menu-toggle d-lg-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">
                        <?php
                        $current_page = basename($_SERVER['PHP_SELF'], '.php');
                        echo ucwords(str_replace('_', ' ', $current_page));
                        ?>
                    </h1>
                </div>
                <div class="header-right">
                    <?php if (in_array(basename($_SERVER['PHP_SELF']), ['notices.php', 'admins.php'])): ?>
                    <div class="admin-quick-nav">
                        <a href="<?php echo basename($_SERVER['PHP_SELF']) === 'notices.php' ? 'add_notice.php' : 'add_admin.php'; ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 
                            <?php echo basename($_SERVER['PHP_SELF']) === 'notices.php' ? 'New Notice' : 'New Admin'; ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </header>

            <div class="admin-content">
