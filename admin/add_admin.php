<?php
// Start output buffering at the very beginning
ob_start();

require_once 'includes/header.php';

// Check if user is super admin
if (!isSuperAdmin()) {
    redirectTo('/admin/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = sanitizeInput($_POST['role']);
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (!in_array($role, ['admin', 'super_admin'])) {
        $error = "Invalid role selected.";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $db->query(
                "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?",
                [$username, $email]
            );
            $exists = $stmt->fetch()['count'] > 0;
            
            if ($exists) {
                $error = "Username or email already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $db->query(
                    "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)",
                    [$username, $email, $hashedPassword, $role]
                );
                
                $success = "Administrator added successfully!";
                
                // Use JavaScript for redirect instead of PHP header
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'admins.php';
                    }, 2000);
                </script>";
            }
        } catch (Exception $e) {
            $error = "Failed to add administrator. Please try again.";
            // Log the error for debugging
            error_log("Admin creation error: " . $e->getMessage());
        }
    }
}
?>

<style>
    /* Form specific styles */
    .card {
        max-width: 800px;
        margin: 0 auto;
    }

    .card-header {
        background: white;
        border-bottom: 1px solid #edf2f7;
        padding: 1.5rem;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    .form-label {
        font-weight: 500;
        color: #4a5568;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
    }

    .form-select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .form-text {
        color: #718096;
        font-size: 0.875rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #4361ee;
        border-color: #4361ee;
        color: white;
    }

    .btn-primary:hover {
        background: #3f37c9;
        border-color: #3f37c9;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #64748b;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    .alert {
        border: none;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .alert-danger {
        background: #fde8e8;
        color: #9b1c1c;
    }

    .alert-success {
        background: #def7ec;
        color: #046c4e;
    }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title mb-0">Add New Administrator</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            <div class="invalid-feedback">Please enter a username.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" <?php echo isset($_POST['role']) && $_POST['role'] === 'admin' ? 'selected' : ''; ?>>
                                    Admin
                                </option>
                                <option value="super_admin" <?php echo isset($_POST['role']) && $_POST['role'] === 'super_admin' ? 'selected' : ''; ?>>
                                    Super Admin
                                </option>
                            </select>
                            <div class="invalid-feedback">Please select a role.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   minlength="8">
                            <div class="form-text">Password must be at least 8 characters long.</div>
                            <div class="invalid-feedback">Please enter a password (minimum 8 characters).</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required minlength="8">
                            <div class="invalid-feedback">Please confirm your password.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Administrator</button>
                            <a href="admins.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Bootstrap form validation JavaScript -->
<script>
(function () {
    'use strict'
    
    // Fetch all forms that need validation
    var forms = document.querySelectorAll('.needs-validation')
    
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php 
require_once 'includes/footer.php';
// End output buffering and send output
ob_end_flush();
?>