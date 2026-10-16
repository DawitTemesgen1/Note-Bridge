<?php
/**
 * ============================================================
 * view_note.php — Single Note Detail Page
 * ============================================================
 * Shows full details of an approved note including metadata,
 * description, download button, bookmark, and star rating.
 * ============================================================
 */

$pageTitle = 'View Note';
require_once __DIR__ . '/../includes/config.php';

// ---------- Get note ID from URL ----------
$noteId = (int)($_GET['id'] ?? 0);

if ($noteId <= 0) {
    setFlash('error', 'Invalid note ID.');
    redirect('browse.php');
}

// Current viewer context
$currentUserId = isLoggedIn() ? (int)$_SESSION['user_id'] : 0;
$isAdminUser   = isAdmin() ? 1 : 0;

// ---------- Fetch note details ----------
// Approved notes are visible to everyone; pending/rejected notes are visible to author and admins
// ---------- Fetch note details using PDO (Chapter 5 Database Programming) ----------
// Approved notes are visible to everyone; pending/rejected notes are visible to author and admins
$stmt = $db->prepare(
    "SELECT n.*, c.name AS course_name, d.name AS department_name,
            u.full_name AS uploader_name, u.id AS uploader_id
     FROM notes n
     JOIN courses c ON n.course_id = c.id
     JOIN departments d ON c.department_id = d.id
     JOIN users u ON n.user_id = u.id
     WHERE n.id = ? AND (n.status = 'approved' OR ? = 1 OR n.user_id = ?)"
);
$stmt->execute([$noteId, $isAdminUser, $currentUserId]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    setFlash('error', 'Note not found or not yet approved.');
    redirect('browse.php');
}

$pageTitle = $note['title'];

// ---------- Fetch average rating using PDO ----------
$ratingStmt = $db->prepare(
    "SELECT COALESCE(AVG(rating), 0) AS avg_rating, COUNT(*) AS rating_count
     FROM ratings WHERE note_id = ?"
);
$ratingStmt->execute([$noteId]);
$ratingData = $ratingStmt->fetch(PDO::FETCH_ASSOC);

// ---------- Check if user has already rated ----------
$userRating = 0;
if (isLoggedIn()) {
    $urStmt = $db->prepare("SELECT rating FROM ratings WHERE note_id = ? AND user_id = ?");
    $urStmt->execute([$noteId, (int)$_SESSION['user_id']]);
    $userRatingRow = $urStmt->fetch(PDO::FETCH_ASSOC);
    if ($userRatingRow) {
        $userRating = (int)$userRatingRow['rating'];
    }
}

// ---------- Check if bookmarked ----------
$isBookmarked = false;
if (isLoggedIn()) {
    $bmStmt = $db->prepare("SELECT id FROM bookmarks WHERE note_id = ? AND user_id = ?");
    $bmStmt->execute([$noteId, (int)$_SESSION['user_id']]);
    $isBookmarked = (bool)$bmStmt->fetch();
}

// ---------- Handle rating submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && isLoggedIn()) {
    $newRating = max(1, min(5, (int)$_POST['rating']));
    $userId    = (int)$_SESSION['user_id'];

    // Insert or update rating using PDO
    $rateStmt = $db->prepare(
        "INSERT INTO ratings (note_id, user_id, rating) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE rating = VALUES(rating)"
    );
    $rateStmt->execute([$noteId, $userId, $newRating]);

    setFlash('success', 'Your rating has been saved!');
    redirect("view_note.php?id=$noteId");
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="note-detail-grid">

        <!-- ===== LEFT: Note Details ===== -->
        <div>
            <?php if ($note['status'] !== 'approved'): ?>
            <div class="flash-message flash-info" style="border-radius: var(--radius-md); padding: var(--space-3) var(--space-4); margin-bottom: var(--space-6);">
                <i class="fas fa-info-circle"></i>
                <strong>Notice:</strong> This note is currently <strong><?php echo ucfirst($note['status']); ?></strong>. Only the author and administrators can see this page.
            </div>
            <?php endif; ?>

            <div class="note-detail-header">
                <div class="note-detail-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="note-detail-info">
                    <h1><?php echo sanitize($note['title']); ?></h1>
                    <div class="note-meta">
                        <span class="note-tag course"><i class="fas fa-book"></i> <?php echo sanitize($note['course_name']); ?></span>
                        <span class="note-tag"><i class="fas fa-building"></i> <?php echo sanitize($note['department_name']); ?></span>
                        <span class="note-tag year"><i class="fas fa-calendar"></i> <?php echo (int)$note['year']; ?></span>
                        <?php if ($note['status'] !== 'approved'): ?>
                        <span class="badge badge-<?php echo $note['status']; ?>"><?php echo ucfirst($note['status']); ?></span>
                        <?php endif; ?>
                    </div>
                    <p><?php echo nl2br(sanitize($note['description'] ?? 'No description provided.')); ?></p>

                    <div class="note-detail-actions">
                        <a href="download.php?id=<?php echo $noteId; ?>" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-secondary bookmark-btn <?php echo $isBookmarked ? 'active' : ''; ?>"
                                data-note-id="<?php echo $noteId; ?>">
                            <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark"></i>
                            <span class="bookmark-label"><?php echo $isBookmarked ? 'Bookmarked' : 'Bookmark'; ?></span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rating Section -->
            <?php if (isLoggedIn()): ?>
            <div class="card" style="margin-top: var(--space-6);">
                <div class="card-body">
                    <h3 style="margin-bottom: var(--space-4);">Rate this Note</h3>
                    <form method="POST" action="">
                        <div class="star-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?php echo ($i <= $userRating) ? 'fas' : 'far'; ?> fa-star <?php echo ($i <= $userRating) ? 'filled' : ''; ?>"
                                   data-value="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="<?php echo $userRating; ?>">
                        <button type="submit" class="btn btn-primary btn-sm" style="margin-top: var(--space-4);">
                            <i class="fas fa-paper-plane"></i> Submit Rating
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== RIGHT: Sidebar ===== -->
        <div class="detail-sidebar">
            <h3>Note Details</h3>
            <div class="detail-meta-row">
                <span class="label">Uploaded by</span>
                <span class="value"><?php echo sanitize($note['uploader_name']); ?></span>
            </div>
            <div class="detail-meta-row">
                <span class="label">Upload Date</span>
                <span class="value"><?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
            </div>
            <div class="detail-meta-row">
                <span class="label">File Size</span>
                <span class="value"><?php echo number_format($note['file_size'] / 1024, 1); ?> KB</span>
            </div>
            <div class="detail-meta-row">
                <span class="label">Downloads</span>
                <span class="value"><?php echo (int)$note['downloads']; ?></span>
            </div>
            <div class="detail-meta-row">
                <span class="label">Average Rating</span>
                <span class="value">
                    <?php echo round($ratingData['avg_rating'], 1); ?>/5
                    <small class="text-muted">(<?php echo (int)$ratingData['rating_count']; ?>)</small>
                </span>
            </div>
            <div class="detail-meta-row">
                <span class="label">Course</span>
                <span class="value"><?php echo sanitize($note['course_name']); ?></span>
            </div>
            <div class="detail-meta-row">
                <span class="label">Department</span>
                <span class="value"><?php echo sanitize($note['department_name']); ?></span>
            </div>
        </div>
    </div>

    <!-- Back link -->
    <div style="margin-top: var(--space-8);">
        <a href="browse.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Browse
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
