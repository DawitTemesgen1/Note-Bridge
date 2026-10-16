<?php
/**
 * ============================================================
 * upload.php — Upload a PDF Study Note
 * ============================================================
 * Logged-in students can upload a PDF file with title,
 * description, course, and academic year. The file is stored
 * in /uploads/ and a database row is created with status
 * 'pending' so an admin can approve it.
 * ============================================================
 */

$pageTitle = 'Upload Note';
require_once __DIR__ . '/../includes/config.php';

// Must be logged in
if (!isLoggedIn()) {
    setFlash('error', 'Please log in to upload notes.');
    redirect('login.php');
}

$errors  = [];

// ---------- Fetch departments and courses for the form (PDO Chapter 5) ----------
$departments = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$courses     = $db->query("SELECT c.*, d.name AS department_name FROM courses c JOIN departments d ON c.department_id = d.id ORDER BY c.name")->fetchAll(PDO::FETCH_ASSOC);

// ---------- Handle form submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $courseId     = (int)($_POST['course_id'] ?? 0);
    $year         = (int)($_POST['year'] ?? 0);

    // --- Validation ---
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if ($courseId <= 0) {
        $errors[] = 'Please select a course.';
    }
    if ($year < 2000 || $year > (int)date('Y') + 1) {
        $errors[] = 'Please enter a valid academic year.';
    }

    // --- File validation ---
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please select a PDF file to upload.';
    } else {
        $file     = $_FILES['pdf_file'];
        $fileSize = $file['size'];
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate extension
        if ($fileType !== 'pdf') {
            $errors[] = 'Only PDF files are allowed.';
        }

        // Validate MIME type using fileinfo if available
        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if ($mimeType !== 'application/pdf') {
                $errors[] = 'The uploaded file is not a valid PDF document.';
            }
        }

        if ($fileSize > MAX_FILE_SIZE) {
            $errors[] = 'File size must be under 10 MB.';
        }
    }

    // --- Save file and insert row ---
    if (empty($errors)) {
        // Generate a unique filename to avoid collisions
        $newFileName = uniqid('note_', true) . '.pdf';
        $destination = UPLOAD_DIR . $newFileName;

        // Create uploads directory if it doesn't exist
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $userId = (int)$_SESSION['user_id'];

            // Prepare and execute with PDO (Chapter 5 slide 38)
            $stmt = $db->prepare(
                "INSERT INTO notes (title, description, file_name, file_size, course_id, year, user_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            if ($stmt->execute([$title, $description, $newFileName, $fileSize, $courseId, $year, $userId])) {
                setFlash('success', 'Note uploaded successfully! It will be visible after admin approval.');
                redirect('my_notes.php');
            } else {
                $errors[] = 'Database error. Please try again.';
                // Clean up the uploaded file
                unlink($destination);
            }
        } else {
            $errors[] = 'Failed to save the file. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-upload"></i> Upload Study Note</h1>
        <p>Share your notes with fellow students. Uploads are reviewed before being published.</p>
    </div>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">

            <!-- Show validation errors -->
            <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="border-radius: var(--radius-md); padding: var(--space-3) var(--space-4); margin-bottom: var(--space-6);">
                <ul style="list-style: disc; padding-left: var(--space-5);">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" data-validate>

                <div class="form-group">
                    <label for="title">Note Title</label>
                    <input type="text" id="title" name="title" class="form-control"
                           placeholder="e.g. Data Structures — Chapter 5 Summary"
                           value="<?php echo sanitize($title ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description (optional)</label>
                    <textarea id="description" name="description" class="form-control"
                              placeholder="Briefly describe what this note covers..."><?php echo sanitize($description ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="department_select">Department</label>
                    <select id="department_select" class="form-control">
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>">
                                <?php echo sanitize($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id" class="form-control" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"
                                    data-dept="<?php echo $course['department_id']; ?>"
                                    <?php echo (isset($courseId) && $courseId == $course['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($course['name']); ?>
                                (<?php echo sanitize($course['department_name']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="year">Academic Year</label>
                    <input type="number" id="year" name="year" class="form-control"
                           placeholder="e.g. <?php echo date('Y'); ?>"
                           value="<?php echo sanitize($year ?? date('Y')); ?>"
                           min="2000" max="<?php echo date('Y') + 1; ?>" required>
                </div>

                <!-- Drag-and-drop file upload area -->
                <div class="form-group">
                    <label>PDF File</label>
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h3>Drag & drop your PDF here</h3>
                        <p>or click to browse — Max 10 MB</p>
                    </div>
                    <input type="file" id="fileInput" name="pdf_file" accept=".pdf" style="display: none;">

                    <!-- File preview -->
                    <div class="upload-preview" id="uploadPreview">
                        <i class="fas fa-file-pdf"></i>
                        <div class="file-info">
                            <span></span>
                            <small></small>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-upload"></i> Upload Note
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
