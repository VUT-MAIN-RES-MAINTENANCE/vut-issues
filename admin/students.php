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

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $student_id = $_GET['id'];
    if (json_delete(STUDENTS_FILE, $student_id)) {
        log_activity($user_id, ROLE_ADMIN, 'delete_student', "Deleted student: $student_id", $student_id);
        header('Location: students.php');
        exit;
    }
}

$students = json_read(STUDENTS_FILE);

usort($students, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

$display_date = date('d/m/Y');
$display_name = !empty($user['name']) ? $user['name'] : 'System Administrator';
$display_email = $user['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Admin Dashboard</title>
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
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="students.php" class="active"><i class="fas fa-user-graduate"></i> Students</a></li>
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
                    <h1>Registered Students</h1>
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

            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1.75rem;">
                <div class="stat-card" style="--card-accent: #3b82f6;">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($students); ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="stat-card" style="--card-accent: #8b5cf6;">
                    <div class="stat-icon" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa; box-shadow: inset 0 0 0 1px rgba(139, 92, 246, 0.2);">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_unique(array_column($students, 'residence'))); ?></h3>
                        <p>Residences</p>
                    </div>
                </div>
                <div class="stat-card" style="--card-accent: #06b6d4;">
                    <div class="stat-icon" style="background: rgba(6, 182, 212, 0.15); color: #06b6d4; box-shadow: inset 0 0 0 1px rgba(6, 182, 212, 0.2);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($students, fn($s) => strtotime($s['created_at'] ?? 'now') >= strtotime('-30 days'))); ?></h3>
                        <p>Last 30 Days</p>
                    </div>
                </div>
            </div>

            <?php if (empty($students)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <h3>No Students Registered</h3>
                    <p>No students have registered yet.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="services-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Residence</th>
                                <th>Room</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: #fff; flex-shrink: 0;">
                                            <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--admin-text-primary);"><?php echo htmlspecialchars($student['name']); ?></div>
                                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($student['student_number'] ?? 'Student'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="color: var(--admin-accent-cyan);"><?php echo htmlspecialchars($student['email']); ?></span>
                                </td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-building" style="font-size: 0.75rem; color: var(--admin-text-dim);"></i>
                                        <?php echo htmlspecialchars($student['residence'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($student['block']) || !empty($student['room'])): ?>
                                        <span style="font-family: monospace; background: rgba(100, 116, 139, 0.15); padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem;">
                                            B<?php echo htmlspecialchars($student['block'] ?? '-'); ?> · R<?php echo htmlspecialchars($student['room'] ?? '-'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--admin-text-dim);">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo date('M d, Y', strtotime($student['created_at'] ?? 'now')); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo date('H:i', strtotime($student['created_at'] ?? 'now')); ?></div>
                                </td>
                                <td>
                                    <a href="students.php?action=delete&id=<?php echo htmlspecialchars($student['id']); ?>"
                                       onclick="return confirm('Are you sure you want to delete this student?');"
                                       class="btn btn-danger" style="padding: 0.45rem 0.85rem; font-size: 0.825rem;">
                                        <i class="fas fa-trash" style="margin-right: 4px;"></i> Remove
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
