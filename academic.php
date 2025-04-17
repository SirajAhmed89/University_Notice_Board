<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';
require_once 'includes/public_header.php';

// Get academic notices
$stmt = $db->query(
    "SELECT n.*, u.username as posted_by_name 
     FROM notices n 
     LEFT JOIN users u ON n.posted_by = u.id 
     WHERE n.category = 'academic'
     ORDER BY n.created_at DESC"
);
$notices = $stmt->fetchAll();
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
        <?php if (!empty($notices)): ?>
            <?php foreach ($notices as $notice): ?>
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); transition: transform 0.3s ease;">
                    <div style="padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <span style="background: #e0f2fe; color: #0369a1; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.875rem; font-weight: 500;">
                                <?php echo htmlspecialchars(ucfirst($notice['category'])); ?>
                            </span>
                            <span style="font-size: 0.875rem; color: #64748b;">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M j, Y', strtotime($notice['created_at'])); ?>
                            </span>
                        </div>
                        <h3 style="font-size: 1.25rem; color: #1e293b; margin-bottom: 0.75rem; font-weight: 600; line-height: 1.4;">
                            <?php echo htmlspecialchars($notice['title']); ?>
                        </h3>
                        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
                            <?php echo substr(htmlspecialchars($notice['description']), 0, 150) . '...'; ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                            <span style="font-size: 0.875rem; color: #64748b;">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($notice['posted_by_name']); ?>
                            </span>
                            <a href="view_notice.php?id=<?php echo $notice['id']; ?>" style="color: #0061f2; font-size: 0.875rem; font-weight: 500; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e0; margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.25rem; color: #2d3748; margin-bottom: 0.5rem;">No Academic Notices</h3>
                <p style="color: #718096;">There are currently no academic notices to display.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/public_footer.php'; ?>
