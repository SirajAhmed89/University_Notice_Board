<?php
require_once '../config.php';
require_once '../db.php';
require_once '../includes/functions.php';

// Debug database connection
try {
    $db->getConnection()->query("SELECT 1");
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
}

if (isLoggedIn()) {
    redirectTo(BASE_URL . '/admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Enhanced debugging
    error_log("Login attempt - Email: " . $email);
    error_log("POST data: " . print_r($_POST, true));
    
    try {
        $stmt = $db->query("SELECT * FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch();

        if ($user) {
            error_log("User found - ID: " . $user['id'] . ", Role: " . $user['role']);
            error_log("Stored password hash: " . $user['password']);
            
            if (password_verify($password, $user['password'])) {
                error_log("Password verified successfully");
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                redirectTo(BASE_URL . '/admin/index.php');
            } else {
                error_log("Password verification failed for input: " . $password);
                $error = 'Invalid email or password';
            }
        } else {
            error_log("No user found with email: " . $email);
            $error = 'Invalid email or password';
        }
    } catch (Exception $e) {
        error_log("Database error during login: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $error = 'A system error occurred. Please try again later.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - University Notice Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            background-image: url('../tasfeer/mlib.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: top;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(45deg, #1a237e, #283593);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }
        .card-body {
            padding: 2rem;
        }
        .btn-primary {
            background: linear-gradient(45deg, #1a237e, #283593);
            border: none;
            padding: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #283593, #1a237e);
            transform: translateY(-1px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }
        .btn-secondary {
            background: linear-gradient(45deg, #546e7a, #37474f);
            border: none;
            padding: 0.8rem;
        }
        .btn-secondary:hover {
            background: linear-gradient(45deg, #37474f, #546e7a);
        }
        .input-group {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            border-radius: 0.375rem;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-right: none;
        }
        .form-control {
            border: 1px solid #ced4da;
            border-left: none;
            padding: 0.8rem;
        }
        .form-control:focus {
            border-color: #ced4da;
            box-shadow: none;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .nav-link {
            color: #1a237e;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: #283593;
            text-decoration: underline;
        }
        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: white;
            -webkit-text-stroke: 1px black;

        }
        .saad1{
            -webkit-text-stroke: 1px black;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <a href="<?php echo BASE_URL; ?>" class="text-decoration-none">
                        <h1 class="logo-text">
                            <i class="fas fa-university me-2"></i>
                            University<br>Notice Board
                        </h1>
                    </a>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center mb-0 saad1">Admin Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <?php echo displayError($error); ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           placeholder="Enter your email"
                                           required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="forgot_password.php" class="nav-link">
                                        <i class="fas fa-question-circle"></i> Forgot Password?
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>" class="nav-link">
                                        <i class="fas fa-home"></i> Back to Home
                                    </a>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    </script>
</body>
</html>
