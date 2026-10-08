<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

require_login('login.php');
require_role(ROLE_ADMIN, 'login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_ADMIN);

if (!$user) {
    header('Location: login.php');
    exit;
}

$students = json_read(STUDENTS_FILE);
$staff = json_read(STAFF_FILE);
$issues = json_read(MAINTENANCE_REQUESTS_FILE);
$activities = json_read(ACTIVITIES_FILE);

$student_count = count($students);
$staff_count = count($staff);
$issue_count = count($issues);
$pending_issues = count(array_filter($issues, fn($i) => ($i['status'] ?? 'pending') === 'pending'));
$completed_issues = count(array_filter($issues, fn($i) => ($i['status'] ?? 'pending') === 'completed'));

$display_date = date('d/m/Y');
$display_name = !empty($user['name']) ? $user['name'] : 'System Administrator';
$display_email = $user['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MainRes Maintenance</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-dashboard" style="padding-top: 0;">
        <aside class="admin-sidebar" style="top: 0; height: 100vh;">
            <div class="admin-sidebar-logo">
                <img src="../assets/images/logo.png" alt="VUT Logo">
                <div>
                    <div>VUT MainRes</div>
                    <div>Admin Panel</div>
                </div>
            </div>
            <ul class="admin-nav">
                <li><a href="../index.php"><i class="fas fa-globe"></i> Website</a></li>
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="maintenance-staff.php"><i class="fas fa-tools"></i> Maintenance Staff</a></li>
                <li><a href="issues.php"><i class="fas fa-clipboard-list"></i> Issues</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li>
                    <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="admin-header">
                <div class="admin-header-main">
                    <h1>Dashboard</h1>
                    <div class="admin-header-sub">
                        <i class="fa-regular fa-calendar"></i>
                        <span><?php echo htmlspecialchars($display_date); ?></span>
                    </div>
                </div>
                <div class="admin-header-user">
                    <div class="admin-header-avatar" title="<?php echo htmlspecialchars($display_name); ?>">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="admin-header-user-details">
                        <div class="admin-header-user-name"><?php echo htmlspecialchars($display_name); ?></div>
                        <?php if (!empty($display_email)): ?>
                            <div class="admin-header-user-email"><?php echo htmlspecialchars($display_email); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card" style="--card-accent: #3b82f6;">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $student_count; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>

                <div class="stat-card" style="--card-accent: #8b5cf6;">
                    <div class="stat-icon staff">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $staff_count; ?></h3>
                        <p>Maintenance Staff</p>
                    </div>
                </div>

                <div class="stat-card" style="--card-accent: #f59e0b;">
                    <div class="stat-icon issues">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $issue_count; ?></h3>
                        <p>Total Issues</p>
                    </div>
                </div>

                <div class="stat-card" style="--card-accent: #ef4444;">
                    <div class="stat-icon pending">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_issues; ?></h3>
                        <p>Pending Issues</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="maintenance-staff.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Staff</span>
                    </a>
                    <a href="issues.php" class="action-btn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>View Issues</span>
                    </a>
                    <a href="issues.php?filter=pending" class="action-btn">
                        <i class="fas fa-clock"></i>
                        <span>Pending Tasks</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
    <script>
    (function applyAdminTheme(){
        const saved = localStorage.getItem('adminTheme');
        if (saved !== 'light') return;
        const root = document.documentElement;
        root.style.setProperty('--admin-bg-primary', '#f5f7fb');
        root.style.setProperty('--admin-bg-secondary', '#ffffff');
        root.style.setProperty('--admin-bg-tertiary', '#eef2f7');
        root.style.setProperty('--admin-sidebar-bg', 'rgba(255, 255, 255, 0.92)');
        root.style.setProperty('--admin-topbar-bg', 'rgba(255, 255, 255, 0.95)');
        root.style.setProperty('--admin-glass-bg', 'rgba(255, 255, 255, 0.8)');
        root.style.setProperty('--admin-glass-border', 'rgba(15, 23, 42, 0.1)');
        root.style.setProperty('--admin-glass-highlight', 'rgba(15, 23, 42, 0.04)');
        root.style.setProperty('--admin-text-primary', '#0f172a');
        root.style.setProperty('--admin-text-secondary', '#334155');
        root.style.setProperty('--admin-text-muted', '#64748b');
        root.style.setProperty('--admin-text-dim', '#94a3b8');
        document.body.classList.add('admin-light-mode');
    })();
    </script>
</body>
</html>
