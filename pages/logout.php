<?php
/**
 * ============================================================
 * logout.php — Destroy Session and Log Out
 * ============================================================
 */

require_once __DIR__ . '/../includes/config.php';

// Destroy session data
session_unset();
session_destroy();

// Redirect to home with flash
session_start();
setFlash('info', 'You have been logged out.');
redirect($basePath . '/index.php');
?>
