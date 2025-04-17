<?php
require_once 'includes/header.php';
require_once '../includes/file_handler.php';

if (!isAdmin()) {
    redirectTo('auth/login.php');
}

$fileHandler = new FileHandler();

// Get filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';
$hasAttachment = isset($_GET['has_attachment']) ? (int)$_GET['has_attachment'] : -1;
$sortBy = isset($_GET['sort_by']) ? sanitizeInput($_GET['sort_by']) : 'created_at';
$sortOrder = isset($_GET['sort_order']) ? sanitizeInput($_GET['sort_order']) : 'DESC';

// Build query
$query = "SELECT n.*, u.username as posted_by_name 
          FROM notices n 
          LEFT JOIN users u ON n.posted_by = u.id 
          WHERE 1=1";
$params = [];

// Add search conditions
if (!empty($search)) {
    $query .= " AND (n.title LIKE ? OR n.description LIKE ? OR u.username LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($category)) {
    $query .= " AND n.category = ?";
    $params[] = $category;
}

if (!empty($dateFrom)) {
    $query .= " AND DATE(n.created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $query .= " AND DATE(n.created_at) <= ?";
    $params[] = $dateTo;
}

// If not super admin, only show own notices
if (!isSuperAdmin()) {
    $query .= " AND n.posted_by = ?";
    $params[] = $_SESSION['user_id'];
}

if ($hasAttachment !== -1) {
    if ($hasAttachment === 1) {
        $query .= " AND n.file_path IS NOT NULL AND n.file_path != ''";
    } else {
        $query .= " AND (n.file_path IS NULL OR n.file_path = '')";
    }
}

// Add sorting
$allowedSortFields = ['title', 'category', 'created_at', 'posted_by_name'];
$sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at';
$sortOrder = $sortOrder === 'ASC' ? 'ASC' : 'DESC';
$query .= " ORDER BY n.$sortBy $sortOrder";

// Execute query
$stmt = $db->query($query, $params);
$notices = $stmt->fetchAll();

// Get categories for filter
$categories = $db->query("SELECT DISTINCT category FROM notices ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Manage Notices</h1>
        <a href="add_notice.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Notice
        </a>
    </div>

    <!-- Filters Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search and Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search in title or description">
                </div>
                
                <div class="col-md-2">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?php echo $dateFrom; ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?php echo $dateTo; ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="has_attachment" class="form-label">Attachments</label>
                    <select class="form-control" id="has_attachment" name="has_attachment">
                        <option value="-1" <?php echo $hasAttachment === -1 ? 'selected' : ''; ?>>All</option>
                        <option value="1" <?php echo $hasAttachment === 1 ? 'selected' : ''; ?>>With Attachments</option>
                        <option value="0" <?php echo $hasAttachment === 0 ? 'selected' : ''; ?>>Without Attachments</option>
                    </select>
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notices Table -->
    <div class="card shadow">
        <div class="card-body">
            <?php if (empty($notices)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4>No notices found</h4>
                    <p class="text-muted">Try adjusting your search criteria</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Posted By</th>
                                <th>Date</th>
                                <th>Attachment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notices as $notice): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($notice['title']); ?></h6>
                                                <small class="text-muted">
                                                    <?php 
                                                    $desc = htmlspecialchars($notice['description']);
                                                    echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($notice['category']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($notice['posted_by_name']); ?></td>
                                    <td><?php echo formatDate($notice['created_at']); ?></td>
                                    <td>
                                        <?php if ($notice['file_path']): ?>
                                            <?php 
                                            $fileInfo = $fileHandler->getFileInfo($notice['file_path']);
                                            if ($fileInfo):
                                            ?>
                                                <div class="attachment-preview">
                                                    <?php if ($fileInfo['preview']): ?>
                                                        <?php if (strpos($fileInfo['type'], 'image/') === 0): ?>
                                                            <img src="<?php echo BASE_URL; ?>/uploads/<?php echo $notice['file_path']; ?>" 
                                                                 class="img-thumbnail" style="max-width: 100px; max-height: 100px;"
                                                                 alt="File preview">
                                                        <?php else: ?>
                                                            <i class="fas <?php echo $fileInfo['icon']; ?> fa-2x"></i>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <i class="fas <?php echo $fileInfo['icon']; ?> fa-2x"></i>
                                                    <?php endif; ?>
                                                    <a href="<?php echo BASE_URL; ?>/uploads/<?php echo urlencode($notice['file_path']); ?>" 
                                                       class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                                                        <i class="fas fa-download"></i> Download
                                                        <small>(<?php echo $fileHandler->formatFileSize($fileInfo['size']); ?>)</small>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view_notice.php?id=<?php echo $notice['id']; ?>" 
                                               class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($notice['posted_by'] == $_SESSION['user_id'] || isSuperAdmin()): ?>
                                                <a href="edit_notice.php?id=<?php echo $notice['id']; ?>" 
                                                   class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_notice.php?id=<?php echo $notice['id']; ?>" 
                                                   class="btn btn-danger btn-sm" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this notice?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Date range validation
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    dateFrom.addEventListener('change', function() {
        dateTo.min = this.value;
    });
    
    dateTo.addEventListener('change', function() {
        dateFrom.max = this.value;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
