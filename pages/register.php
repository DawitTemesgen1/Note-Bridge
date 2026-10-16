<?php
/**
 * ============================================================
 * register.php — User Registration Page
 * ============================================================
 * Lets new students create an account with their name, email,
 * and password. Passwords are hashed with bcrypt before storing.
 * ============================================================
 */

$pageTitle = 'Sign Up';
require_once __DIR__ . '/../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect($basePath . '/index.php');
}

$errors = [];

// ---------- Handle form submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $fullName        = trim($_POST['full_name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (empty($fullName)) {
        $errors[] = 'Full name is required.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if email already exists using PDO (Chapter 5 Database Programming)
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    // --- Insert user if no errors ---
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$fullName, $email, $hashedPassword])) {
            setFlash('success', 'Account created successfully! Please log in.');
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="card">
            <div class="card-body">
                <h1><i class="fas fa-user-plus"></i> Sign Up</h1>
                <p class="auth-subtitle">Create your free NoteBridge account</p>

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

                <form method="POST" action="" data-validate>
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                               placeholder="Enter your full name"
                               value="<?php echo sanitize($fullName ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="you@university.edu"
                               value="<?php echo sanitize($email ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="At least 6 characters" data-minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                               placeholder="Re-enter your password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <p class="auth-footer">
                    Already have an account?
                    <a href="login.php">Log in here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
