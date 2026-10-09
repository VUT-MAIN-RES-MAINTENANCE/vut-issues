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

$error = '';
$success = '';

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
            log_activity($user_id, ROLE_ADMIN, 'assign_issue', "Assigned issue $issue_id to staff $staff_id", $issue_id);
        } else {
            $error = 'Failed to assign issue. Please try again.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $issue_id = $_POST['issue_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (empty($issue_id) || empty($status)) {
        $error = 'Issue ID and Status are required.';
    } else {
        $updates = [
            'status' => htmlspecialchars($status),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (json_update(MAINTENANCE_REQUESTS_FILE, $issue_id, $updates)) {
            $success = 'Issue status updated successfully!';
            log_activity($user_id, ROLE_ADMIN, 'update_status', "Updated issue $issue_id status to $status", $issue_id);
        } else {
            $error = 'Failed to update status. Please try again.';
        }
    }
}

$filter = $_GET['filter'] ?? 'all';

$issues = json_read(MAINTENANCE_REQUESTS_FILE);
$staff = json_read(STAFF_FILE);

usort($issues, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

if ($filter !== 'all') {
    $issues = array_filter($issues, function($issue) use ($filter) {
        return ($issue['status'] ?? 'pending') === $filter;
    });
}

$staff_lookup = [];
foreach ($staff as $s) {
    $staff_lookup[$s['id']] = $s['name'];
}

$total_issues = count(json_read(MAINTENANCE_REQUESTS_FILE));
$pending_count = count(array_filter(json_read(MAINTENANCE_REQUESTS_FILE), fn($i) => ($i['status'] ?? 'pending') === 'pending'));
$in_progress_count = count(array_filter(json_read(MAINTENANCE_REQUESTS_FILE), fn($i) => ($i['status'] ?? 'pending') === 'in_progress'));
$completed_count = count(array_filter(json_read(MAINTENANCE_REQUESTS_FILE), fn($i) => ($i['status'] ?? 'pending') === 'completed'));

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
    <title>Issues - Admin Dashboard</title>
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
                <li><a href="students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="maintenance-staff.php"><i class="fas fa-tools"></i> Maintenance Staff</a></li>
                <li><a href="issues.php" class="active"><i class="fas fa-clipboard-list"></i> Issues</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li>
                    <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Maintenance Issues</h1>
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

            <div class="stats-summary">
                <div class="stat-item">
                    <i class="fas fa-clipboard-list" style="color: #3b82f6;"></i>
                    <div class="count"><?php echo $total_issues; ?></div>
                    <div class="label">Total Issues</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock" style="color: #f59e0b;"></i>
                    <div class="count"><?php echo $pending_count; ?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-spinner fa-spin" style="color: #a78bfa;"></i>
                    <div class="count"><?php echo $in_progress_count; ?></div>
                    <div class="label">In Progress</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-check-circle" style="color: #22c55e;"></i>
                    <div class="count"><?php echo $completed_count; ?></div>
                    <div class="label">Fixed</div>
                </div>
            </div>

            <div class="filter-tabs">
                <a href="issues.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="issues.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="issues.php?filter=assigned" class="filter-tab <?php echo $filter === 'assigned' ? 'active' : ''; ?>">Assigned</a>
                <a href="issues.php?filter=in_progress" class="filter-tab <?php echo $filter === 'in_progress' ? 'active' : ''; ?>">In Progress</a>
                <a href="issues.php?filter=completed" class="filter-tab <?php echo $filter === 'completed' ? 'active' : ''; ?>">Fixed</a>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (empty($issues)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Issues Found</h3>
                    <p>No maintenance issues match the current filter.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="issues-table">
                        <thead>
                            <tr>
                                <th>Issue</th>
                                <th>Student</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($issues as $issue): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--admin-text-primary);"><?php echo htmlspecialchars($issue['issue']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 3px;"><?php echo date('M d, Y', strtotime($issue['created_at'] ?? 'now')); ?></div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($issue['student_name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 3px;"><?php echo htmlspecialchars($issue['email'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($issue['residence']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 3px;">Block <?php echo htmlspecialchars($issue['block']); ?>, Room <?php echo htmlspecialchars($issue['room']); ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($issue['status'] ?? 'pending'); ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $issue['status'] ?? 'pending'))); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($issue['assigned_to']) && isset($staff_lookup[$issue['assigned_to']])): ?>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, var(--admin-accent-cyan), var(--admin-accent-blue)); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; color: #fff;">
                                                <?php echo strtoupper(substr($staff_lookup[$issue['assigned_to']], 0, 1)); ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($staff_lookup[$issue['assigned_to']]); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--admin-text-dim); font-size: 0.875rem;">— Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($issue['assigned_to']) && !empty($staff)): ?>
                                        <form method="POST" action="" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <input type="hidden" name="action" value="assign">
                                            <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['id']); ?>">
                                            <select name="staff_id">
                                                <option value="">Select Staff</option>
                                                <?php foreach ($staff as $s): ?>
                                                    <option value="<?php echo htmlspecialchars($s['id']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="action-btn-small primary"><i class="fas fa-user-check"></i> Assign</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['id']); ?>">
                                            <select name="status">
                                                <option value="pending" <?php echo ($issue['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="assigned" <?php echo ($issue['status'] ?? '') === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                                <option value="in_progress" <?php echo ($issue['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="completed" <?php echo ($issue['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Fixed</option>
                                            </select>
                                            <button type="submit" class="action-btn-small primary"><i class="fas fa-sync"></i> Update</button>
                                        </form>
                                    <?php endif; ?>
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
