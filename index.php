<?php
require_once 'includes/public_header.php';
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';
require_once 'includes/file_handler.php';

$fileHandler = new FileHandler();

// Get search parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$time = $_GET['time'] ?? '';

// Get all categories for filter dropdown
$stmt = $db->query("SELECT DISTINCT category FROM notices ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build query
$sql = "SELECT n.*, u.username as posted_by_name 
        FROM notices n 
        LEFT JOIN users u ON n.posted_by = u.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (n.title LIKE ? OR n.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND n.category = ?";
    $params[] = $category;
}

if ($time) {
    switch($time) {
        case 'today':
            $sql .= " AND DATE(n.created_at) = CURDATE()";
            break;
        case 'week':
            $sql .= " AND n.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $sql .= " AND n.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
    }
}

$sql .= " ORDER BY n.created_at DESC";

// Execute query
$stmt = $db->query($sql, $params);
$notices = $stmt->fetchAll();
?>

<!-- Add this CSS section in the header or before the hero section -->
<style>
    .notice-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2.5rem;
        padding: 2rem 0;
    }

    .notice-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        border: 1px solid #edf2f7;
    }

    .notice-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
    }

    .notice-image {
        height: 200px;
        overflow: hidden;
        position: relative;
        background: #f8f9fa;
    }

    .notice-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .notice-image .file-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3rem;
        color: #6c757d;
    }

    .notice-content {
        padding: 1.75rem;
    }

    .notice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .notice-category {
        padding: 0.5rem 1.2rem;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .notice-category.academic {
        background: #ebf8ff;
        color: #2b6cb0;
    }

    .notice-category.event {
        background: #faf5ff;
        color: #6b46c1;
    }

    .notice-category.general {
        background: #f0fff4;
        color: #2f855a;
    }

    .notice-category.important {
        background: #fff5f5;
        color: #c53030;
    }

    .notice-date {
        color: #718096;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .notice-date i {
        margin-right: 0.5rem;
    }

    .notice-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #1a1a1a;
        line-height: 1.4;
        letter-spacing: -0.02em;
    }

    .notice-excerpt {
        color: #4a5568;
        font-size: 1rem;
        line-height: 1.7;
        margin-bottom: 1.5rem;
        font-weight: 400;
    }

    .notice-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1.25rem;
        border-top: 1px solid #edf2f7;
        margin-top: 1rem;
    }

    .notice-author {
        color: #4a5568;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .notice-author i {
        margin-right: 0.5rem;
    }

    .notice-link {
        color: #3182ce;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .notice-link:hover {
        color: #2c5282;
    }

    .notice-link i {
        margin-left: 0.5rem;
        transition: transform 0.3s ease;
    }

    .notice-link:hover i {
        transform: translateX(5px);
    }

    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        grid-column: 1 / -1;
        background: white;
        border-radius: 16px;
        border: 2px dashed #e2e8f0;
    }

    .empty-state-icon {
        font-size: 4.5rem;
        color: #a0aec0;
        margin-bottom: 1.5rem;
    }

    .empty-state-text {
        color: #2d3748;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .empty-state-subtext {
        color: #718096;
        font-size: 1.1rem;
    }
</style>

<!-- Notices Section -->
<div class="container">
    <div style="text-align: center; margin: 3rem 0 2rem;">
        <h2 style="font-size: 2.2rem; font-weight: 700; color: #2d3748; margin-bottom: 0.5rem; position: relative; display: inline-block;">
            Latest Notices
            <span style="position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 60%; height: 4px; background: linear-gradient(to right, #3182ce, #63b3ed); border-radius: 2px;"></span>
        </h2>
        <p style="color: #718096; font-size: 1.1rem; margin-top: 1rem;">Stay updated with the most recent announcements</p>
    </div>

    <div class="notice-grid">
        <?php if (empty($notices)): ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list empty-state-icon"></i>
            <h3 class="empty-state-text">No notices found</h3>
            <p class="empty-state-subtext">Try adjusting your search criteria</p>
        </div>
        <?php else: ?>
            <?php foreach ($notices as $notice): ?>
                <div class="notice-card">
                    <?php if ($notice['file_path']): ?>
                        <div class="notice-image">
                            <?php 
                            $file_extension = strtolower(pathinfo($notice['file_path'], PATHINFO_EXTENSION));
                            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/<?php echo $notice['file_path']; ?>" alt="Notice image">
                            <?php else: ?>
                                <i class="fas fa-file-alt file-icon"></i>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="notice-content">
                        <div class="notice-header">
                            <span class="notice-category <?php echo strtolower($notice['category']); ?>">
                                <?php echo $notice['category']; ?>
                            </span>
                            <span class="notice-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                            </span>
                        </div>
                        
                        <h3 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h3>
                        
                        <p class="notice-excerpt">
                            <?php echo substr(strip_tags($notice['description']), 0, 150) . '...'; ?>
                        </p>
                        
                        <div class="notice-footer">
                            <span class="notice-author">
                                <i class="far fa-user"></i>
                                <?php echo htmlspecialchars($notice['posted_by_name']); ?>
                            </span>
                            <a href="view_notice.php?id=<?php echo $notice['id']; ?>" class="notice-link">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/public_footer.php'; ?>
