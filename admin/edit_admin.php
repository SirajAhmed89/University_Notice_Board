<?php
require_once 'includes/header.php';

// Check if user is super admin
if (!isSuperAdmin()) {
    redirectTo('/admin/index.php');
}

if (!isset($_GET['id'])) {
    redirectTo('/admin/admins.php');
}

$adminId = (int)$_GET['id'];

// Prevent editing self through this page
if ($adminId === $_SESSION['user_id']) {
    redirectTo('/admin/admins.php');
}

// Get admin details
$stmt = $db->query("SELECT * FROM users WHERE id = ?", [$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    redirectTo('/admin/admins.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    if (empty($username) || empty($email) || empty($role)) {
        $error = "Username, email, and role are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!in_array($role, ['admin', 'super_admin'])) {
        $error = "Invalid role selected.";
    } else {
        // Check if username or email already exists for other users
        $stmt = $db->query(
            "SELECT COUNT(*) as count FROM users WHERE (username = ? OR email = ?) AND id != ?",
            [$username, $email, $adminId]
        );
        $exists = $stmt->fetch()['count'] > 0;
        
        if ($exists) {
            $error = "Username or email already exists.";
        } else {
            try {
                // Start with basic update query
                $query = "UPDATE users SET username = ?, email = ?, role = ?";
                $params = [$username, $email, $role];
                
                // If new password is provided, update it
                if (!empty($newPassword)) {
                    if (strlen($newPassword) < 8) {
                        $error = "Password must be at least 8 characters long.";
                    } elseif ($newPassword !== $confirmPassword) {
                        $error = "Passwords do not match.";
                    } else {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $query .= ", password = ?";
                        $params[] = $hashedPassword;
                    }
                }
                
                if (empty($error)) {
                    $query .= " WHERE id = ?";
                    $params[] = $adminId;
                    
                    $db->query($query, $params);
                    $success = "Administrator updated successfully!";
                    
                    // Refresh admin data
                    $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$adminId]);
                    $admin = $stmt->fetch();
                    
                    // Redirect after a brief delay
                    header("refresh:2;url=admins.php");
                }
            } catch (Exception $e) {
                $error = "Failed to update administrator. Please try again.";
            }
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title mb-0">Edit Administrator</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <?php echo displayError($error); ?>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <?php echo displaySuccess($success); ?>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required
                                   value="<?php echo htmlspecialchars($admin['username']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($admin['email']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="admin" <?php echo $admin['role'] === 'admin' ? 'selected' : ''; ?>>
                                    Admin
                                </option>
                                <option value="super_admin" <?php echo $admin['role'] === 'super_admin' ? 'selected' : ''; ?>>
                                    Super Admin
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password"
                                   minlength="8">
                            <div class="form-text">Leave blank to keep current password.</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Administrator</button>
                            <a href="admins.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
