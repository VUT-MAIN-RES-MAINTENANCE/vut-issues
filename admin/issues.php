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

$error = '';
$success = '';

// Handle issue assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign') {
    $issue_id = $_POST['issue_id'] ?? '';
    $staff_id = $_POST['staff_id'] ?? '';
    
    if (empty($issue_id) || empty($staff_id)) {
        $error = 'Issue ID and Staff ID are required.';
    } else {
        $updates = [
            'assigned_to' => htmlspecialchars($staff_id),
            'status' => STATUS_ASSIGNED,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (json_update(MAINTENANCE_REQUESTS_FILE, $issue_id, $updates)) {
            $success = 'Issue assigned successfully!';
            // Log activity
            log_activity($user_id, ROLE_ADMIN, 'assign_issue', "Assigned issue $issue_id to staff $staff_id", $issue_id);
        } else {
            $error = 'Failed to assign issue. Please try again.';
        }
    }
}

// Get all issues and staff
$issues = json_read(MAINTENANCE_REQUESTS_FILE);
$staff = json_read(STAFF_FILE);

// Sort issues by created date (newest first)
usort($issues, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

// Create staff lookup array
$staff_lookup = [];
foreach ($staff as $s) {
    $staff_lookup[$s['id']] = $s['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage Issues - Admin - MainRes Maintenance">
    <meta name="keywords" content="VUT, Admin, Issues">
    <title>Issues - MainRes Maintenance</title>
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
        <h2 class="section-title">Manage Issues</h2>
        <p class="section-subtitle">View and manage all maintenance requests.</p>
        
        <?php if ($error): ?>
            <div class="error-message" style="color: #ef4444; margin-bottom: 1rem; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message" style="color: #22c55e; margin-bottom: 1rem; padding: 1rem; background: rgba(34, 197, 94, 0.1); border-radius: 8px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($issues)): ?>
            <div class="about-hero-banner" style="margin-bottom: 2rem;">
                <h1>No Maintenance Issues</h1>
                <p>No maintenance issues have been reported yet.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Issue ID</th>
                            <th>Issue Type</th>
                            <th>Student</th>
                            <th>Residence</th>
                            <th>Block</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($issues as $issue): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($issue['id'], -8)); ?></td>
                            <td><?php echo htmlspecialchars($issue['issue']); ?></td>
                            <td><?php echo htmlspecialchars($issue['student_name']); ?></td>
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
                            <td><?php echo isset($issue['assigned_to']) && isset($staff_lookup[$issue['assigned_to']]) ? htmlspecialchars($staff_lookup[$issue['assigned_to']]) : 'Unassigned'; ?></td>
                            <td>
                                <?php if (empty($issue['assigned_to']) && !empty($staff)): ?>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="assign">
                                        <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['id']); ?>">
                                        <select name="staff_id" style="padding: 0.3rem; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: white;">
                                            <option value="">Select Staff</option>
                                            <?php foreach ($staff as $s): ?>
                                                <option value="<?php echo htmlspecialchars($s['id']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; margin-left: 0.5rem;">Assign</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Assigned</span>
                                <?php endif; ?>
                            </td>
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
