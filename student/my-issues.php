<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

// Require login
require_login('login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_STUDENT);

if (!$user) {
    header('Location: login.php');
    exit;
}

// Get all maintenance requests
$all_requests = json_read(MAINTENANCE_REQUESTS_FILE);

// Filter to show only this student's issues
$my_issues = array_filter($all_requests, function($request) use ($user_id) {
    return isset($request['student_id']) && $request['student_id'] == $user_id;
});

// Sort by created date (newest first)
usort($my_issues, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View your reported maintenance issues - MainRes Maintenance">
    <meta name="keywords" content="VUT, My Issues, Maintenance">
    <title>My Issues - Malogesinte?action=logoutnance</title>
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
            <li><a href="../index.php?page=about">About</a></li>
            <li><a href="report-issue.php">Report</a></li>
            <li><a href="my-issues.php" class="nav-btn-text">My Issues</a></li>
            <li><a href="profile.php" class="nav-btn-text">Profile</a></li>
            <li><a href="../index.php" class="nav-btn-primary">Log Out</a></li>
        </ul>
    </nav>

    <section class="page-container">
        <h2 class="section-title">My Issues</h2>
        <p class="section-subtitle">View and track your reported maintenance issues.</p>
        
        <?php if (empty($my_issues)): ?>
            <div class="about-hero-banner" style="margin-bottom: 2rem;">
                <h1>No Issues Reported</h1>
                <p>You haven't reported any maintenance issues yet. <a href="report-issue.php" style="color: var(--accent-color);">Report your first issue now.</a></p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Issue ID</th>
                            <th>Issue Type</th>
                            <th>Residence</th>
                            <th>Block</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_issues as $issue): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($issue['id'], -8)); ?></td>
                            <td><?php echo htmlspecialchars($issue['issue']); ?></td>
                            <td><?php echo htmlspecialchars($issue['residence']); ?></td>
                            <td><?php echo htmlspecialchars($issue['block']); ?></td>
                            <td><?php echo htmlspecialchars($issue['room']); ?></td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; 
                                    <?php
                                    $status_colors = [
                                        STATUS_PENDING => 'background: #f59e0b; color: white;',
                                        STATUS_ASSIGNED => 'background: #3b82f6; color: white;',
                                        STATUS_IN_PROGRESS => 'background: #8b5cf6; color: white;',
                                        STATUS_COMPLETED => 'background: #22c55e; color: white;'
                                    ];
                                    echo $status_colors[$issue['status']] ?? 'background: #6b7280; color: white;';
                                    ?>">
                                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $issue['status']))); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($issue['created_at'])); ?></td>
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
                <div class="footer-column-title">Maintenance Services</div>
                <a href="../index.php?page=about">Bulb Replacement</a>
                <a href="../index.php?page=about">Window Handle</a>
                <a href="../index.php?page=about">Door Handle</a>
                <a href="../index.php?page=about">WiFi Problems</a>
                <a href="../index.php?page=about">Leakage Problems</a>
                <a href="../index.php?page=about">Stove Problem</a>
                <a href="../index.php?page=about">HVAC</a>
                <a href="../index.php?page=about">Painting</a>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Quick Navigation</div>
                <a href="../index.php">Home</a>
                <a href="report-issue.php">Report Issue</a>
                <a href="../index.php?page=about">About</a>
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
