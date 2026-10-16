<?php
/**
 * ============================================================
 * browse.php — Browse & Search Notes
 * ============================================================
 * Shows approved notes with search, filter by department,
 * course, and year. Includes pagination.
 * ============================================================
 */

$pageTitle = 'Browse Notes';
require_once __DIR__ . '/../includes/config.php';

// ---------- Search / Filter parameters ----------
$search       = trim($_GET['search'] ?? '');
$deptFilter   = (int)($_GET['department'] ?? 0);
$courseFilter  = (int)($_GET['course'] ?? 0);
$yearFilter   = (int)($_GET['year'] ?? 0);
$sortBy       = $_GET['sort'] ?? 'newest';
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 9;
$offset       = ($page - 1) * $perPage;

// ---------- Build query dynamically with PDO (Chapter 5 Database Programming) ----------
$where  = ["n.status = 'approved'"];
$params = [];

if (!empty($search)) {
    $where[]  = "(n.title LIKE ? OR n.description LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($deptFilter > 0) {
    $where[]  = "c.department_id = ?";
    $params[] = $deptFilter;
}

if ($courseFilter > 0) {
    $where[]  = "n.course_id = ?";
    $params[] = $courseFilter;
}

if ($yearFilter > 0) {
    $where[]  = "n.year = ?";
    $params[] = $yearFilter;
}

$whereClause = implode(' AND ', $where);

// Sort order
switch ($sortBy) {
    case 'oldest':    $orderBy = 'n.created_at ASC'; break;
    case 'downloads': $orderBy = 'n.downloads DESC'; break;
    case 'title':     $orderBy = 'n.title ASC'; break;
    default:          $orderBy = 'n.created_at DESC'; break;
}

// ---------- Count total results using PDO ----------
$countSQL = "SELECT COUNT(*)
             FROM notes n
             JOIN courses c ON n.course_id = c.id
             WHERE $whereClause";

$countStmt = $db->prepare($countSQL);
$countStmt->execute($params);
$totalResults = (int)$countStmt->fetchColumn();
$totalPages   = (int)ceil($totalResults / $perPage);

// ---------- Fetch notes using PDO (Chapter 5 slide 38) ----------
$sql = "SELECT n.*, c.name AS course_name, d.name AS department_name,
               u.full_name AS uploader_name,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(r.id) AS rating_count
        FROM notes n
        JOIN courses c ON n.course_id = c.id
        JOIN departments d ON c.department_id = d.id
        JOIN users u ON n.user_id = u.id
        LEFT JOIN ratings r ON r.note_id = n.id
        WHERE $whereClause
        GROUP BY n.id, c.name, d.name, u.full_name
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- Fetch departments and courses for filter dropdowns (PDO) ----------
$departments = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$allCourses  = $db->query("SELECT c.*, d.name AS dept_name FROM courses c JOIN departments d ON c.department_id = d.id ORDER BY c.name")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-search"></i> Browse Notes</h1>
        <p>Search and filter through <?php echo $totalResults; ?> approved study notes.</p>
    </div>

    <!-- ==================== SEARCH & FILTER BAR ==================== -->
    <div class="search-bar">
        <form method="GET" action="">
            <!-- Top Row: Full-width search input + Action buttons -->
            <div class="search-top-row">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" name="search" class="form-control"
                           placeholder="Search notes by title, topic, or keyword..."
                           value="<?php echo sanitize($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if (!empty($search) || $deptFilter > 0 || $courseFilter > 0 || $yearFilter > 0 || $sortBy !== 'newest'): ?>
                <a href="browse.php" class="btn btn-secondary" title="Reset all filters">
                    <i class="fas fa-undo"></i> Reset
                </a>
                <?php endif; ?>
            </div>

            <!-- Filter Row: 4 Equal Grid Columns (Department, Course, Year, Sort) -->
            <div class="filter-grid-row">
                <div class="form-group">
                    <label for="departmentFilter"><i class="fas fa-building"></i> Department</label>
                    <select id="departmentFilter" name="department" class="form-control">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"
                                    <?php echo ($deptFilter == $dept['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="courseFilter"><i class="fas fa-book"></i> Course</label>
                    <select id="courseFilter" name="course" class="form-control">
                        <option value="">All Courses (Select Department to filter)</option>
                        <?php foreach ($allCourses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"
                                    data-dept="<?php echo $c['department_id']; ?>"
                                    <?php echo ($courseFilter == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="yearFilter"><i class="fas fa-calendar"></i> Academic Year</label>
                    <select name="year" id="yearFilter" class="form-control">
                        <option value="">All Years</option>
                        <?php for ($y = (int)date('Y'); $y >= 2015; $y--): ?>
                            <option value="<?php echo $y; ?>"
                                    <?php echo ($yearFilter == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sortFilter"><i class="fas fa-sort"></i> Sort By</label>
                    <select name="sort" id="sortFilter" class="form-control">
                        <option value="newest"    <?php echo ($sortBy === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest"    <?php echo ($sortBy === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="downloads" <?php echo ($sortBy === 'downloads') ? 'selected' : ''; ?>>Most Downloaded</option>
                        <option value="title"     <?php echo ($sortBy === 'title') ? 'selected' : ''; ?>>Title (A-Z)</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- ==================== RESULTS GRID ==================== -->
    <?php if (!empty($notes)): ?>
    <div class="grid grid-3">
        <?php foreach ($notes as $note): ?>
        <div class="card note-card">
            <div class="card-body">
                <div class="note-card-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <h3><a href="view_note.php?id=<?php echo (int)$note['id']; ?>" style="color:inherit;"><?php echo sanitize($note['title']); ?></a></h3>
                <p><?php echo sanitize($note['description'] ?? 'No description provided.'); ?></p>
                <div class="note-meta">
                    <span class="note-tag course"><i class="fas fa-book"></i> <?php echo sanitize($note['course_name']); ?></span>
                    <span class="note-tag year"><i class="fas fa-calendar"></i> <?php echo (int)$note['year']; ?></span>
                    <span class="note-tag"><i class="fas fa-download"></i> <?php echo (int)$note['downloads']; ?></span>
                </div>

                <!-- Star display -->
                <div class="stars" title="<?php echo round($note['avg_rating'], 1); ?>/5 (<?php echo (int)$note['rating_count']; ?> ratings)">
                    <?php
                    $avg = round($note['avg_rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $avg
                            ? '<i class="fas fa-star"></i>'
                            : '<i class="fas fa-star empty"></i>';
                    }
                    ?>
                </div>

                <div class="note-actions">
                    <a href="view_note.php?id=<?php echo (int)$note['id']; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="download.php?id=<?php echo (int)$note['id']; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        // Build the query string without 'page' param
        $queryParams = $_GET;
        unset($queryParams['page']);
        $queryString = http_build_query($queryParams);

        if ($page > 1): ?>
            <a href="?<?php echo $queryString; ?>&page=<?php echo $page - 1; ?>">&laquo;</a>
        <?php endif;

        for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="active"><?php echo $p; ?></span>
            <?php else: ?>
                <a href="?<?php echo $queryString; ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
            <?php endif; ?>
        <?php endfor;

        if ($page < $totalPages): ?>
            <a href="?<?php echo $queryString; ?>&page=<?php echo $page + 1; ?>">&raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>No Notes Found</h3>
        <p>Try adjusting your search or filter criteria.</p>
        <a href="browse.php" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Clear Filters
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
