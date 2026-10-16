<?php
/**
 * ============================================================
 * get_courses.php — AJAX: Fetch Courses by Department
 * ============================================================
 * Returns JSON array of courses for a given department_id.
 * Uses PDO prepared statements as taught in Chapter 5.
 * ============================================================
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Step 1: Read and cast department ID from GET request
$deptId = (int)($_GET['department_id'] ?? 0);

if ($deptId <= 0) {
    echo json_encode([]);
    exit();
}

// Step 2: Prepare and execute PDO query with positional parameter (Chapter 5 slide 38)
$stmt = $db->prepare("SELECT id, name FROM courses WHERE department_id = ? ORDER BY name ASC");
$stmt->execute([$deptId]);

// Step 3: Fetch all matching rows as an associative array
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Step 4: Return JSON response
echo json_encode($courses);
?>
