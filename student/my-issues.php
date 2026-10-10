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

// Get user initials for avatar
$user_initials = strtoupper(substr($user['name'], 0, 1));

// Helper function for status badge
function getStatusBadge($status) {
    $badges = [
        STATUS_PENDING => '<span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">Pending</span>',
        STATUS_ASSIGNED => '<span style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">Assigned</span>',
        STATUS_IN_PROGRESS => '<span style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">In Progress</span>',
        STATUS_COMPLETED => '<span style="background: rgba(34, 197, 94, 0.15); color: #22c55e; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">Fixed</span>'
    ];
    return $badges[$status] ?? $status;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View your reported maintenance issues - MainRes Maintenance">
    <meta name="keywords" content="VUT, My Issues, Maintenance">
    <title>My Issues - Student Interface</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <button class="sidebar-toggle" onclick="document.querySelector('.student-sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>

    <div class="student-layout">
        <!-- Sidebar -->
        <aside class="student-sidebar">
            <div class="student-sidebar-header">
                <img src="../assets/images/logo.png" alt="VUT Logo">
                <div>
                    <h2>VUT MainRes</h2>
                    <span>Student</span>
                </div>
            </div>

            <nav class="student-sidebar-nav">
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="my-issues.php" class="active">
                    <i class="fas fa-clipboard-list"></i>
                    My Issues
                </a>
                <a href="report-issue.php">
                    <i class="fas fa-plus-circle"></i>
                    Report Issue
                </a>
                <a href="profile.php">
                    <i class="fas fa-user"></i>
                    My Profile
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </nav>

            <div class="student-sidebar-footer">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
                <a href="login.php?action=logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Log Out
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="student-main">
            <div class="student-header">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">
                        <?php echo date('l, F j, Y'); ?> · <?php echo date('g:i A'); ?>
                    </div>
                    <h1>My Issues</h1>
                    <p>View and track your reported maintenance issues.</p>
                </div>
                <div class="student-user-info">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--accent-color); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; color: white;">
                        <?php echo $user_initials; ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                        <span>Student</span>
                    </div>
                </div>
            </div>

            <?php if (empty($my_issues)): ?>
                <div style="background: var(--glass-bg); padding: 3rem; border-radius: 12px; border: 1px solid var(--glass-border); text-align: center; margin-top: 2rem;">
                    <i class="fas fa-clipboard-list" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.5rem;">No Issues Reported Yet</h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">You haven't reported any maintenance issues yet.</p>
                    <a href="report-issue.php" style="display: inline-block; padding: 0.75rem 1.5rem; background: var(--accent-color); color: white; text-decoration: none; border-radius: 8px; font-weight: 500;">Report Your First Issue</a>
                </div>
            <?php else: ?>
                <div style="background: var(--glass-bg); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border); overflow-x: auto; margin-top: 2rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-muted);">Issue</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-muted);">Location</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-muted);">Status</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-muted);">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_issues as $issue): ?>
                                <tr style="border-bottom: 1px solid var(--glass-border);">
                                    <td style="padding: 1rem;">
                                        <div style="font-weight: 500; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($issue['issue']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($issue['description'] ?? 'No description'); ?></div>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <div><?php echo htmlspecialchars($issue['residence']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--text-muted);">Block <?php echo htmlspecialchars($issue['block']); ?>, Room <?php echo htmlspecialchars($issue['room']); ?></div>
                                    </td>
                                    <td style="padding: 1rem;"><?php echo getStatusBadge($issue['status']); ?></td>
                                    <td style="padding: 1rem; color: var(--text-muted); font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($issue['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
    function applyTheme(isLight) {
        if (isLight) {
            document.body.classList.add('student-light-mode');
        } else {
            document.body.classList.remove('student-light-mode');
        }
    }

    (function loadTheme() {
        const saved = localStorage.getItem('studentTheme');
        const isLight = saved === null ? true : saved === 'light';
        applyTheme(isLight);
    })();
    </script>
</body>
</html>
