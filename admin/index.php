<?php
require_once 'includes/header.php';

// Get total number of notices
$stmt = $db->query("SELECT COUNT(*) as total FROM notices");
$totalNotices = $stmt->fetch()['total'] ?? 0;

// Get total number of admins
$stmt = $db->query("SELECT COUNT(*) as total FROM users");
$totalAdmins = $stmt->fetch()['total'] ?? 0;

// Get recent notices
$stmt = $db->query(
    "SELECT n.*, u.username FROM notices n 
    JOIN users u ON n.posted_by = u.id 
    ORDER BY created_at DESC LIMIT 5"
);
$recentNotices = $stmt->fetchAll();

?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Dashboard</h1>
    
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Notices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalNotices; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isSuperAdmin()): ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalAdmins; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Notices</h6>
                    <a href="notices.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentNotices)): ?>
                        <p class="text-center">No notices found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Posted By</th>
                                        <th>Date Posted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentNotices as $notice): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($notice['title'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($notice['category'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($notice['username'] ?? ''); ?></td>
                                            <td><?php echo formatDate($notice['created_at'] ?? ''); ?></td>
                                            <td>
                                                <a href="view_notice.php?id=<?php echo $notice['id']; ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (isSuperAdmin() || ($notice['posted_by'] ?? 0) == ($_SESSION['admin_id'] ?? 0)): ?>
                                                    <a href="edit_notice.php?id=<?php echo $notice['id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete_notice.php?id=<?php echo $notice['id']; ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       onclick="return confirm('Are you sure you want to delete this notice?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
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
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
