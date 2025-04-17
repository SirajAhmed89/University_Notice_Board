<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';
require_once 'includes/public_header.php';

if (!isset($_GET['id'])) {
    redirectTo('/index.php');
}

$noticeId = (int)$_GET['id'];

// Get notice details with poster information
$stmt = $db->query(
    "SELECT n.*, u.username as posted_by_name 
     FROM notices n 
     LEFT JOIN users u ON n.posted_by = u.id 
     WHERE n.id = ?", 
    [$noticeId]
);
$notice = $stmt->fetch();

if (!$notice) {
    redirectTo('/index.php');
}

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Function to get file icon based on extension
function getFileIcon($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $iconMap = [
        'pdf' => 'fa-file-pdf',
        'doc' => 'fa-file-word',
        'docx' => 'fa-file-word',
        'xls' => 'fa-file-excel',
        'xlsx' => 'fa-file-excel',
        'ppt' => 'fa-file-powerpoint',
        'pptx' => 'fa-file-powerpoint',
        'jpg' => 'fa-file-image',
        'jpeg' => 'fa-file-image',
        'png' => 'fa-file-image',
        'gif' => 'fa-file-image',
        'zip' => 'fa-file-archive',
        'rar' => 'fa-file-archive',
        'txt' => 'fa-file-alt'
    ];
    
    return isset($iconMap[$extension]) ? $iconMap[$extension] : 'fa-file';
}

// Get related notices (same category)
$stmt = $db->query(
    "SELECT id, title, category, created_at 
    FROM notices 
    WHERE category = ? AND id != ? 
    ORDER BY created_at DESC LIMIT 5",
    [$notice['category'], $noticeId]
);
$relatedNotices = $stmt->fetchAll();
?>

<style>
    .notice-container {
        min-height: 100vh;
        background: #f8fafc;
        padding: 3rem 0;
    }

    .notice-wrapper {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .notice-main {
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
        transition: transform 0.3s ease;
    }

    .notice-header {
        background: linear-gradient(135deg, #3182ce 0%, #63b3ed 100%);
        color: white;
        padding: 2.5rem;
        position: relative;
    }

    .notice-header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
    }

    .notice-title {
        font-size: 2rem;
        margin: 0;
        font-weight: 700;
        flex: 1;
        line-height: 1.4;
        letter-spacing: -0.02em;
    }

    .notice-category-badge {
        background: rgba(255, 255, 255, 0.95);
        color: #3182ce;
        padding: 0.75rem 1.5rem;
        border-radius: 30px;
        font-size: 0.95rem;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .notice-meta {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        margin-top: -1px;
        padding: 1.25rem 2.5rem;
        display: flex;
        gap: 2rem;
        color: #e2e8f0;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .notice-meta i {
        color: #90cdf4;
        margin-right: 0.75rem;
    }

    .notice-content {
        padding: 2.5rem;
        line-height: 1.8;
        color: #2d3748;
        font-size: 1.1rem;
        white-space: pre-line;
    }

    .notice-attachment {
        padding: 2.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .attachment-title {
        font-size: 1.25rem;
        color: #2d3748;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
    }

    .attachment-preview {
        margin-bottom: 2rem;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .attachment-preview img {
        width: 100%;
        max-height: 500px;
        object-fit: contain;
        background: #f8fafc;
    }

    .attachment-preview embed {
        width: 100%;
        height: 500px;
        border: none;
    }

    .attachment-info {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .attachment-icon {
        font-size: 2rem;
        color: #3182ce;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .attachment-details {
        flex: 1;
    }

    .attachment-name {
        font-size: 1rem;
        color: #2d3748;
        margin: 0 0 0.25rem;
        font-weight: 500;
    }

    .attachment-size {
        font-size: 0.875rem;
        color: #718096;
    }

    .download-button {
        background: #3182ce;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
    }

    .download-button:hover {
        background: #2c5282;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(49, 130, 206, 0.3);
    }

    .back-link {
        padding: 1.5rem 2.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        color: #718096;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .back-button:hover {
        color: #3182ce;
    }

    .related-notices {
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .related-title {
        padding: 1.5rem 2.5rem;
        margin: 0;
        border-bottom: 1px solid #e2e8f0;
        font-size: 1.25rem;
        color: #2d3748;
        font-weight: 600;
    }

    .related-list {
        padding: 1.25rem;
    }

    .related-item {
        display: block;
        padding: 1.25rem;
        text-decoration: none;
        border-radius: 16px;
        transition: all 0.3s ease;
    }

    .related-item:hover {
        background: #f8fafc;
        transform: translateX(4px);
    }

    .related-item-title {
        color: #2d3748;
        font-size: 1.1rem;
        margin: 0 0 0.5rem;
        font-weight: 500;
    }

    .related-item-date {
        font-size: 0.875rem;
        color: #718096;
    }

    .related-item-date i {
        margin-right: 0.5rem;
    }
</style>

<div class="notice-container">
    <div class="notice-wrapper">
        <div class="notice-main">
            <div class="notice-header">
                <div class="notice-header-content">
                    <h1 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h1>
                    <span class="notice-category-badge">
                    <?php echo htmlspecialchars(ucfirst($notice['category'])); ?>
                </span>
                </div>
            </div>
            
            <div class="notice-meta">
                <span><i class="fas fa-user"></i><?php echo htmlspecialchars($notice['posted_by_name']); ?></span>
                <span><i class="fas fa-calendar"></i><?php echo date('F j, Y', strtotime($notice['created_at'])); ?></span>
            </div>

            <div class="notice-content">
                <?php echo nl2br(htmlspecialchars($notice['description'])); ?>
            </div>

            <?php if ($notice['file_path'] && file_exists('uploads/' . $notice['file_path'])): ?>
            <div class="notice-attachment">
                <h3 class="attachment-title">
                    <i class="fas fa-paperclip"></i> Attachment
                </h3>
                
                <?php
                $fileExt = strtolower(pathinfo($notice['file_path'], PATHINFO_EXTENSION));
                $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif']);
                $isPdf = $fileExt === 'pdf';
                ?>

                <?php if ($isImage || $isPdf): ?>
                <div class="attachment-preview">
                <?php if ($isImage): ?>
                        <img src="uploads/<?php echo htmlspecialchars($notice['file_path']); ?>" alt="Attachment Preview">
                    <?php else: ?>
                        <embed src="uploads/<?php echo htmlspecialchars($notice['file_path']); ?>" type="application/pdf">
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="attachment-info">
                    <div class="attachment-icon">
                        <i class="fas <?php echo getFileIcon($notice['file_path']); ?>"></i>
                    </div>
                    <div class="attachment-details">
                        <h4 class="attachment-name"><?php echo htmlspecialchars($notice['file_path']); ?></h4>
                        <span class="attachment-size">
                            <?php echo formatFileSize(filesize('uploads/' . $notice['file_path'])); ?>
                        </span>
                    </div>
                    <a href="uploads/<?php echo urlencode($notice['file_path']); ?>" class="download-button" download>
                        <i class="fas fa-download"></i>
                        Download
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <div class="back-link">
                <a href="index.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Notices
                </a>
            </div>
        </div>

        <?php if (!empty($relatedNotices)): ?>
        <div class="related-notices">
            <h3 class="related-title">
                <i class="fas fa-link"></i> Related Notices
            </h3>
            <div class="related-list">
                <?php foreach ($relatedNotices as $relatedNotice): ?>
                <a href="view_notice.php?id=<?php echo $relatedNotice['id']; ?>" class="related-item">
                    <h4 class="related-item-title"><?php echo htmlspecialchars($relatedNotice['title']); ?></h4>
                    <span class="related-item-date">
                        <i class="fas fa-calendar"></i>
                        <?php echo date('F j, Y', strtotime($relatedNotice['created_at'])); ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/public_footer.php'; ?>
