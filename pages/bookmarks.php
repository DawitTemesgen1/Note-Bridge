<?php
/**
 * ============================================================
 * bookmarks.php — View Bookmarked Notes
 * ============================================================
 * Shows all notes that the logged-in user has bookmarked.
 * Uses PDO prepared statements as taught in Chapter 5.
 * ============================================================
 */

$pageTitle = 'Bookmarks';
require_once __DIR__ . '/../includes/config.php';

// Must be logged in
if (!isLoggedIn()) {
    setFlash('error', 'Please log in to view your bookmarks.');
    redirect('login.php');
}

$userId = (int)$_SESSION['user_id'];

// Fetch bookmarked notes using PDO (Chapter 5)
$stmt = $db->prepare(
    "SELECT n.*, c.name AS course_name, d.name AS department_name,
            b.created_at AS bookmarked_at
     FROM bookmarks b
     JOIN notes n ON b.note_id = n.id
     JOIN courses c ON n.course_id = c.id
     JOIN departments d ON c.department_id = d.id
     WHERE b.user_id = ? AND n.status = 'approved'
     ORDER BY b.created_at DESC"
);
$stmt->execute([$userId]);
$bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-bookmark"></i> My Bookmarks</h1>
        <p>You have <?php echo count($bookmarks); ?> bookmarked note(s).</p>
    </div>

    <?php if (!empty($bookmarks)): ?>
    <div class="grid grid-3">
        <?php foreach ($bookmarks as $note): ?>
        <div class="card note-card">
            <div class="card-body">
                <div class="note-card-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <h3><?php echo sanitize($note['title']); ?></h3>
                <p><?php echo sanitize($note['description'] ?? 'No description provided.'); ?></p>
                <div class="note-meta">
                    <span class="note-tag course"><i class="fas fa-book"></i> <?php echo sanitize($note['course_name']); ?></span>
                    <span class="note-tag year"><i class="fas fa-calendar"></i> <?php echo (int)$note['year']; ?></span>
                </div>
                <div class="note-actions">
                    <a href="view_note.php?id=<?php echo (int)$note['id']; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <button class="btn btn-secondary btn-sm bookmark-btn active" data-note-id="<?php echo (int)$note['id']; ?>">
                        <i class="fas fa-bookmark"></i> <span class="bookmark-label">Remove</span>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="far fa-bookmark"></i>
        <h3>No Bookmarks Yet</h3>
        <p>Browse notes and bookmark the ones you want to come back to.</p>
        <a href="browse.php" class="btn btn-primary">
            <i class="fas fa-search"></i> Browse Notes
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
