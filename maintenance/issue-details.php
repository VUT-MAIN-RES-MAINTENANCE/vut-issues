<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

// Require login
require_login('login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_STAFF);

if (!$user) {
    header('Location: login.php');
    exit;
}

// Get issue ID from URL
$issue_id = $_GET['id'] ?? '';

if (empty($issue_id)) {
    header('Location: assigned-issues.php');
    exit;
}

// Get the issue
$issue = json_find_by_id(MAINTENANCE_REQUESTS_FILE, $issue_id);

if (!$issue) {
    header('Location: assigned-issues.php');
    exit;
}

// Verify issue is assigned to this staff member
if (!isset($issue['assigned_to']) || $issue['assigned_to'] != $user_id) {
    header('Location: assigned-issues.php');
    exit;
}

$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'] ?? '';
    $maintenance_notes = trim($_POST['maintenance_notes'] ?? '');
    
    // Validate status
    $valid_statuses = [STATUS_ASSIGNED, STATUS_IN_PROGRESS, STATUS_COMPLETED];
    if (!in_array($new_status, $valid_statuses)) {
        $error = 'Invalid status.';
    } else {
        $updates = [
            'status' => $new_status,
            'maintenance_notes' => htmlspecialchars($maintenance_notes),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (json_update(MAINTENANCE_REQUESTS_FILE, $issue_id, $updates)) {
            $success = 'Issue updated successfully!';
            // Refresh issue data
            $issue = json_find_by_id(MAINTENANCE_REQUESTS_FILE, $issue_id);
            // Log activity
            log_activity($user_id, ROLE_STAFF, 'update_issue', "Updated issue status to: $new_status", $issue_id);
        } else {
            $error = 'Failed to update issue. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Issue Details - Maintenance Staff - MainRes Maintenance">
    <meta name="keywords" content="VUT, Maintenance Staff, Issue Details">
    <title>Issue Details - MainRes Maintenance</title>
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
            <li><a href="assigned-issues.php" class="nav-btn-text">Assigned Issues</a></li>
            <li><a href="profile.php" class="nav-btn-text">Profile</a></li>
            <li><a href="login.php?action=logout" class="nav-btn-primary">Log Out</a></li>
        </ul>
    </nav>

    <section class="page-container">
        <h2 class="section-title">Issue Details</h2>
        <p class="section-subtitle">View detailed information about a maintenance issue.</p>
        
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
        
        <div class="about-hero-banner" style="margin-bottom: 2rem;">
            <h1>Maintenance Issue #<?php echo htmlspecialchars(substr($issue['id'], -8)); ?></h1>
            <p>Issue reported by <?php echo htmlspecialchars($issue['student_name']); ?></p>
        </div>
        
        <div class="form-container">
            <div style="background: rgba(255, 255, 255, 0.05); padding: 2rem; border-radius: 8px; margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">Issue Information</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div>
                        <strong>Issue Type:</strong><br>
                        <?php echo htmlspecialchars($issue['issue']); ?>
                    </div>
                    <div>
                        <strong>Status:</strong><br>
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
                    </div>
                    <div>
                        <strong>Residence:</strong><br>
                        <?php echo htmlspecialchars($issue['residence']); ?>
                    </div>
                    <div>
                        <strong>Block:</strong><br>
                        <?php echo htmlspecialchars($issue['block']); ?>
                    </div>
                    <div>
                        <strong>Room:</strong><br>
                        <?php echo htmlspecialchars($issue['room']); ?>
                    </div>
                    <div>
                        <strong>Gender:</strong><br>
                        <?php echo htmlspecialchars($issue['gender']); ?>
                    </div>
                    <div>
                        <strong>Student No:</strong><br>
                        <?php echo htmlspecialchars($issue['student_no']); ?>
                    </div>
                    <div>
                        <strong>Reported:</strong><br>
                        <?php echo date('M d, Y H:i', strtotime($issue['created_at'])); ?>
                    </div>
                </div>
                
                <?php if (!empty($issue['description'])): ?>
                    <div style="margin-top: 1rem;">
                        <strong>Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($issue['description'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($issue['image'])): ?>
                    <div style="margin-top: 1rem;">
                        <strong>Issue Picture:</strong><br>
                        <img src="../<?php echo htmlspecialchars($issue['image']); ?>" alt="Issue picture" style="max-width: 300px; border-radius: 8px; margin-top: 0.5rem;">
                    </div>
                <?php endif; ?>
            </div>
            
            <form class="modern-form" method="POST" action="">
                <h3 style="margin-bottom: 1rem;">Update Status</h3>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="<?php echo STATUS_ASSIGNED; ?>" <?php echo $issue['status'] === STATUS_ASSIGNED ? 'selected' : ''; ?>>Assigned</option>
                        <option value="<?php echo STATUS_IN_PROGRESS; ?>" <?php echo $issue['status'] === STATUS_IN_PROGRESS ? 'selected' : ''; ?>>In Progress</option>
                        <option value="<?php echo STATUS_COMPLETED; ?>" <?php echo $issue['status'] === STATUS_COMPLETED ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Maintenance Notes</label>
                    <textarea name="maintenance_notes" placeholder="Add notes about the maintenance work..."><?php echo htmlspecialchars($issue['maintenance_notes'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Issue</button>
            </form>
        </div>
        
        <div style="margin-top: 2rem;">
            <a href="assigned-issues.php" class="btn" style="display: inline-block; padding: 0.6rem 1.2rem; background: rgba(255, 255, 255, 0.1); color: white; text-decoration: none; border-radius: 4px;">&larr; Back to Assigned Issues</a>
        </div>
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
