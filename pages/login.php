<?php
/**
 * ============================================================
 * login.php — User Login Page
 * ============================================================
 * Authenticates students/admins using email + password.
 * On success, sets session variables and redirects home.
 * ============================================================
 */

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect($basePath . '/index.php');
}

$errors = [];

// ---------- Handle form submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- Validation ---
    if (empty($email)) {
        $errors[] = 'Email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // --- Attempt login using PDO (Chapter 5 Database Programming) ---
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables (as taught in Chapter 5 PHPv1)
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];

            setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect($basePath . '/index.php');
        } else {
            $errors[] = 'Incorrect email or password.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="card">
            <div class="card-body">
                <h1><i class="fas fa-sign-in-alt"></i> Login</h1>
                <p class="auth-subtitle">Welcome back to NoteBridge</p>

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
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="you@university.edu"
                               value="<?php echo sanitize($email ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Log In
                    </button>
                </form>

                <p class="auth-footer">
                    Don't have an account?
                    <a href="register.php">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
