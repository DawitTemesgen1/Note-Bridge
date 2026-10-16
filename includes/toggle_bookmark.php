<?php
/**
 * ============================================================
 * toggle_bookmark.php — AJAX Bookmark Toggle
 * ============================================================
 * Adds or removes a bookmark for the logged-in user.
 * Returns JSON: { "status": "added" } or { "status": "removed" }
 * Uses PDO prepared statements as taught in Chapter 5.
 * ============================================================
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Step 1: Check authentication
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Step 2: Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

$noteId = (int)($_POST['note_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if ($noteId <= 0) {
    echo json_encode(['error' => 'Invalid note ID']);
    exit();
}

// Step 3: Check if bookmark already exists using PDO
$stmt = $db->prepare("SELECT id FROM bookmarks WHERE note_id = ? AND user_id = ?");
$stmt->execute([$noteId, $userId]);
$bookmark = $stmt->fetch();

if ($bookmark) {
    // Remove bookmark
    $del = $db->prepare("DELETE FROM bookmarks WHERE note_id = ? AND user_id = ?");
    $del->execute([$noteId, $userId]);
    echo json_encode(['status' => 'removed']);
} else {
    // Add bookmark
    $add = $db->prepare("INSERT INTO bookmarks (note_id, user_id) VALUES (?, ?)");
    $add->execute([$noteId, $userId]);
    echo json_encode(['status' => 'added']);
}
?>
