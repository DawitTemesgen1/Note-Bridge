<?php
/**
 * ============================================================
 * profile.php — User Profile Page
 * ============================================================
 * Shows the logged-in user's profile and lets them update
 * their name, bio, and password.
 * ============================================================
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/config.php';

// Must be logged in
if (!isLoggedIn()) {
    setFlash('error', 'Please log in to view your profile.');
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$errors = [];

// ---------- Handle profile update ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($fullName)) {
        $errors[] = 'Full name is required.';
    }

    // Update password only if provided
    $updatePassword = false;
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        $updatePassword = true;
    }

    if (empty($errors)) {
        if ($updatePassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET full_name = ?, bio = ?, password = ? WHERE id = ?");
            $executed = $stmt->execute([$fullName, $bio, $hashedPassword, $userId]);
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, bio = ? WHERE id = ?");
            $executed = $stmt->execute([$fullName, $bio, $userId]);
        }

        if ($executed) {
            $_SESSION['full_name'] = $fullName;
            setFlash('success', 'Profile updated successfully!');
            redirect('profile.php');
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

// ---------- Fetch current user data using PDO (Chapter 5 Database Programming) ----------
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ---------- Fetch upload stats using PDO ----------
$statsStmt = $db->prepare(
    "SELECT COUNT(*) AS total_uploads,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
            COALESCE(SUM(downloads), 0) AS total_downloads
     FROM notes WHERE user_id = ?"
);
$statsStmt->execute([$userId]);
$userStats = $statsStmt->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <!-- Profile header -->
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="profile-info">
            <h1><?php echo sanitize($user['full_name']); ?></h1>
            <p><i class="fas fa-envelope"></i> <?php echo sanitize($user['email']); ?></p>
            <p><i class="fas fa-calendar"></i> Joined <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            <p class="text-muted"><?php echo sanitize($user['bio'] ?? 'No bio yet.'); ?></p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid stats-grid-3 mb-8">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-upload"></i></div>
            <div>
                <div class="stat-number"><?php echo (int)$userStats['total_uploads']; ?></div>
                <div class="stat-label">Uploads</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-number"><?php echo (int)$userStats['approved']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-download"></i></div>
            <div>
                <div class="stat-number"><?php echo (int)$userStats['total_downloads']; ?></div>
                <div class="stat-label">Total Downloads</div>
            </div>
        </div>
    </div>

    <!-- Edit profile form -->
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h3><i class="fas fa-edit"></i> Edit Profile</h3>
        </div>
        <div class="card-body">
            <!-- Show errors -->
            <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="border-radius: var(--radius-md); padding: var(--space-3) var(--space-4); margin-bottom: var(--space-6);">
                <ul style="list-style: disc; padding-left: var(--space-5);">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?php echo sanitize($user['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" class="form-control"
                              placeholder="Tell us about yourself..."><?php echo sanitize($user['bio'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="new_password" name="new_password" class="form-control"
                           placeholder="At least 6 characters">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                           placeholder="Re-enter new password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
