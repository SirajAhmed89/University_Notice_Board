<?php
// Session is already started in config.php
// session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !isSessionExpired();
}

function isSessionExpired() {
    if (!isset($_SESSION['last_activity'])) {
        return true;
    }
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_destroy();
        return true;
    }
    $_SESSION['last_activity'] = time();
    return false;
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

function isAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
}

function redirectTo($path) {
    // If path starts with '/', append it to BASE_URL
    // If path starts with 'http', use it as is
    // Otherwise, append it to BASE_URL with '/'
    if (strpos($path, 'http') === 0) {
        $url = $path;
    } else {
        $url = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
    header("Location: " . $url);
    exit();
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function validateFileUpload($file) {
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['size'] > $maxSize) {
        return "File is too large. Maximum size is 5MB.";
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return "Invalid file type. Only PDF, JPEG, and PNG files are allowed.";
    }

    return true;
}

function uploadFile($file) {
    $validation = validateFileUpload($file);
    if ($validation !== true) {
        return ['error' => $validation];
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = UPLOAD_DIR . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => $fileName];
    }

    return ['error' => 'Failed to upload file.'];
}

function formatDate($date) {
    return date('F j, Y, g:i a', strtotime($date));
}

function displayError($message) {
    return "<div class='alert alert-danger' role='alert'>$message</div>";
}

function displaySuccess($message) {
    return "<div class='alert alert-success' role='alert'>$message</div>";
}
?>
