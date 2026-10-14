<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

require_login('login.php');
require_role(ROLE_ADMIN, 'login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_ADMIN);

if (!$user) {
    header('Location: ../student/login.php');
    exit;
}

$students = json_read(STUDENTS_FILE);
$staff = json_read(STAFF_FILE);
$staff_lookup = get_staff_lookup();
$issues = get_all_issues();
$stats = get_issue_stats();
$activities = json_read(ACTIVITIES_FILE);
$settings = json_read(SETTINGS_FILE);

$student_count = count($students);
$staff_count = count($staff);
$issue_count = $stats['total'];
$pending_issues = $stats['pending'];
$completed_issues = $stats['completed'];

$display_date = date('d/m/Y');
$display_time = date('H:i');
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
                    <a href="#" onclick="window.location.href='../student/login.php?action=logout';"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-header-info">
                    <div class="admin-header-date" title="Today's date">
                        <i class="fa-regular fa-calendar"></i>
                        <span><?php echo htmlspecialchars($display_date); ?> <?php echo htmlspecialchars($display_time); ?></span>
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
                        <h3 data-stat="total"><?php echo $issue_count; ?></h3>
                        <p>Total Issues</p>
                    </div>
                </div>

                <div class="stat-card" style="--card-accent: #ef4444;">
                    <div class="stat-icon pending">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 data-stat="pending"><?php echo $pending_issues; ?></h3>
                        <p>Pending Issues</p>
                    </div>
                </div>
            </div>

            <!-- Recent Issues Overview -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; margin-bottom: 1rem;">
                <h2 style="margin: 0; font-size: 1.35rem; font-weight: 700;">Recent Maintenance Issues</h2>
                <a href="issues.php" style="color: var(--admin-accent-cyan, #06b6d4); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem;">
                    View All Issues <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>

            <?php if (empty($issues)): ?>
                <div class="empty-state" style="background: var(--admin-glass-bg); padding: 3rem; border-radius: 12px; border: 1px solid var(--admin-glass-border); text-align: center; margin-bottom: 2rem;">
                    <i class="fas fa-clipboard-check" style="font-size: 3rem; color: var(--admin-text-muted); margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h3 style="margin: 0 0 0.5rem 0;">No Issues Reported</h3>
                    <p style="color: var(--admin-text-muted); margin: 0;">There are currently no maintenance issues in the system.</p>
                </div>
            <?php else: ?>
                <div class="table-container" style="margin-bottom: 2rem;">
                    <table class="issues-table">
                        <thead>
                            <tr>
                                <th>Issue</th>
                                <th>Student</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="recent-issues-body" data-sync-container="issues">
                            <?php 
                            $recent_slice = array_slice($issues, 0, 5);
                            foreach ($recent_slice as $issue): 
                            ?>
                            <tr data-issue-id="<?php echo htmlspecialchars($issue['id']); ?>">
                                <td>
                                    <div style="font-weight: 600; color: var(--admin-text-primary);"><?php echo htmlspecialchars($issue['issue']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 3px;"><?php echo date('M d, Y', strtotime($issue['created_at'] ?? 'now')); ?></div>
                                    <div data-field="notes">
                                        <?php if (!empty($issue['maintenance_notes'])): ?>
                                            <div style="background: rgba(34, 197, 94, 0.1); border-left: 3px solid #22c55e; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; color: #86efac; margin-top: 0.35rem;">
                                                <strong>Resolution:</strong> <?php echo htmlspecialchars($issue['maintenance_notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($issue['student_name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 3px;"><?php echo htmlspecialchars($issue['student_no'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($issue['residence']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 3px;">Block <?php echo htmlspecialchars($issue['block']); ?>, Room <?php echo htmlspecialchars($issue['room']); ?></div>
                                </td>
                                <td data-field="status">
                                    <span class="status-badge status-<?php echo htmlspecialchars($issue['status'] ?? 'pending'); ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $issue['status'] ?? 'pending'))); ?>
                                    </span>
                                </td>
                                <td data-field="assigned_to">
                                    <?php if (isset($issue['assigned_to']) && isset($staff_lookup[$issue['assigned_to']])): ?>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, var(--admin-accent-cyan), var(--admin-accent-blue)); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; color: #fff;">
                                                <?php echo strtoupper(substr($staff_lookup[$issue['assigned_to']]['name'], 0, 1)); ?>
                                            </div>
                                            <span style="font-size: 0.85rem;"><?php echo htmlspecialchars($staff_lookup[$issue['assigned_to']]['name']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--admin-text-dim); font-size: 0.85rem;">— Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="issues.php" class="action-btn-small primary" style="text-decoration: none;">Manage</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

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

    <!-- Live Unified Polling & Real-time Sync -->
    <script src="../assets/js/issue-sync.js"></script>
    <script src="../assets/js/translations.js"></script>
    <script>
    function applyTheme(isLight) {
        const root = document.documentElement;
        const body = document.body;
        
        if (isLight) {
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
            body.classList.add('admin-light-mode');
        } else {
            root.style.removeProperty('--admin-bg-primary');
            root.style.removeProperty('--admin-bg-secondary');
            root.style.removeProperty('--admin-bg-tertiary');
            root.style.removeProperty('--admin-sidebar-bg');
            root.style.removeProperty('--admin-topbar-bg');
            root.style.removeProperty('--admin-glass-bg');
            root.style.removeProperty('--admin-glass-border');
            root.style.removeProperty('--admin-glass-highlight');
            root.style.removeProperty('--admin-text-primary');
            root.style.removeProperty('--admin-text-secondary');
            root.style.removeProperty('--admin-text-muted');
            root.style.removeProperty('--admin-text-dim');
            body.classList.remove('admin-light-mode');
        }
    }

    (function loadTheme() {
        const serverTheme = <?php echo json_encode($settings['appearance']['theme'] ?? 'dark'); ?>;
        const isLight = serverTheme === 'light';
        localStorage.setItem('adminTheme', serverTheme);
        applyTheme(isLight);
    })();
    </script>
</body>
</html>
