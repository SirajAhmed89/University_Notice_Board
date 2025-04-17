<?php
require_once 'includes/header.php';
require_once '../includes/file_handler.php';

if (!isAdmin()) {
    redirectTo('../auth/login.php');
}

$error = '';
$success = '';
$fileHandler = new FileHandler();

// Get notice ID
$noticeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get notice details
$stmt = $db->query("SELECT * FROM notices WHERE id = ?", [$noticeId]);
$notice = $stmt->fetch();

// Check if notice exists and user has permission to edit
if (!$notice) {
    redirectTo('notices.php');
}

// Only allow editing if user is the notice owner
if ($notice['posted_by'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "You don't have permission to edit this notice.";
    redirectTo('notices.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    
    if (empty($title) || empty($description) || empty($category)) {
        $error = "All fields are required.";
    } else {
        $filePath = $notice['file_path'];
        
        // Handle file upload if new file is selected
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $fileHandler->uploadFile($_FILES['attachment']);
            
            if (isset($uploadResult['error'])) {
                $error = $uploadResult['error'];
            } else {
                // Delete old file if it exists
                if ($filePath) {
                    $fileHandler->deleteFile($filePath);
                }
                $filePath = $uploadResult['path'];
            }
        }
        
        if (empty($error)) {
            try {
                $db->query(
                    "UPDATE notices SET title = ?, description = ?, category = ?, file_path = ? WHERE id = ?",
                    [$title, $description, $category, $filePath, $noticeId]
                );
                $success = "Notice updated successfully!";
                
                // Redirect after a brief delay
                header("refresh:2;url=notices.php");
            } catch (Exception $e) {
                $error = "Failed to update notice. Please try again.";
            }
        }
    }
}

// Get existing categories
$categories = $db->query("SELECT DISTINCT category FROM notices ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">Edit Notice</h3>
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
                                   value="<?php echo htmlspecialchars($notice['title']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                            <?php echo $notice['category'] === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="Academic" <?php echo $notice['category'] === 'Academic' ? 'selected' : ''; ?>>Academic</option>
                                <option value="Administrative" <?php echo $notice['category'] === 'Administrative' ? 'selected' : ''; ?>>Administrative</option>
                                <option value="Events" <?php echo $notice['category'] === 'Events' ? 'selected' : ''; ?>>Events</option>
                                <option value="Examination" <?php echo $notice['category'] === 'Examination' ? 'selected' : ''; ?>>Examination</option>
                                <option value="General" <?php echo $notice['category'] === 'General' ? 'selected' : ''; ?>>General</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="6" required><?php 
                                echo htmlspecialchars($notice['description']); 
                            ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="attachment" class="form-label">Attachment</label>
                            <?php if ($notice['file_path']): ?>
                                <?php 
                                $fileInfo = $fileHandler->getFileInfo($notice['file_path']);
                                if ($fileInfo):
                                ?>
                                    <div class="mb-2">
                                        <strong>Current file:</strong>
                                        <a href="<?php echo BASE_URL; ?>/uploads/<?php echo urlencode($notice['file_path']); ?>" 
                                           target="_blank">
                                            <i class="fas <?php echo $fileInfo['icon']; ?>"></i>
                                            <?php echo htmlspecialchars($fileInfo['name']); ?>
                                            (<?php echo $fileHandler->formatFileSize($fileInfo['size']); ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="attachment" name="attachment">
                            <div class="form-text">
                                Maximum file size: 5MB<br>
                                Allowed types: PDF, JPEG, PNG, DOC, DOCX, XLS, XLSX<br>
                                Leave empty to keep the current file
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Notice</button>
                            <a href="notices.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
