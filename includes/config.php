<?php
/**
 * ============================================================
 * config.php — Database Connection & Global Settings
 * ============================================================
 * This file connects PHP to the MySQL database.
 * It is included at the top of every page that needs
 * database access via: require_once 'includes/config.php';
 * ============================================================
 */

// --- Database credentials (XAMPP defaults) ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // default XAMPP user
define('DB_PASS', '');            // default XAMPP password (empty)
define('DB_NAME', 'notebridge');

// --- Site & Institution settings ---
define('SITE_NAME', 'NoteBridge');
define('UNIVERSITY_NAME', 'Jimma University');
define('INSTITUTE_NAME', 'Jimma Institute of Technology');
define('DEPARTMENT_NAME', 'Computer Science Department');

// Auto-detect base path to seamlessly support root development servers (e.g. php -S)
// and subfolder deployments in XAMPP (e.g. /Note Bridge or /notebridge)
if (!defined('BASE_PATH')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('#^(.*?)/(?:pages|admin|includes|tests)(?:/.*)?$#i', $scriptDir, $m)) {
        $base = rtrim($m[1], '/');
    } else {
        $base = rtrim($scriptDir, '/');
    }
    define('BASE_PATH', $base);
}
define('SITE_URL', (isset($_SERVER['HTTP_HOST']) ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] : 'http://localhost') . BASE_PATH);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024);  // 10 MB max upload

// Global variable for template compatibility
$basePath = BASE_PATH;

// --- Start session if not already started ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Create database connection using PDO (as taught in Chapter 5 Database Programming) ---
// PDO (PHP Data Objects) provides a secure, consistent interface with prepared statements.
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $db = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Raise exceptions on SQL errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC        // Return associative arrays by default
    ]);
    // Assign to $conn as well so both $db and $conn are accessible
    $conn = $db;
} catch (PDOException $e) {
    if (!defined('TEST_MODE') || !TEST_MODE) {
        die("<div style='font-family:sans-serif;padding:2rem;max-width:600px;margin:2rem auto;border:1px solid #f87171;border-radius:8px;background:#fef2f2;color:#991b1b;'>" .
            "<h2>Database Connection Failed (PDO Error)</h2>" .
            "<p>Could not connect to the database <strong>" . DB_NAME . "</strong>.</p>" .
            "<p><strong>Details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>" .
            "<p><strong>Troubleshooting:</strong></p>" .
            "<ol>" .
            "<li>Open XAMPP Control Panel and start <strong>MySQL</strong>.</li>" .
            "<li>Open phpMyAdmin (<a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a>).</li>" .
            "<li>Import the <code>database/notebridge.sql</code> file to create the database and tables.</li>" .
            "</ol></div>");
    }
}

/**
 * Helper: Redirect to a URL and stop script execution.
 * @param string $url — the page to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Helper: Check if the current user is logged in.
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Helper: Check if the current user is an admin.
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Helper: Sanitize user input to prevent XSS attacks.
 * @param string $data — raw user input
 * @return string — cleaned output safe for HTML display
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Helper: Set a flash message that displays once then disappears.
 * @param string $type    — 'success', 'error', or 'info'
 * @param string $message — the text to display
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Helper: Retrieve and clear the flash message.
 * @return array|null — the flash message array or null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
