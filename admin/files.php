<?php
require_once 'includes/header.php';
require_once '../includes/file_handler.php';

if (!isAdmin()) {
    redirectTo('/admin/index.php');
}

$fileHandler = new FileHandler();
$error = '';
$success = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $result = $fileHandler->uploadFile($_FILES['file']);
    
    if ($result['success']) {
        $success = "File uploaded successfully!";
    } else {
        $error = implode('<br>', $result['errors']);
    }
}

// Handle file deletion
if (isset($_GET['delete']) && isset($_GET['filename'])) {
    $filename = sanitizeInput($_GET['filename']);
    
    if ($fileHandler->deleteFile($filename)) {
        $success = "File deleted successfully!";
    } else {
        $error = "Failed to delete file.";
    }
}

// Get list of files in upload directory
$files = [];
if ($handle = opendir(UPLOAD_DIR)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $fileInfo = $fileHandler->getFileInfo($entry);
            if ($fileInfo) {
                $files[] = $fileInfo;
            }
        }
    }
    closedir($handle);
}

// Sort files by modified date (newest first)
usort($files, function($a, $b) {
    return $b['modified'] - $a['modified'];
});
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">File Management</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-upload"></i> Upload File
        </button>
    </div>

    <?php if ($error): ?>
        <?php echo displayError($error); ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?php echo displaySuccess($success); ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <?php if (empty($files)): ?>
                <p class="text-center py-4">No files uploaded yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Modified</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                                <tr>
                                    <td>
                                        <i class="fas <?php echo $fileHandler->getFileIcon($file['type']); ?> me-2"></i>
                                        <?php echo htmlspecialchars($file['filename']); ?>
                                    </td>
                                    <td><?php echo $file['type']; ?></td>
                                    <td><?php echo $fileHandler->formatFileSize($file['size']); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', $file['modified']); ?></td>
                                    <td>
                                        <a href="../uploads/<?php echo urlencode($file['filename']); ?>" 
                                           class="btn btn-info btn-sm" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="files.php?delete=1&filename=<?php echo urlencode($file['filename']); ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this file?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Select File</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="form-text">
                            Maximum file size: 5MB<br>
                            Allowed types: PDF, JPEG, PNG, DOC, DOCX, XLS, XLSX
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="preview-content" class="text-center">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File size validation
    document.querySelector('#file').addEventListener('change', function() {
        if (this.files[0].size > 5 * 1024 * 1024) {
            alert('File is too large. Maximum size is 5MB.');
            this.value = '';
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
