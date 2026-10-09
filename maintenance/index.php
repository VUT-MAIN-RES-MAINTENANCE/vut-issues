<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

// Require login and check role
require_login('login.php');
require_role(ROLE_STAFF, '../index.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_STAFF);

if (!$user) {
    header('Location: login.php');
    exit;
}

// Get assigned issues count
$all_issues = json_read(MAINTENANCE_REQUESTS_FILE);
$assigned_issues = array_filter($all_issues, function($issue) use ($user_id) {
    return isset($issue['assigned_to']) && $issue['assigned_to'] == $user_id;
});
$assigned_count = count($assigned_issues);

// Count issues by status
$pending_count = count(array_filter($assigned_issues, function($issue) {
    return ($issue['status'] ?? '') === STATUS_ASSIGNED;
}));
$in_progress_count = count(array_filter($assigned_issues, function($issue) {
    return ($issue['status'] ?? '') === STATUS_IN_PROGRESS;
}));
$completed_count = count(array_filter($assigned_issues, function($issue) {
    return ($issue['status'] ?? '') === STATUS_COMPLETED;
}));

// Get user initials for avatar
$user_initials = strtoupper(substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Staff Dashboard - MainRes Maintenance</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <button class="sidebar-toggle" onclick="document.querySelector('.maintenance-sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>

    <div class="maintenance-layout">
        <!-- Sidebar -->
        <aside class="maintenance-sidebar">
            <div class="maintenance-sidebar-header">
                <img src="../assets/images/logo.png" alt="VUT Logo">
                <div>
                    <h2>VUT MainRes</h2>
                    <span>Maintenance</span>
                </div>
            </div>

            <nav class="maintenance-sidebar-nav">
                <a href="index.php" class="active">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="assigned-issues.php">
                    <i class="fas fa-clipboard-list"></i>
                    Assigned Issues
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

            <div class="maintenance-sidebar-footer">
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
        <main class="maintenance-main">
            <div class="maintenance-header">
                <div>
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?>. Here's your maintenance overview.</p>
                </div>
                <div class="maintenance-user-info">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--accent-color); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; color: white;">
                        <?php echo $user_initials; ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                        <span>Maintenance Staff</span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
                <div class="stat-card" style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $assigned_count; ?></h3>
                        <div style="background: rgba(59, 130, 246, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-clipboard-list" style="font-size: 1.25rem; color: #3b82f6;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Total Assigned Issues</p>
                </div>

                <div class="stat-card" style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $pending_count; ?></h3>
                        <div style="background: rgba(245, 158, 11, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-clock" style="font-size: 1.25rem; color: #f59e0b;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Pending Awaiting Action</p>
                </div>

                <div class="stat-card" style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $in_progress_count; ?></h3>
                        <div style="background: rgba(139, 92, 246, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-tools" style="font-size: 1.25rem; color: #8b5cf6;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Active In Progress</p>
                </div>

                <div class="stat-card" style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $completed_count; ?></h3>
                        <div style="background: rgba(34, 197, 94, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-check-circle" style="font-size: 1.25rem; color: #22c55e;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Done Completed</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">Quick Actions</h2>
            <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <a href="assigned-issues.php" style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; text-decoration: none; color: white; border: 1px solid var(--glass-border); transition: all 0.3s ease; display: block;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="background: rgba(59, 130, 246, 0.15); padding: 1.25rem; border-radius: 12px;">
                            <i class="fas fa-clipboard-list" style="font-size: 1.75rem; color: #3b82f6;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 600;">Assigned Issues</h3>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">View and manage your assigned maintenance issues</p>
                        </div>
                    </div>
                </a>

                <a href="profile.php" style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; text-decoration: none; color: white; border: 1px solid var(--glass-border); transition: all 0.3s ease; display: block;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="background: rgba(139, 92, 246, 0.15); padding: 1.25rem; border-radius: 12px;">
                            <i class="fas fa-user" style="font-size: 1.75rem; color: #8b5cf6;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 600;">My Profile</h3>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">Update your profile information and settings</p>
                        </div>
                    </div>
                </a>
            </div>
        </main>
    </div>

    <script>
    function applyTheme(isLight) {
        if (isLight) {
            document.body.classList.add('maintenance-light-mode');
        } else {
            document.body.classList.remove('maintenance-light-mode');
        }
    }

    (function loadTheme() {
        const saved = localStorage.getItem('maintenanceTheme');
        const isLight = saved === null ? true : saved === 'light';
        applyTheme(isLight);
    })();
    </script>
</body>
</html>
