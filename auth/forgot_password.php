<?php
require_once '../config.php';
require_once '../db.php';
require_once '../includes/functions.php';
require '../vendor/autoload.php'; // Requires PHPMailer installation

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    $stmt = $db->query("SELECT * FROM users WHERE email = ?", [$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = generateToken();
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $db->query(
            "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?",
            [$token, $expiry, $user['id']]
        );
        
        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            
            $mail->setFrom(SMTP_USER, 'University Notice Board');
            $mail->addAddress($email);
            
            $resetLink = BASE_URL . "/auth/reset_password.php?token=" . $token;
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Please click the following link to reset your password: <br><a href='$resetLink'>Reset Password</a><br>
                          This link will expire in 15 minutes.";
            
            $mail->send();
            $message = "Password reset instructions have been sent to your email.";
        } catch (Exception $e) {
            $error = "Failed to send password reset email. Please try again later.";
        }
    } else {
        $error = "If an account exists with this email, you will receive password reset instructions.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - University Notice Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Forgot Password</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <?php echo displaySuccess($message); ?>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <?php echo displayError($error); ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="login.php">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
