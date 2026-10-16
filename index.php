<?php
/**
 * ============================================================
 * index.php — NoteBridge Home Page
 * ============================================================
 * The landing page that visitors see first. Shows a hero
 * section, feature highlights, and recently approved notes.
 * ============================================================
 */

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

// ---------- Fetch stats for hero section using PDO (Chapter 5 Database Programming) ----------
$statsQuery = "SELECT
    (SELECT COUNT(*) FROM notes WHERE status = 'approved') AS total_notes,
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COALESCE(SUM(downloads), 0) FROM notes WHERE status = 'approved') AS total_downloads";

$stats = $db->query($statsQuery)->fetch(PDO::FETCH_ASSOC) ?: ['total_notes' => 0, 'total_users' => 0, 'total_downloads' => 0];

// ---------- Fetch 6 most recent approved notes using PDO ----------
$recentQuery = "SELECT n.*, c.name AS course_name, d.name AS department_name, u.full_name AS uploader_name
                FROM notes n
                JOIN courses c ON n.course_id = c.id
                JOIN departments d ON c.department_id = d.id
                JOIN users u ON n.user_id = u.id
                WHERE n.status = 'approved'
                ORDER BY n.created_at DESC
                LIMIT 6";
$recentNotes = $db->query($recentQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ==================== HERO SECTION ==================== -->
<section class="hero">
    <div class="hero-content">
        <h1>Share Knowledge,<br>Bridge the Gap</h1>
        <p>Upload, discover, and download quality university study notes. NoteBridge connects students across departments and courses.</p>
        <div class="hero-actions" style="display: flex; justify-content: center; gap: var(--space-4); flex-wrap: wrap;">
            <a href="<?php echo $basePath; ?>/pages/browse.php" class="btn btn-lg btn-accent">
                <i class="fas fa-search"></i> Browse Notes
            </a>
            <?php if (isLoggedIn()): ?>
            <a href="<?php echo $basePath; ?>/pages/upload.php" class="btn btn-lg btn-hero-outline">
                <i class="fas fa-upload"></i> Upload Notes
            </a>
            <?php else: ?>
            <a href="<?php echo $basePath; ?>/pages/register.php" class="btn btn-lg btn-hero-outline">
                <i class="fas fa-user-plus"></i> Join Free
            </a>
            <?php endif; ?>
        </div>

        <!-- Live stats -->
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="stat-number"><?php echo (int)$stats['total_notes']; ?></span>
                <span class="stat-label">Notes Shared</span>
            </div>
            <div class="hero-stat">
                <span class="stat-number"><?php echo (int)$stats['total_users']; ?></span>
                <span class="stat-label">Students</span>
            </div>
            <div class="hero-stat">
                <span class="stat-number"><?php echo (int)($stats['total_downloads'] ?? 0); ?></span>
                <span class="stat-label">Downloads</span>
            </div>
        </div>
    </div>
</section>

<!-- ==================== FEATURES SECTION ==================== -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Why Use NoteBridge?</h2>
            <p>Everything you need to share and find study materials in one place.</p>
        </div>

        <div class="grid grid-4">
            <div class="card feature-card">
                <div class="feature-icon blue">
                    <i class="fas fa-upload"></i>
                </div>
                <h3>Easy Upload</h3>
                <p>Upload your PDF study notes in seconds with drag-and-drop support. Help fellow students succeed.</p>
            </div>

            <div class="card feature-card">
                <div class="feature-icon green">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Smart Search</h3>
                <p>Find notes by department, course, academic year, or keyword. Organised and easy to navigate.</p>
            </div>

            <div class="card feature-card">
                <div class="feature-icon purple">
                    <i class="fas fa-download"></i>
                </div>
                <h3>Instant Download</h3>
                <p>Download approved notes as PDF files, ready for studying on any device, anywhere.</p>
            </div>

            <div class="card feature-card">
                <div class="feature-icon amber">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Rate & Review</h3>
                <p>Rate notes to help the community identify the best resources for each course.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==================== RECENT NOTES SECTION ==================== -->
<section class="section" style="background: var(--gray-100);">
    <div class="container">
        <div class="section-header">
            <h2>Recently Added Notes</h2>
            <p>Fresh study materials uploaded by your peers.</p>
        </div>

        <?php if (!empty($recentNotes)): ?>
        <div class="grid grid-3">
            <?php foreach ($recentNotes as $note): ?>
            <div class="card note-card">
                <div class="card-body">
                    <div class="note-card-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h3><?php echo sanitize($note['title']); ?></h3>
                    <p><?php echo sanitize($note['description'] ?? 'No description provided.'); ?></p>
                    <div class="note-meta">
                        <span class="note-tag course"><i class="fas fa-book"></i> <?php echo sanitize($note['course_name']); ?></span>
                        <span class="note-tag year"><i class="fas fa-calendar"></i> <?php echo (int)$note['year']; ?></span>
                        <span class="note-tag"><i class="fas fa-download"></i> <?php echo (int)$note['downloads']; ?></span>
                    </div>
                    <div class="note-actions">
                        <a href="<?php echo $basePath; ?>/pages/view_note.php?id=<?php echo (int)$note['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8">
            <a href="<?php echo $basePath; ?>/pages/browse.php" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> Browse All Notes
            </a>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No Notes Yet</h3>
            <p>Be the first to upload study notes and help your classmates!</p>
            <a href="<?php echo $basePath; ?>/pages/upload.php" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload Notes
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
