<?php
/**
 * ============================================================
 * download.php — Download a Note PDF
 * ============================================================
 * Increments the download counter and sends the PDF file.
 * ============================================================
 */

require_once __DIR__ . '/../includes/config.php';

$noteId = (int)($_GET['id'] ?? 0);

if ($noteId <= 0) {
    setFlash('error', 'Invalid note ID.');
    redirect('browse.php');
}

// Current viewer context
$currentUserId = isLoggedIn() ? (int)$_SESSION['user_id'] : 0;
$isAdminUser   = isAdmin() ? 1 : 0;

// Fetch the note using PDO (approved notes accessible to all; pending/rejected accessible to admin and author)
$stmt = $db->prepare("SELECT file_name, title, status FROM notes WHERE id = ? AND (status = 'approved' OR ? = 1 OR user_id = ?)");
$stmt->execute([$noteId, $isAdminUser, $currentUserId]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    setFlash('error', 'Note not found or not approved.');
    redirect('browse.php');
}

$filePath = UPLOAD_DIR . $note['file_name'];

if (!file_exists($filePath)) {
    setFlash('error', 'The file could not be found on the server.');
    redirect('browse.php');
}

// Increment download counter only for approved notes using PDO
if ($note['status'] === 'approved') {
    $updateStmt = $db->prepare("UPDATE notes SET downloads = downloads + 1 WHERE id = ?");
    $updateStmt->execute([$noteId]);
}

// Clean any previous output buffer to avoid corrupting the binary PDF stream
if (ob_get_level()) {
    ob_end_clean();
}

// Send the file as a download
$downloadName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $note['title']) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');

readfile($filePath);
exit();
?>
