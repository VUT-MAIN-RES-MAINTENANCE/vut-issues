<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require login and check role
require_login('../maintenance/login.php');
require_role(ROLE_STAFF, '../index.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_STAFF);

if (!$user) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Staff Interface - MainRes Maintenance</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="home-page">
    <nav class="navbar">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="VUT Logo" class="nav-logo">
            VUT MainRes<span>Maintenance</span>
        </div>
        <ul class="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li><a href="login.php?action=logout" class="nav-btn-primary">Log Out</a></li>
        </ul>
    </nav>

    <section class="page-container">
        <div class="about-hero-banner" style="margin-bottom: 2rem;">
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
            <p>Maintenance interface is under development.</p>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 MainRes Maintenance. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
