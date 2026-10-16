<?php
/**
 * ============================================================
 * admin/dashboard.php — Admin Dashboard & Site Management
 * ============================================================
 * Admin panel with:
 *   - Overview stats (users, notes, downloads, departments, courses)
 *   - Pending notes approval/rejection
 *   - All notes management & deletion
 *   - User management & deletion
 *   - Department management (Add, Edit, Delete with cascade cleanup)
 *   - Course management (Add, Edit, Delete with cascade cleanup)
 * Uses PDO prepared statements strictly as taught in Chapter 5.
 * ============================================================
 */

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/config.php';

// Only admins can access
if (!isLoggedIn() || !isAdmin()) {
    setFlash('error', 'Access denied. Admin privileges required.');
    redirect($basePath . '/index.php');
}

// ============================================================
// 1. HANDLE POST ACTIONS (Add & Edit Departments / Courses)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Add Department ---
    if ($action === 'add_department') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            setFlash('error', 'Department name is required.');
        } else {
            $check = $db->prepare("SELECT id FROM departments WHERE name = ?");
            $check->execute([$name]);
            if ($check->fetch()) {
                setFlash('error', 'A department with that name already exists.');
            } else {
                $stmt = $db->prepare("INSERT INTO departments (name) VALUES (?)");
                $stmt->execute([$name]);
                setFlash('success', 'Department "' . sanitize($name) . '" added successfully.');
            }
        }
        redirect('dashboard.php?tab=departments');
    }

    // --- Edit Department ---
    if ($action === 'edit_department') {
        $id   = (int)($_POST['dept_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || $name === '') {
            setFlash('error', 'Please provide a valid department name.');
        } else {
            $check = $db->prepare("SELECT id FROM departments WHERE name = ? AND id != ?");
            $check->execute([$name, $id]);
            if ($check->fetch()) {
                setFlash('error', 'Another department with that name already exists.');
            } else {
                $stmt = $db->prepare("UPDATE departments SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                setFlash('success', 'Department updated successfully.');
            }
        }
        redirect('dashboard.php?tab=departments');
    }

    // --- Add Course ---
    if ($action === 'add_course') {
        $name   = trim($_POST['name'] ?? '');
        $deptId = (int)($_POST['department_id'] ?? 0);
        if ($name === '' || $deptId <= 0) {
            setFlash('error', 'Please provide a course name and select a valid department.');
        } else {
            $check = $db->prepare("SELECT id FROM courses WHERE name = ? AND department_id = ?");
            $check->execute([$name, $deptId]);
            if ($check->fetch()) {
                setFlash('error', 'This course already exists in the selected department.');
            } else {
                $stmt = $db->prepare("INSERT INTO courses (name, department_id) VALUES (?, ?)");
                $stmt->execute([$name, $deptId]);
                setFlash('success', 'Course "' . sanitize($name) . '" added successfully.');
            }
        }
        redirect('dashboard.php?tab=courses');
    }

    // --- Edit Course ---
    if ($action === 'edit_course') {
        $id     = (int)($_POST['course_id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $deptId = (int)($_POST['department_id'] ?? 0);
        if ($id <= 0 || $name === '' || $deptId <= 0) {
            setFlash('error', 'Please provide valid course details.');
        } else {
            $check = $db->prepare("SELECT id FROM courses WHERE name = ? AND department_id = ? AND id != ?");
            $check->execute([$name, $deptId, $id]);
            if ($check->fetch()) {
                setFlash('error', 'Another course with that name already exists in that department.');
            } else {
                $stmt = $db->prepare("UPDATE courses SET name = ?, department_id = ? WHERE id = ?");
                $stmt->execute([$name, $deptId, $id]);
                setFlash('success', 'Course updated successfully.');
            }
        }
        redirect('dashboard.php?tab=courses');
    }
}

// ============================================================
// 2. HANDLE GET ACTIONS (Approve, Reject, Deletes)
// ============================================================

// Approve a note
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $stmt = $db->prepare("UPDATE notes SET status = 'approved' WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Note approved.');
    redirect('dashboard.php?tab=pending');
}

// Reject a note
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $stmt = $db->prepare("UPDATE notes SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('info', 'Note rejected.');
    redirect('dashboard.php?tab=pending');
}

// Delete a note
if (isset($_GET['delete_note'])) {
    $id = (int)$_GET['delete_note'];

    $fileStmt = $db->prepare("SELECT file_name FROM notes WHERE id = ?");
    $fileStmt->execute([$id]);
    $fileResult = $fileStmt->fetch(PDO::FETCH_ASSOC);

    if ($fileResult) {
        $filePath = UPLOAD_DIR . $fileResult['file_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $db->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Note deleted.');
    redirect('dashboard.php?tab=notes');
}

// Delete a user (and clean up their files on disk first)
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];

    if ($id === (int)$_SESSION['user_id']) {
        setFlash('error', 'You cannot delete your own account.');
    } else {
        $userFilesStmt = $db->prepare("SELECT file_name FROM notes WHERE user_id = ?");
        $userFilesStmt->execute([$id]);
        $files = $userFilesStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($files as $f) {
            $path = UPLOAD_DIR . $f['file_name'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'User and associated notes deleted successfully.');
    }
    redirect('dashboard.php?tab=users');
}

// Delete a department (clean up disk files for all notes in it)
if (isset($_GET['delete_dept'])) {
    $id = (int)$_GET['delete_dept'];

    $fileStmt = $db->prepare("SELECT n.file_name FROM notes n JOIN courses c ON n.course_id = c.id WHERE c.department_id = ?");
    $fileStmt->execute([$id]);
    $files = $fileStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($files as $f) {
        $path = UPLOAD_DIR . $f['file_name'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $stmt = $db->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Department and all its associated courses and notes deleted.');
    redirect('dashboard.php?tab=departments');
}

// Delete a course (clean up disk files for all notes in it)
if (isset($_GET['delete_course'])) {
    $id = (int)$_GET['delete_course'];

    $fileStmt = $db->prepare("SELECT file_name FROM notes WHERE course_id = ?");
    $fileStmt->execute([$id]);
    $files = $fileStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($files as $f) {
        $path = UPLOAD_DIR . $f['file_name'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Course and all its associated notes deleted.');
    redirect('dashboard.php?tab=courses');
}

// ============================================================
// 3. FETCH DATA & STATS USING PDO (Chapter 5)
// ============================================================
$totalUsers       = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalNotes       = (int)$db->query("SELECT COUNT(*) FROM notes")->fetchColumn();
$pendingNotes     = (int)$db->query("SELECT COUNT(*) FROM notes WHERE status = 'pending'")->fetchColumn();
$totalDownloads   = (int)$db->query("SELECT COALESCE(SUM(downloads), 0) FROM notes")->fetchColumn();
$totalDepartments = (int)$db->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$totalCourses     = (int)$db->query("SELECT COUNT(*) FROM courses")->fetchColumn();

// Fetch pending notes
$pending = $db->query(
    "SELECT n.*, c.name AS course_name, u.full_name AS uploader_name
     FROM notes n
     JOIN courses c ON n.course_id = c.id
     JOIN users u ON n.user_id = u.id
     WHERE n.status = 'pending'
     ORDER BY n.created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Fetch all notes
$allNotes = $db->query(
    "SELECT n.*, c.name AS course_name, u.full_name AS uploader_name
     FROM notes n
     JOIN courses c ON n.course_id = c.id
     JOIN users u ON n.user_id = u.id
     ORDER BY n.created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Fetch all users
$allUsers = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch departments with course and note counts
$allDepartments = $db->query(
    "SELECT d.*,
            COUNT(DISTINCT c.id) AS course_count,
            COUNT(DISTINCT n.id) AS note_count
     FROM departments d
     LEFT JOIN courses c ON c.department_id = d.id
     LEFT JOIN notes n ON n.course_id = c.id
     GROUP BY d.id
     ORDER BY d.name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses with department names and note counts
$allCoursesList = $db->query(
    "SELECT c.*,
            d.name AS department_name,
            COUNT(n.id) AS note_count
     FROM courses c
     JOIN departments d ON c.department_id = d.id
     LEFT JOIN notes n ON n.course_id = c.id
     GROUP BY c.id
     ORDER BY d.name ASC, c.name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Check if currently editing a department
$editDept = null;
if (isset($_GET['edit_dept'])) {
    $editDeptId = (int)$_GET['edit_dept'];
    $editDeptStmt = $db->prepare("SELECT * FROM departments WHERE id = ?");
    $editDeptStmt->execute([$editDeptId]);
    $editDept = $editDeptStmt->fetch(PDO::FETCH_ASSOC);
}

// Check if currently editing a course
$editCourse = null;
if (isset($_GET['edit_course'])) {
    $editCourseId = (int)$_GET['edit_course'];
    $editCourseStmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
    $editCourseStmt->execute([$editCourseId]);
    $editCourse = $editCourseStmt->fetch(PDO::FETCH_ASSOC);
}

// Determine active tab
$activeTab = $_GET['tab'] ?? 'pending';
if ($editDept)   $activeTab = 'departments';
if ($editCourse) $activeTab = 'courses';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-shield-alt"></i> Admin Dashboard</h1>
        <p>Manage notes, users, departments, courses, and site content.</p>
    </div>

    <!-- ===== Stats Cards ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-file-alt"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalNotes; ?></div>
                <div class="stat-label">Total Notes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-number"><?php echo $pendingNotes; ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-download"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalDownloads; ?></div>
                <div class="stat-label">Total Downloads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class="fas fa-building"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalDepartments; ?></div>
                <div class="stat-label">Departments</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon indigo"><i class="fas fa-book"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalCourses; ?></div>
                <div class="stat-label">Courses</div>
            </div>
        </div>
    </div>

    <!-- ===== Tab Navigation ===== -->
    <div class="tabs">
        <button class="tab-btn <?php echo ($activeTab === 'pending') ? 'active' : ''; ?>" data-tab="tab-pending">
            <i class="fas fa-clock"></i> Pending (<?php echo $pendingNotes; ?>)
        </button>
        <button class="tab-btn <?php echo ($activeTab === 'notes') ? 'active' : ''; ?>" data-tab="tab-notes">
            <i class="fas fa-file-alt"></i> All Notes (<?php echo $totalNotes; ?>)
        </button>
        <button class="tab-btn <?php echo ($activeTab === 'users') ? 'active' : ''; ?>" data-tab="tab-users">
            <i class="fas fa-users"></i> Users (<?php echo $totalUsers; ?>)
        </button>
        <button class="tab-btn <?php echo ($activeTab === 'departments') ? 'active' : ''; ?>" data-tab="tab-departments">
            <i class="fas fa-building"></i> Departments (<?php echo count($allDepartments); ?>)
        </button>
        <button class="tab-btn <?php echo ($activeTab === 'courses') ? 'active' : ''; ?>" data-tab="tab-courses">
            <i class="fas fa-book"></i> Courses (<?php echo count($allCoursesList); ?>)
        </button>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 1: Pending Notes                                         -->
    <!-- ============================================================ -->
    <div class="tab-content <?php echo ($activeTab === 'pending') ? 'active' : ''; ?>" id="tab-pending">
        <?php if (!empty($pending)): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $note): ?>
                    <tr>
                        <td><strong><?php echo sanitize($note['title']); ?></strong></td>
                        <td><?php echo sanitize($note['course_name']); ?></td>
                        <td><?php echo sanitize($note['uploader_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($note['created_at'])); ?></td>
                        <td><?php echo number_format($note['file_size'] / 1024, 1); ?> KB</td>
                        <td>
                            <a href="<?php echo $basePath; ?>/pages/view_note.php?id=<?php echo $note['id']; ?>" class="btn btn-primary btn-sm" title="View Note Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo $basePath; ?>/pages/download.php?id=<?php echo $note['id']; ?>" class="btn btn-secondary btn-sm" title="Download PDF">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="dashboard.php?approve=<?php echo $note['id']; ?>" class="btn btn-success btn-sm" title="Approve">
                                <i class="fas fa-check"></i> Approve
                            </a>
                            <a href="dashboard.php?reject=<?php echo $note['id']; ?>" class="btn btn-danger btn-sm" title="Reject">
                                <i class="fas fa-times"></i> Reject
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <h3>All Caught Up!</h3>
            <p>There are no notes waiting for approval.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 2: All Notes                                             -->
    <!-- ============================================================ -->
    <div class="tab-content <?php echo ($activeTab === 'notes') ? 'active' : ''; ?>" id="tab-notes">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Uploaded By</th>
                        <th>Status</th>
                        <th>Downloads</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allNotes as $note): ?>
                    <tr>
                        <td><strong><?php echo sanitize($note['title']); ?></strong></td>
                        <td><?php echo sanitize($note['course_name']); ?></td>
                        <td><?php echo sanitize($note['uploader_name']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $note['status']; ?>">
                                <?php echo ucfirst($note['status']); ?>
                            </span>
                        </td>
                        <td><?php echo (int)$note['downloads']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($note['created_at'])); ?></td>
                        <td>
                            <a href="<?php echo $basePath; ?>/pages/view_note.php?id=<?php echo $note['id']; ?>" class="btn btn-primary btn-sm" title="View Note">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo $basePath; ?>/pages/download.php?id=<?php echo $note['id']; ?>" class="btn btn-secondary btn-sm" title="Download PDF">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php if ($note['status'] !== 'approved'): ?>
                                <a href="dashboard.php?approve=<?php echo $note['id']; ?>" class="btn btn-success btn-sm" title="Approve">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>
                            <a href="dashboard.php?delete_note=<?php echo $note['id']; ?>"
                               class="btn btn-danger btn-sm"
                               data-confirm="Are you sure you want to permanently delete this note?"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 3: Users                                                 -->
    <!-- ============================================================ -->
    <div class="tab-content <?php echo ($activeTab === 'users') ? 'active' : ''; ?>" id="tab-users">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $u): ?>
                    <tr>
                        <td><strong><?php echo sanitize($u['full_name']); ?></strong></td>
                        <td><?php echo sanitize($u['email']); ?></td>
                        <td>
                            <span class="badge <?php echo ($u['role'] === 'admin') ? 'badge-approved' : 'badge-pending'; ?>">
                                <?php echo ucfirst($u['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="dashboard.php?delete_user=<?php echo $u['id']; ?>"
                               class="btn btn-danger btn-sm"
                               data-confirm="Are you sure you want to delete this user? Their notes will also be deleted.">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            <?php else: ?>
                            <span class="text-muted text-small">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 4: Departments Management                               -->
    <!-- ============================================================ -->
    <div class="tab-content <?php echo ($activeTab === 'departments') ? 'active' : ''; ?>" id="tab-departments">
        <!-- Add or Edit Department Card -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-body">
                <?php if ($editDept): ?>
                <h3 style="margin-bottom: var(--space-4); color: var(--primary);">
                    <i class="fas fa-edit"></i> Edit Department: <?php echo sanitize($editDept['name']); ?>
                </h3>
                <form method="POST" action="dashboard.php?tab=departments" style="display: flex; gap: var(--space-3); align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="edit_department">
                    <input type="hidden" name="dept_id" value="<?php echo (int)$editDept['id']; ?>">
                    <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                        <label for="edit_dept_name">Department Name</label>
                        <input type="text" id="edit_dept_name" name="name" class="form-control"
                               value="<?php echo sanitize($editDept['name']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="height: 44px;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="dashboard.php?tab=departments" class="btn btn-secondary" style="height: 44px; display: inline-flex; align-items: center;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </form>
                <?php else: ?>
                <h3 style="margin-bottom: var(--space-4);">
                    <i class="fas fa-plus-circle"></i> Add New Department
                </h3>
                <form method="POST" action="dashboard.php?tab=departments" style="display: flex; gap: var(--space-3); align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="add_department">
                    <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                        <label for="new_dept_name">Department Name</label>
                        <input type="text" id="new_dept_name" name="name" class="form-control"
                               placeholder="e.g. Civil Engineering" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="height: 44px;">
                        <i class="fas fa-plus"></i> Add Department
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Departments List Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Department Name</th>
                        <th>Total Courses</th>
                        <th>Total Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allDepartments)): ?>
                        <?php foreach ($allDepartments as $dept): ?>
                        <tr>
                            <td><?php echo (int)$dept['id']; ?></td>
                            <td><strong><?php echo sanitize($dept['name']); ?></strong></td>
                            <td><span class="badge badge-approved"><?php echo (int)$dept['course_count']; ?> courses</span></td>
                            <td><?php echo (int)$dept['note_count']; ?> notes</td>
                            <td>
                                <a href="dashboard.php?edit_dept=<?php echo (int)$dept['id']; ?>" class="btn btn-primary btn-sm" title="Edit Department">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="dashboard.php?delete_dept=<?php echo (int)$dept['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   data-confirm="Are you sure you want to delete '<?php echo sanitize($dept['name']); ?>'? All associated courses and note files will also be permanently deleted."
                                   title="Delete Department">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--gray-400); padding: var(--space-6);">
                                No departments found. Add your first department above!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 5: Courses Management                                   -->
    <!-- ============================================================ -->
    <div class="tab-content <?php echo ($activeTab === 'courses') ? 'active' : ''; ?>" id="tab-courses">
        <!-- Add or Edit Course Card -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-body">
                <?php if ($editCourse): ?>
                <h3 style="margin-bottom: var(--space-4); color: var(--primary);">
                    <i class="fas fa-edit"></i> Edit Course: <?php echo sanitize($editCourse['name']); ?>
                </h3>
                <form method="POST" action="dashboard.php?tab=courses" style="display: flex; gap: var(--space-3); align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="edit_course">
                    <input type="hidden" name="course_id" value="<?php echo (int)$editCourse['id']; ?>">
                    <div class="form-group" style="flex: 2; min-width: 250px; margin-bottom: 0;">
                        <label for="edit_course_name">Course Name</label>
                        <input type="text" id="edit_course_name" name="name" class="form-control"
                               value="<?php echo sanitize($editCourse['name']); ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1.5; min-width: 220px; margin-bottom: 0;">
                        <label for="edit_course_dept">Department</label>
                        <select id="edit_course_dept" name="department_id" class="form-control" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($allDepartments as $dept): ?>
                                <option value="<?php echo (int)$dept['id']; ?>" <?php echo ($editCourse['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="height: 44px;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="dashboard.php?tab=courses" class="btn btn-secondary" style="height: 44px; display: inline-flex; align-items: center;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </form>
                <?php else: ?>
                <h3 style="margin-bottom: var(--space-4);">
                    <i class="fas fa-plus-circle"></i> Add New Course
                </h3>
                <form method="POST" action="dashboard.php?tab=courses" style="display: flex; gap: var(--space-3); align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="add_course">
                    <div class="form-group" style="flex: 2; min-width: 250px; margin-bottom: 0;">
                        <label for="new_course_name">Course Name</label>
                        <input type="text" id="new_course_name" name="name" class="form-control"
                               placeholder="e.g. Artificial Intelligence" required>
                    </div>
                    <div class="form-group" style="flex: 1.5; min-width: 220px; margin-bottom: 0;">
                        <label for="new_course_dept">Department</label>
                        <select id="new_course_dept" name="department_id" class="form-control" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($allDepartments as $dept): ?>
                                <option value="<?php echo (int)$dept['id']; ?>">
                                    <?php echo sanitize($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="height: 44px;">
                        <i class="fas fa-plus"></i> Add Course
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Courses List Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Course Name</th>
                        <th>Department</th>
                        <th>Notes Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allCoursesList)): ?>
                        <?php foreach ($allCoursesList as $course): ?>
                        <tr>
                            <td><?php echo (int)$course['id']; ?></td>
                            <td><strong><?php echo sanitize($course['name']); ?></strong></td>
                            <td><span class="badge badge-approved"><?php echo sanitize($course['department_name']); ?></span></td>
                            <td><?php echo (int)$course['note_count']; ?> notes</td>
                            <td>
                                <a href="dashboard.php?edit_course=<?php echo (int)$course['id']; ?>" class="btn btn-primary btn-sm" title="Edit Course">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="dashboard.php?delete_course=<?php echo (int)$course['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   data-confirm="Are you sure you want to delete '<?php echo sanitize($course['name']); ?>'? All associated notes will also be permanently deleted."
                                   title="Delete Course">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--gray-400); padding: var(--space-6);">
                                No courses found. Add your first course above!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
