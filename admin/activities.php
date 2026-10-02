<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

// Require login
require_login('login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_ADMIN);

if (!$user) {
    header('Location: login.php');
    exit;
}

// Get all activities
$activities = json_read(ACTIVITIES_FILE);

// Sort by timestamp (newest first)
usort($activities, function($a, $b) {
    return strtotime($b['timestamp'] ?? 0) - strtotime($a['timestamp'] ?? 0);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View Activities - Admin - MainRes Maintenance">
    <meta name="keywords" content="VUT, Admin, Activities">
    <title>Activities - MainRes Maintenance</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="home-page">
    <nav class="navbar">
        <input type="checkbox" id="nav-toggle" class="nav-toggle-input">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="VUT Logo" class="nav-logo">
            VUT MainRes<span>Maintenance</span>
        </div>
        <label for="nav-toggle" class="menu-toggle-btn">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </label>
        <ul class="nav-links" id="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li><a href="students.php" class="nav-btn-text">Students</a></li>
            <li><a href="maintenance-staff.php" class="nav-btn-text">Maintenance Staff</a></li>
            <li><a href="issues.php" class="nav-btn-text">Issues</a></li>
            <li><a href="activities.php" class="nav-btn-text">Activities</a></li>
            <li><a href="settings.php" class="nav-btn-text">Settings</a></li>
            <li><a href="login.php?action=logout" class="nav-btn-primary">Log Out</a></li>
        </ul>
    </nav>

    <section class="page-container">
        <h2 class="section-title">Activity Log</h2>
        <p class="section-subtitle">Monitor activities from students and maintenance staff.</p>
        
        <?php if (empty($activities)): ?>
            <div class="about-hero-banner" style="margin-bottom: 2rem;">
                <h1>No Activities Recorded</h1>
                <p>No activities have been logged yet.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User Role</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Related ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i:s', strtotime($activity['timestamp'] ?? 'now')); ?></td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; 
                                    <?php
                                    $role_colors = [
                                        ROLE_STUDENT => 'background: #3b82f6; color: white;',
                                        ROLE_STAFF => 'background: #8b5cf6; color: white;',
                                        ROLE_ADMIN => 'background: #ef4444; color: white;'
                                    ];
                                    echo $role_colors[$activity['user_role']] ?? 'background: #6b7280; color: white;';
                                    ?>">
                                    <?php echo htmlspecialchars(ucfirst($activity['user_role'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                            <td><?php echo htmlspecialchars($activity['description'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(substr($activity['related_id'] ?? '', -8)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <footer class="footer">
        <div class="footer-services">
            <div class="footer-column">
                <div class="footer-column-title">Our Services</div>
                <p class="footer-column-text">We offer a wide range of premium maintenance services to keep your property in perfect condition.</p>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Quick Navigation</div>
                <a href="../index.php">Home</a>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Connect with us</div>
                <div class="social-icons">
                    <a href="https://www.facebook.com/VUT.ac.za/" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://x.com/VUT_Science" target="_blank" rel="noopener"><i class="fab fa-x-twitter"></i></a>
                    <a href="https://www.linkedin.com/school/vaal-university-of-technology/" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.instagram.com/vut_university/" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.tiktok.com/@vut_university" target="_blank" rel="noopener"><i class="fab fa-tiktok"></i></a>
                    <a href="https://www.youtube.com/user/VUTTV" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
                </div>
                <div class="footer-column-title">Official Site</div>
                <a href="https://vut.ac.za/" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Visit VUT Website</a>
            </div>
        </div>
        <div class="footer-content">
            <div class="footer-brand">
                <img src="../assets/images/logo.png" alt="MainRes Logo" class="footer-logo">
                <p>&copy; 2026 MainRes Maintenance. All rights reserved.</p>
            </div>
            <div class="footer-links">
                <a href="mailto:vut@mainresmaintenance.com" class="footer-link">vut@mainresmaintenance.com</a>
            </div>
        </div>
    </footer>
</body>
</html>
