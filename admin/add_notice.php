<?php
ob_start(); // Start output buffering
require_once 'includes/header.php';
require_once '../includes/file_handler.php';

if (!isAdmin()) {
    redirectTo('../auth/login.php');
}

$error = '';
$success = '';
$fileHandler = new FileHandler();
$shouldRedirect = false; // Flag to track if we should redirect

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    
    if (empty($title) || empty($description) || empty($category)) {
        $error = "All fields are required.";
    } else {
        $filePath = null;
        
        // Handle file upload
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $fileHandler->uploadFile($_FILES['attachment']);
            
            if (isset($uploadResult['error'])) {
                $error = $uploadResult['error'];
            } else {
                $filePath = $uploadResult['path'];
            }
        }
        
        if (empty($error)) {
            try {
                $db->query(
                    "INSERT INTO notices (title, description, category, posted_by, file_path) VALUES (?, ?, ?, ?, ?)",
                    [$title, $description, $category, $_SESSION['user_id'], $filePath]
                );
                $success = "Notice added successfully!";
                $shouldRedirect = true; // Set the flag instead of immediate redirect
            } catch (Exception $e) {
                $error = "Failed to add notice. Please try again.";
                // Delete uploaded file if notice creation fails
                if ($filePath) {
                    $fileHandler->deleteFile($filePath);
                }
            }
        }
    }
}

// If we need to redirect, do it before any output
if ($shouldRedirect) {
    header("refresh:2;url=notices.php");
    exit();
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">Add New Notice</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <?php echo displayError($error); ?>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <?php echo displaySuccess($success); ?>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                            <?php echo (isset($_POST['category']) && $_POST['category'] === $cat) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="Academic" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Academic') ? 'selected' : ''; ?>>Academic</option>
                                <option value="Administrative" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Administrative') ? 'selected' : ''; ?>>Administrative</option>
                                <option value="Events" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Events') ? 'selected' : ''; ?>>Events</option>
                                <option value="Examination" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Examination') ? 'selected' : ''; ?>>Examination</option>
                                <option value="General" <?php echo (isset($_POST['category']) && $_POST['category'] === 'General') ? 'selected' : ''; ?>>General</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="6" required><?php 
                                echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; 
                            ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="attachment" class="form-label">Attachment</label>
                            <input type="file" class="form-control" id="attachment" name="attachment">
                            <div class="form-text">
                                Maximum file size: 5MB<br>
                                Allowed types: PDF, JPEG, PNG, DOC, DOCX, XLS, XLSX
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Notice</button>
                            <a href="notices.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'includes/footer.php';
ob_end_flush(); // End output buffering and send output
?>
