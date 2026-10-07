<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

// Require login and check role
require_login('../admin/login.php');
require_role(ROLE_ADMIN, '../index.php');

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
            log_activity($user_id, ROLE_ADMIN, 'assign_issue', "Assigned issue $issue_id to staff $staff_id", $issue_id);
        } else {
            $error = 'Failed to assign issue. Please try again.';
        }
    }
}

// Handle status update
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

// Get filter from URL
$filter = $_GET['filter'] ?? 'all';

// Get all issues and staff
$issues = json_read(MAINTENANCE_REQUESTS_FILE);
$staff = json_read(STAFF_FILE);

// Sort issues by created date (newest first)
usort($issues, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

// Filter issues if needed
if ($filter !== 'all') {
    $issues = array_filter($issues, function($issue) use ($filter) {
        return ($issue['status'] ?? 'pending') === $filter;
    });
}

// Create staff lookup array
$staff_lookup = [];
foreach ($staff as $s) {
    $staff_lookup[$s['id']] = $s['name'];
}

// Get counts
$total_issues = count(json_read(MAINTENANCE_REQUESTS_FILE));
$pending_count = count(array_filter(json_read(MAINTENANCE_REQUESTS_FILE), fn($i) => ($i['status'] ?? 'pending') === 'pending'));
$in_progress_count = count(array_filter(json_read(MAINTENANCE_REQUESTS_FILE), fn($i) => ($i['status'] ?? 'pending') === 'in_progress'));
$completed_count = count(array_filter(json_read(MAINTENANCE_REQUESTS_FILE), fn($i) => ($i['status'] ?? 'pending') === 'completed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues Management - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .admin-dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background: linear-gradient(135deg, #1e3a5f 0%, #26648E 100%);
            padding: 2rem 1rem;
            color: white;
        }
        
        .admin-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .admin-sidebar-logo img {
            height: 40px;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.5rem;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-nav a i {
            width: 20px;
            text-align: center;
        }
        
        .admin-content {
            padding: 2rem;
            background: #f5f7fa;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .admin-header h1 {
            color: #1e3a5f;
            font-size: 2rem;
        }
        
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .filter-tab {
            padding: 0.5rem 1rem;
            border: none;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            color: #666;
            text-decoration: none;
        }
        
        .filter-tab:hover, .filter-tab.active {
            background: #26648E;
            color: white;
        }
        
        .issues-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .issues-table thead {
            background: #1e3a5f;
            color: white;
        }
        
        .issues-table th, .issues-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .issues-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-assigned { background: #dbeafe; color: #1e40af; }
        .status-in_progress { background: #e0e7ff; color: #3730a3; }
        .status-completed { background: #d1fae5; color: #065f46; }
        
        .action-btn {
            padding: 0.375rem 0.75rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s;
        }
        
        .action-btn-primary {
            background: #26648E;
            color: white;
        }
        
        .action-btn-primary:hover {
            background: #1e3a5f;
        }
        
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-item .count {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3a5f;
        }
        
        .stat-item .label {
            font-size: 0.875rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <img src="../assets/images/logo.png" alt="VUT Logo">
                <div>
                    <div style="font-weight: 800;">VUT MainRes</div>
                    <div style="font-size: 0.8rem; opacity: 0.8;">Admin Panel</div>
                </div>
            </div>
            <ul class="admin-nav">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="maintenance-staff.php"><i class="fas fa-tools"></i> Maintenance Staff</a></li>
                <li><a href="issues.php" class="active"><i class="fas fa-clipboard-list"></i> Issues</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1rem;">
                    <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Student Reports</h1>
                <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            
            <div class="stats-summary">
                <div class="stat-item">
                    <div class="count"><?php echo $total_issues; ?></div>
                    <div class="label">Total Issues</div>
                </div>
                <div class="stat-item">
                    <div class="count"><?php echo $pending_count; ?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="stat-item">
                    <div class="count"><?php echo $in_progress_count; ?></div>
                    <div class="label">In Progress</div>
                </div>
                <div class="stat-item">
                    <div class="count"><?php echo $completed_count; ?></div>
                    <div class="label">Fixed (Completed)</div>
                </div>
            </div>
            
            <div class="filter-tabs">
                <a href="issues.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="issues.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="issues.php?filter=in_progress" class="filter-tab <?php echo $filter === 'in_progress' ? 'active' : ''; ?>">In Progress</a>
                <a href="issues.php?filter=completed" class="filter-tab <?php echo $filter === 'completed' ? 'active' : ''; ?>">Fixed</a>
            </div>
            
            <?php if ($error): ?>
                <div style="color: #ef4444; margin-bottom: 1rem; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="color: #22c55e; margin-bottom: 1rem; padding: 1rem; background: rgba(34, 197, 94, 0.1); border-radius: 8px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($issues)): ?>
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
                    <i class="fas fa-clipboard-list" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3 style="color: #1e3a5f; margin-bottom: 0.5rem;">No Issues Found</h3>
                    <p style="color: #666;">No maintenance issues match the current filter.</p>
                </div>
            <?php else: ?>
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
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($issue['issue']); ?></div>
                                <div style="font-size: 0.85rem; color: #666;"><?php echo date('M d, Y', strtotime($issue['created_at'] ?? 'now')); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($issue['student_name']); ?></div>
                                <div style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($issue['email'] ?? ''); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($issue['residence']); ?></div>
                                <div style="font-size: 0.85rem; color: #666;">Block <?php echo htmlspecialchars($issue['block']); ?>, Room <?php echo htmlspecialchars($issue['room']); ?></div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($issue['status'] ?? 'pending'); ?>">
                                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $issue['status'] ?? 'pending'))); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isset($issue['assigned_to']) && isset($staff_lookup[$issue['assigned_to']])): ?>
                                    <div><?php echo htmlspecialchars($staff_lookup[$issue['assigned_to']]); ?></div>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($issue['assigned_to']) && !empty($staff)): ?>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="assign">
                                        <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['id']); ?>">
                                        <select name="staff_id" style="padding: 0.375rem; border-radius: 6px; border: 1px solid #d1d5db; margin-right: 0.5rem;">
                                            <option value="">Select Staff</option>
                                            <?php foreach ($staff as $s): ?>
                                                <option value="<?php echo htmlspecialchars($s['id']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="action-btn action-btn-primary">Assign</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['id']); ?>">
                                        <select name="status" style="padding: 0.375rem; border-radius: 6px; border: 1px solid #d1d5db;">
                                            <option value="pending" <?php echo ($issue['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo ($issue['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo ($issue['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Fixed</option>
                                        </select>
                                        <button type="submit" class="action-btn action-btn-primary" style="margin-left: 0.5rem;">Update</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
