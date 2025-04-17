<?php
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    redirectTo('/admin/notices.php');
}

$noticeId = (int)$_GET['id'];

// Get notice details with poster information
$stmt = $db->query(
    "SELECT n.*, u.username FROM notices n 
    JOIN users u ON n.posted_by = u.id 
    WHERE n.id = ?",
    [$noticeId]
);
$notice = $stmt->fetch();

if (!$notice) {
    redirectTo('/admin/notices.php');
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">View Notice</h3>
                    <div>
                        <?php if (isSuperAdmin() || $notice['posted_by'] === $_SESSION['user_id']): ?>
                            <a href="edit_notice.php?id=<?php echo $notice['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_notice.php?id=<?php echo $notice['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this notice?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        <?php endif; ?>
                        <a href="notices.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h4 class="text-primary"><?php echo htmlspecialchars($notice['title']); ?></h4>
                        <div class="text-muted mb-3">
                            <small>
                                <i class="fas fa-user"></i> Posted by: <?php echo htmlspecialchars($notice['username']); ?>
                                <span class="mx-2">|</span>
                                <i class="fas fa-calendar"></i> Date: <?php echo formatDate($notice['created_at']); ?>
                                <span class="mx-2">|</span>
                                <i class="fas fa-tag"></i> Category: <?php echo htmlspecialchars($notice['category']); ?>
                            </small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Description:</h5>
                        <div class="border rounded p-3 bg-light">
                            <?php echo nl2br(htmlspecialchars($notice['description'])); ?>
                        </div>
                    </div>

                    <?php if ($notice['file_path']): ?>
                        <div class="mb-4">
                            <h5>Attachment:</h5>
                            <div class="border rounded p-3 bg-light">
                                <a href="../uploads/<?php echo $notice['file_path']; ?>" 
                                   class="btn btn-outline-primary" target="_blank">
                                    <i class="fas fa-file"></i> View Attachment
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
