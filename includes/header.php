<?php
/**
 * ============================================================
 * header.php — Shared Navigation Bar & HTML Head
 * ============================================================
 * Included at the top of every page to provide consistent
 * navigation, branding, and CSS/JS references.
 * ============================================================
 */

// Make sure config is loaded (provides $conn, session, helpers)
require_once __DIR__ . '/config.php';

// Determine the base path so links work from any sub-folder
$basePath = defined('BASE_PATH') ? BASE_PATH : '/Note Bridge';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NoteBridge — Share and discover university study notes. Upload, search, and download PDF notes organised by department and course.">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' — ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons (free CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Our main stylesheet -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/style.css">

    <!-- Base path configuration for JavaScript -->
    <script>
        window.basePath = <?php echo json_encode($basePath); ?>;
    </script>
</head>
<body>

<!-- ==================== NAVIGATION BAR ==================== -->
<nav class="navbar" id="navbar">
    <div class="container nav-container">

        <!-- Logo / Brand -->
        <a href="<?php echo $basePath; ?>/index.php" class="nav-brand">
            <i class="fas fa-book-open"></i>
            <span>Note<strong>Bridge</strong></span>
        </a>

        <!-- Hamburger button (visible on mobile) -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navigation links -->
        <ul class="nav-links" id="navLinks">
            <li><a href="<?php echo $basePath; ?>/index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="<?php echo $basePath; ?>/pages/browse.php"><i class="fas fa-search"></i> Browse Notes</a></li>

            <?php if (isLoggedIn()): ?>
                <!-- Links shown only when logged in -->
                <li><a href="<?php echo $basePath; ?>/pages/upload.php"><i class="fas fa-upload"></i> Upload</a></li>
                <li><a href="<?php echo $basePath; ?>/pages/my_notes.php"><i class="fas fa-folder"></i> My Notes</a></li>
                <li><a href="<?php echo $basePath; ?>/pages/bookmarks.php"><i class="fas fa-bookmark"></i> Bookmarks</a></li>

                <?php if (isAdmin()): ?>
                    <!-- Admin-only link -->
                    <li><a href="<?php echo $basePath; ?>/admin/dashboard.php"><i class="fas fa-shield-alt"></i> Admin</a></li>
                <?php endif; ?>

                <!-- User dropdown -->
                <li class="nav-dropdown">
                    <button class="nav-dropdown-btn" id="userDropdownBtn">
                        <i class="fas fa-user-circle"></i>
                        <?php echo sanitize($_SESSION['full_name']); ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu" id="userDropdownMenu">
                        <li><a href="<?php echo $basePath; ?>/pages/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a href="<?php echo $basePath; ?>/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>

            <?php else: ?>
                <!-- Links shown when NOT logged in -->
                <li><a href="<?php echo $basePath; ?>/pages/login.php" class="btn btn-outline-nav">Login</a></li>
                <li><a href="<?php echo $basePath; ?>/pages/register.php" class="btn btn-primary-nav">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Flash message display -->
<?php
$flash = getFlash();
if ($flash):
?>
<div class="flash-message flash-<?php echo $flash['type']; ?>" id="flashMessage">
    <div class="container">
        <span><?php echo sanitize($flash['message']); ?></span>
        <button class="flash-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
    </div>
</div>
<?php endif; ?>

<!-- Main content wrapper -->
<main class="main-content">
