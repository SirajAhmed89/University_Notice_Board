<?php
require_once '../config.php';
require_once '../db.php';
require_once '../includes/functions.php';

$message = '';
$error = '';
$validToken = false;

if (!isset($_GET['token'])) {
    redirectTo('/auth/login.php');
}

$token = sanitizeInput($_GET['token']);
$stmt = $db->query(
    "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW()",
    [$token]
);
$user = $stmt->fetch();

if (!$user) {
    $error = "Invalid or expired reset token.";
} else {
    $validToken = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $db->query(
            "UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?",
            [$hashedPassword, $user['id']]
        );
        
        $message = "Password has been reset successfully. You can now login with your new password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - University Notice Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Reset Password</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <?php echo displaySuccess($message); ?>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-primary">Go to Login</a>
                            </div>
                        <?php elseif ($error): ?>
                            <?php echo displayError($error); ?>
                            <?php if (!$validToken): ?>
                                <div class="text-center mt-3">
                                    <a href="forgot_password.php" class="btn btn-primary">Request New Reset Link</a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required 
                                           minlength="8">
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required minlength="8">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
