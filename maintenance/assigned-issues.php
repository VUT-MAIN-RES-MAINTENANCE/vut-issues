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

$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['issue_id'])) {
    $issue_id = $_POST['issue_id'];
    $action = $_POST['action'];
    
    $issue = json_find_by_id(MAINTENANCE_REQUESTS_FILE, $issue_id);
    
    if (!$issue) {
        $error = 'Issue not found.';
    } else {
        $new_status = '';
        if ($action === 'start_working') {
            $new_status = STATUS_IN_PROGRESS;
        } elseif ($action === 'mark_fixed') {
            $new_status = STATUS_COMPLETED;
        } else {
            $error = 'Invalid action.';
        }
        
        if ($new_status) {
            $updates = [
                'status' => $new_status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (json_update(MAINTENANCE_REQUESTS_FILE, $issue_id, $updates)) {
                $success = 'Issue status updated successfully!';
                log_activity($user_id, ROLE_STAFF, 'update_status', "Updated issue status to: $new_status", $issue_id);
            } else {
                $error = 'Failed to update issue status.';
            }
        }
    }
}

// Get filter from URL
$status_filter = $_GET['status'] ?? 'all';

// Get all issues - show all reported issues
$all_issues = json_read(MAINTENANCE_REQUESTS_FILE);

// Filter by status if specified
if ($status_filter !== 'all') {
    $all_issues = array_filter($all_issues, function($issue) use ($status_filter) {
        return ($issue['status'] ?? '') === $status_filter;
    });
    $all_issues = array_values($all_issues);
}

// Sort by created date (newest first)
usort($all_issues, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

// Get user initials for avatar
$user_initials = strtoupper(substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Assigned Issues - Maintenance Staff - MainRes Maintenance">
    <meta name="keywords" content="VUT, Maintenance Staff, Assigned Issues">
    <title>Assigned Issues - MainRes Maintenance</title>
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
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="assigned-issues.php" class="active">
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
                <a href="#" onclick="window.location.href='../student/login.php?action=logout';">
                    <i class="fas fa-sign-out-alt"></i>
                    Log Out
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="maintenance-main">
            <div class="maintenance-header">
                <div>
                    <h1>All Issues</h1>
                    <p>View and manage all reported maintenance issues.</p>
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

            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: #ef4444;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: #22c55e;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Status Filters -->
            <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <a href="assigned-issues.php?status=all" style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease; <?php echo $status_filter === 'all' ? 'background: var(--accent-color); color: white;' : 'background: var(--glass-bg); color: var(--text-muted); border: 1px solid var(--glass-border);'; ?>">
                    All Issues
                </a>
                <a href="assigned-issues.php?status=<?php echo STATUS_PENDING; ?>" style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease; <?php echo $status_filter === STATUS_PENDING ? 'background: var(--accent-color); color: white;' : 'background: var(--glass-bg); color: var(--text-muted); border: 1px solid var(--glass-border);'; ?>">
                    Pending
                </a>
                <a href="assigned-issues.php?status=<?php echo STATUS_IN_PROGRESS; ?>" style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease; <?php echo $status_filter === STATUS_IN_PROGRESS ? 'background: var(--accent-color); color: white;' : 'background: var(--glass-bg); color: var(--text-muted); border: 1px solid var(--glass-border);'; ?>">
                    In Progress
                </a>
                <a href="assigned-issues.php?status=<?php echo STATUS_COMPLETED; ?>" style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease; <?php echo $status_filter === STATUS_COMPLETED ? 'background: var(--accent-color); color: white;' : 'background: var(--glass-bg); color: var(--text-muted); border: 1px solid var(--glass-border);'; ?>">
                    Fixed
                </a>
            </div>

            <?php if (empty($all_issues)): ?>
                <div style="background: var(--glass-bg); padding: 4rem 2rem; border-radius: 12px; border: 1px solid var(--glass-border); text-align: center;">
                    <i class="fas fa-inbox" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem; opacity: 0.5;"></i>
                    <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Issues Reported</h2>
                    <p style="color: var(--text-muted);">No maintenance issues have been reported yet.</p>
                </div>
            <?php else: ?>
                <div style="background: var(--glass-bg); border-radius: 12px; border: 1px solid var(--glass-border); overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(79, 143, 192, 0.1);">
                                <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Issue ID</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Issue Type</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Student</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Residence</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Block</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Room</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                                <th style="padding: 1rem; text-align: left; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_issues as $index => $issue): ?>
                            <tr style="border-top: 1px solid var(--glass-border); transition: background 0.2s ease;">
                                <td style="padding: 1rem; font-family: monospace; font-size: 0.9rem;"><?php echo htmlspecialchars(substr($issue['id'], -8)); ?></td>
                                <td style="padding: 1rem; font-weight: 500;"><?php echo htmlspecialchars($issue['issue']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($issue['student_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($issue['residence']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($issue['block']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($issue['room']); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500; 
                                        <?php
                                        $status_colors = [
                                            STATUS_PENDING => 'background: rgba(245, 158, 11, 0.2); color: #f59e0b;',
                                            STATUS_ASSIGNED => 'background: rgba(59, 130, 246, 0.2); color: #3b82f6;',
                                            STATUS_IN_PROGRESS => 'background: rgba(139, 92, 246, 0.2); color: #8b5cf6;',
                                            STATUS_COMPLETED => 'background: rgba(34, 197, 94, 0.2); color: #22c55e;'
                                        ];
                                        echo $status_colors[$issue['status']] ?? 'background: rgba(107, 114, 128, 0.2); color: #6b7280;';
                                        ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $issue['status']))); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                                        <a href="issue-details.php?id=<?php echo htmlspecialchars($issue['id']); ?>" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; text-decoration: none; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.75rem; font-weight: 500; transition: all 0.2s ease; min-width: 95px; white-space: nowrap;">
                                            View Details <i class="fas fa-arrow-right" style="font-size: 0.65rem;"></i>
                                        </a>
                                        <?php if ($issue['status'] === STATUS_PENDING): ?>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="start_working">
                                                <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['id']); ?>">
                                                <button type="submit" onclick="return confirm('Start working on this issue?')" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; min-width: 95px; white-space: nowrap;">
                                                    <i class="fas fa-play" style="font-size: 0.65rem;"></i> Start Working
                                                </button>
                                            </form>
                                        <?php elseif ($issue['status'] === STATUS_IN_PROGRESS): ?>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="mark_fixed">
                                                <input type="hidden" name="issue_id" value="<?php echo htmlspecialchars($issue['id']); ?>">
                                                <button type="submit" onclick="return confirm('Mark this issue as fixed?')" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; min-width: 95px; white-space: nowrap;">
                                                    <i class="fas fa-check" style="font-size: 0.65rem;"></i> Mark as Fixed
                                                </button>
                                            </form>
                                        <?php elseif ($issue['status'] === STATUS_COMPLETED): ?>
                                            <span style="display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.75rem; font-weight: 500; min-width: 95px; white-space: nowrap;">
                                                <i class="fas fa-check-circle" style="font-size: 0.65rem;"></i> Fixed
                                            </span>
                                        <?php endif; ?>
                                    </div>
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
