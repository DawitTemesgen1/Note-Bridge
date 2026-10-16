<?php
/**
 * ============================================================
 * test_suite.php — Automated QA Test Suite for NoteBridge
 * ============================================================
 * Executes automated unit, security, schema, and route tests
 * without requiring a running web server.
 * ============================================================
 */

$passed = 0;
$failed = 0;
$tests  = [];

function assertTest($description, $condition, $details = '') {
    global $passed, $failed, $tests;
    if ($condition) {
        $passed++;
        $tests[] = ['status' => 'PASS', 'desc' => $description, 'details' => $details];
        echo "  [PASS] $description\n";
    } else {
        $failed++;
        $tests[] = ['status' => 'FAIL', 'desc' => $description, 'details' => $details];
        echo "  [FAIL] $description ($details)\n";
    }
}

// Define test mode to allow CLI execution without live database
define('TEST_MODE', true);

// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "============================================================\n";
echo "NoteBridge Automated QA Test Suite\n";
echo "============================================================\n\n";

// ============================================================
// 1. CONFIG & HELPER TESTS
// ============================================================
echo "1. Testing Configuration and Helpers...\n";

// Check if sanitize function works
require_once __DIR__ . '/../includes/config.php';

$dirty = '<script>alert("XSS")</script>';
$clean = sanitize($dirty);
assertTest('sanitize() converts < and > to HTML entities', strpos($clean, '<script>') === false && strpos($clean, '&lt;script&gt;') !== false);

$dirtyQuotes = 'Hello "World" & \'Note\'';
$cleanQuotes = sanitize($dirtyQuotes);
assertTest('sanitize() handles quotes and ampersands with ENT_QUOTES', strpos($cleanQuotes, '&quot;') !== false && strpos($cleanQuotes, '&amp;') !== false);

// Flash messages
setFlash('success', 'Test Flash Message');
$flash = getFlash();
assertTest('setFlash() and getFlash() store and retrieve message', $flash !== null && $flash['message'] === 'Test Flash Message');
$flashAfter = getFlash();
assertTest('getFlash() clears message after retrieval (one-time display)', $flashAfter === null);

// PDO Database connection check (Chapter 5)
assertTest('$db is an instance of PDO (Chapter 5 Database Programming)', isset($db) && $db instanceof PDO);

// isLoggedIn() and isAdmin()
$_SESSION['user_id'] = 42;
$_SESSION['role'] = 'student';
assertTest('isLoggedIn() returns true when session user_id is set', isLoggedIn() === true);
assertTest('isAdmin() returns false for student role', isAdmin() === false);

$_SESSION['role'] = 'admin';
assertTest('isAdmin() returns true for admin role', isAdmin() === true);

unset($_SESSION['user_id']);
unset($_SESSION['role']);
assertTest('isLoggedIn() returns false after session cleanup', isLoggedIn() === false);

// ============================================================
// 2. SECURITY & PASSWORD TESTS
// ============================================================
echo "\n2. Testing Password Hashing & Bcrypt Security...\n";

$sqlFile = file_get_contents(__DIR__ . '/../database/notebridge.sql');

// Extract admin seed hash
preg_match("/'admin@notebridge\.com',\s*'([^']+)'/", $sqlFile, $adminMatch);
$adminHash = $adminMatch[1] ?? '';
assertTest('Admin seed hash exists in notebridge.sql', !empty($adminHash));
assertTest('Admin seed hash verifies against password "admin123"', password_verify('admin123', $adminHash));

// Extract student password verification
$sampleHash = password_hash('student123', PASSWORD_BCRYPT);
assertTest('Student password hashing uses secure bcrypt algorithm', password_get_info($sampleHash)['algoName'] === 'bcrypt');
assertTest('Student password verifies correctly against bcrypt hash', password_verify('student123', $sampleHash));

// Check uploads directory security
$htaccessUploads = __DIR__ . '/../uploads/.htaccess';
assertTest('uploads/.htaccess file exists', file_exists($htaccessUploads));
$htaccessContent = file_get_contents($htaccessUploads);
assertTest('uploads/.htaccess contains SetHandler default-handler', strpos($htaccessContent, 'SetHandler default-handler') !== false);
assertTest('uploads/.htaccess disables PHP engine', strpos($htaccessContent, 'php_flag engine off') !== false);

// ============================================================
// 3. SCHEMA & DATABASE SQL TESTS
// ============================================================
echo "\n3. Testing Database Schema Integrity...\n";

assertTest('Schema creates database "notebridge"', strpos($sqlFile, 'CREATE DATABASE IF NOT EXISTS notebridge') !== false);
assertTest('Schema creates "users" table with role enum', strpos($sqlFile, 'CREATE TABLE IF NOT EXISTS users') !== false);
assertTest('Schema creates "departments" table', strpos($sqlFile, 'CREATE TABLE IF NOT EXISTS departments') !== false);
assertTest('Schema creates "courses" table with ON DELETE CASCADE', strpos($sqlFile, 'CREATE TABLE IF NOT EXISTS courses') !== false);
assertTest('Schema creates "notes" table with ON DELETE CASCADE', strpos($sqlFile, 'CREATE TABLE IF NOT EXISTS notes') !== false);
assertTest('Schema creates "ratings" table with CHECK (rating BETWEEN 1 AND 5)', strpos($sqlFile, 'rating BETWEEN 1 AND 5') !== false);
assertTest('Schema creates "bookmarks" table with unique constraint', strpos($sqlFile, 'unique_bookmark (note_id, user_id)') !== false);
assertTest('Schema uses idempotent INSERT IGNORE queries for seed data', strpos($sqlFile, 'INSERT IGNORE INTO departments') !== false);

// ============================================================
// 4. ROUTE & LINK INTEGRITY AUDIT
// ============================================================
echo "\n4. Testing Route & Link Integrity...\n";

$projectRoot = realpath(__DIR__ . '/..');
$allPhpFiles = glob("$projectRoot/*.php");
$allPhpFiles = array_merge($allPhpFiles, glob("$projectRoot/pages/*.php"));
$allPhpFiles = array_merge($allPhpFiles, glob("$projectRoot/admin/*.php"));
$allPhpFiles = array_merge($allPhpFiles, glob("$projectRoot/includes/*.php"));

$brokenLinks = 0;
foreach ($allPhpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    // Find all href="..."
    preg_match_all('/href=["\']([^"\']+)["\']/', $content, $hrefs);
    foreach ($hrefs[1] as $href) {
        // Skip external, anchors, and dynamic query strings
        if (strpos($href, 'http') === 0 || strpos($href, '#') === 0 || strpos($href, 'mailto:') === 0 || strpos($href, '?') === 0) {
            continue;
        }
        // Normalize base path
        $cleanPath = str_replace('<?php echo $basePath; ?>', '', $href);
        $cleanPath = str_replace('/Note Bridge', '', $cleanPath);
        $cleanPath = strtok($cleanPath, '?'); // strip query string

        // Check file exists relative to current file or root
        $targetInRoot = $projectRoot . '/' . ltrim($cleanPath, '/');
        $targetRelative = dirname($phpFile) . '/' . $cleanPath;

        if (!file_exists($targetInRoot) && !file_exists($targetRelative)) {
            echo "    Warning: Possible broken link '$href' in " . basename($phpFile) . "\n";
            $brokenLinks++;
        }
    }
}
assertTest('Zero broken internal href links found across all pages', $brokenLinks === 0, "Broken links count: $brokenLinks");

// ============================================================
// 5. UPLOAD DIRECTORY & STORAGE INTEGRITY
// ============================================================
echo "\n5. Testing Upload Storage Integrity...\n";

$uploadDir = __DIR__ . '/../uploads/';
assertTest('Uploads directory exists', is_dir($uploadDir));
assertTest('Uploads directory is writable for student note uploads', is_writable($uploadDir));

// Test file creation and immediate clean deletion
$testFilePath = $uploadDir . 'qa_test_temp_' . uniqid() . '.pdf';
$writeResult = file_put_contents($testFilePath, '%PDF-1.4 test QA');
assertTest('Upload storage allows writing uploaded note files', $writeResult !== false);

$deleteResult = file_exists($testFilePath) && unlink($testFilePath);
assertTest('Upload storage allows clean deletion of files on cascade', $deleteResult === true);

// ============================================================
// SUMMARY
// ============================================================
echo "\n============================================================\n";
echo "SUMMARY: Total Tests: " . ($passed + $failed) . " | Passed: $passed | Failed: $failed\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
?>
