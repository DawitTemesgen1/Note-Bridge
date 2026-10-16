<?php
/**
 * ============================================================
 * my_notes.php — View Your Uploaded Notes
 * ============================================================
 * Shows all notes uploaded by the logged-in user with their
 * approval status. Users can delete their own notes here.
 * Uses PDO prepared statements as taught in Chapter 5.
 * ============================================================
 */

$pageTitle = 'My Notes';
require_once __DIR__ . '/../includes/config.php';

// Must be logged in
if (!isLoggedIn()) {
    setFlash('error', 'Please log in to view your notes.');
    redirect('login.php');
}

$userId = (int)$_SESSION['user_id'];

// ---------- Handle delete request (Chapter 5 PDO) ----------
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];

    // Verify ownership using PDO
    $checkStmt = $db->prepare("SELECT file_name FROM notes WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$deleteId, $userId]);
    $note = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($note) {
        $noteFile = $note['file_name'];

        // Delete from database using PDO
        $delStmt = $db->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        $delStmt->execute([$deleteId, $userId]);

        // Delete file from disk
        $filePath = UPLOAD_DIR . $noteFile;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        setFlash('success', 'Note deleted successfully.');
    } else {
        setFlash('error', 'Note not found or you do not own it.');
    }
    redirect('my_notes.php');
}

// ---------- Fetch user's notes using PDO ----------
$stmt = $db->prepare(
    "SELECT n.*, c.name AS course_name
     FROM notes n
     JOIN courses c ON n.course_id = c.id
     WHERE n.user_id = ?
     ORDER BY n.created_at DESC"
);
$stmt->execute([$userId]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header flex-between">
        <div>
            <h1><i class="fas fa-folder"></i> My Notes</h1>
            <p>You have uploaded <?php echo count($notes); ?> note(s).</p>
        </div>
        <a href="upload.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Upload New
        </a>
    </div>

    <?php if (!empty($notes)): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Downloads</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $note): ?>
                <tr>
                    <td><strong><?php echo sanitize($note['title']); ?></strong></td>
                    <td><?php echo sanitize($note['course_name']); ?></td>
                    <td><?php echo (int)$note['year']; ?></td>
                    <td>
                        <span class="badge badge-<?php echo $note['status']; ?>">
                            <?php echo ucfirst($note['status']); ?>
                        </span>
                    </td>
                    <td><?php echo (int)$note['downloads']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($note['created_at'])); ?></td>
                    <td>
                        <a href="view_note.php?id=<?php echo (int)$note['id']; ?>" class="btn btn-primary btn-sm" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="download.php?id=<?php echo (int)$note['id']; ?>" class="btn btn-secondary btn-sm" title="Download PDF">
                            <i class="fas fa-download"></i>
                        </a>
                        <a href="my_notes.php?delete=<?php echo (int)$note['id']; ?>"
                           class="btn btn-danger btn-sm"
                           data-confirm="Are you sure you want to delete this note?"
                           title="Delete Note">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-folder-open"></i>
        <h3>No Notes Uploaded</h3>
        <p>You haven't uploaded any study notes yet. Get started!</p>
        <a href="upload.php" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Your First Note
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
