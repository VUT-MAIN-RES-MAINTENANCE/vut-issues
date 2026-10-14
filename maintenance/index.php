<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

require_login('login.php');
require_role(ROLE_STAFF, 'login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_STAFF);

if (!$user) {
    header('Location: ../student/login.php');
    exit;
}

$settings = json_read(SETTINGS_FILE);

$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['issue_id'])) {
    $issue_id = $_POST['issue_id'];
    $action = $_POST['action'];
    $notes = trim($_POST['maintenance_notes'] ?? '');
    
    $issue = get_issue_by_id($issue_id);
    
    if (!$issue) {
        $error = 'Issue not found.';
    } else {
        $updates = [];
        $desc = '';
        if ($action === 'start_working') {
            $updates['status'] = STATUS_IN_PROGRESS;
            if (empty($issue['assigned_to'])) {
                $updates['assigned_to'] = $user_id;
            }
            $desc = "Started working on issue: " . ($issue['issue'] ?? $issue_id);
        } elseif ($action === 'mark_fixed') {
            $updates['status'] = STATUS_COMPLETED;
            if ($notes !== '') {
                $updates['maintenance_notes'] = $notes;
            }
            $desc = "Marked issue as fixed: " . ($issue['issue'] ?? $issue_id) . ($notes ? " (Notes: $notes)" : "");
        } else {
            $error = 'Invalid action.';
        }
        
        if (!empty($updates)) {
            if (update_maintenance_issue($issue_id, $updates, $user_id, ROLE_STAFF, $action, $desc)) {
                $success = 'Issue status updated successfully!';
            } else {
                $error = 'Failed to update issue status.';
            }
        }
    }
}

// Get all issues and stats via unified domain helpers
$all_issues = get_all_issues();
$stats = get_issue_stats();
$total_count = $stats['total'];
$pending_count = $stats['pending'];
$in_progress_count = $stats['in_progress'];
$completed_count = $stats['completed'];

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
                    <h1>Dashboard</h1>
                    <p>Welcome back! Here's what's happening with maintenance.</p>
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
                        <h3 data-stat="total" style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $total_count; ?></h3>
                        <div style="background: rgba(59, 130, 246, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-clipboard-list" style="font-size: 1.25rem; color: #3b82f6;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Total Issues</p>
                </div>

                <div class="stat-card" style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 data-stat="pending" style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $pending_count; ?></h3>
                        <div style="background: rgba(245, 158, 11, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-clock" style="font-size: 1.25rem; color: #f59e0b;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Pending</p>
                </div>

                <div class="stat-card" style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 data-stat="in_progress" style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $in_progress_count; ?></h3>
                        <div style="background: rgba(139, 92, 246, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-tools" style="font-size: 1.25rem; color: #8b5cf6;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">In Progress</p>
                </div>

                <div class="stat-card" style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 data-stat="completed" style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $completed_count; ?></h3>
                        <div style="background: rgba(34, 197, 94, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-check-circle" style="font-size: 1.25rem; color: #22c55e;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Fixed</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">Quick Actions</h2>
            <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
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

            <!-- Recent Issues -->
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">Maintenance Issues</h2>
            <?php if (empty($all_issues)): ?>
                <div style="background: var(--glass-bg); padding: 4rem 2rem; border-radius: 12px; border: 1px solid var(--glass-border); text-align: center;">
                    <i class="fas fa-inbox" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem; opacity: 0.5;"></i>
                    <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Issues Available</h2>
                    <p style="color: var(--text-muted);">No maintenance issues are currently available.</p>
                </div>
            <?php else: ?>
                <div style="background: var(--glass-bg); border-radius: 12px; border: 1px solid var(--glass-border); overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(79, 143, 192, 0.1);">
                                <th style="padding: 0.6rem 0.5rem; text-align: left; font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Issue ID</th>
                                <th style="padding: 0.6rem 0.5rem; text-align: left; font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Issue & Notes</th>
                                <th style="padding: 0.6rem 0.5rem; text-align: left; font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Student</th>
                                <th style="padding: 0.6rem 0.5rem; text-align: left; font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Location</th>
                                <th style="padding: 0.6rem 0.5rem; text-align: left; font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                                <th style="padding: 0.6rem 0.5rem; text-align: left; font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="issues-table-body" data-sync-container="issues">
                            <?php foreach ($all_issues as $index => $issue): ?>
                            <tr data-issue-id="<?php echo htmlspecialchars($issue['id']); ?>" style="border-top: 1px solid var(--glass-border); transition: background 0.2s ease;">
                                <td style="padding: 0.6rem 0.5rem; font-family: monospace; font-size: 0.75rem;"><?php echo htmlspecialchars(substr($issue['id'], -8)); ?></td>
                                <td style="padding: 0.6rem 0.5rem;">
                                    <div style="font-weight: 600; font-size: 0.85rem;"><?php echo htmlspecialchars($issue['issue']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($issue['description'] ?? ''); ?></div>
                                    <div data-field="notes">
                                        <?php if (!empty($issue['maintenance_notes'])): ?>
                                            <div style="background: rgba(34, 197, 94, 0.1); border-left: 3px solid #22c55e; padding: 0.25rem 0.45rem; border-radius: 4px; font-size: 0.75rem; color: #86efac; margin-top: 0.25rem;">
                                                <strong>Resolution:</strong> <?php echo htmlspecialchars($issue['maintenance_notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding: 0.6rem 0.5rem; font-size: 0.8rem;">
                                    <div><?php echo htmlspecialchars($issue['student_name']); ?></div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);"><?php echo htmlspecialchars($issue['student_no'] ?? ''); ?></div>
                                </td>
                                <td style="padding: 0.6rem 0.5rem; font-size: 0.8rem;">
                                    <div><?php echo htmlspecialchars($issue['residence']); ?></div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">Blk <?php echo htmlspecialchars($issue['block']); ?>, Rm <?php echo htmlspecialchars($issue['room']); ?></div>
                                </td>
                                <td style="padding: 0.6rem 0.5rem;" data-field="status">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 500; white-space: nowrap; min-width: 90px;
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
                                <td style="padding: 0.6rem 0.5rem;" data-field="actions">
                                    <div style="display: flex; gap: 0.25rem; align-items: center;">
                                        <a href="issue-details.php?id=<?php echo htmlspecialchars($issue['id']); ?>" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; text-decoration: none; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.7rem; font-weight: 500; transition: all 0.2s ease; white-space: nowrap;">
                                            View <i class="fas fa-arrow-right" style="font-size: 0.55rem;"></i>
                                        </a>
                                        <?php if ($issue['status'] === STATUS_PENDING): ?>
                                            <button type="button" class="btn-start-working" data-id="<?php echo htmlspecialchars($issue['id']); ?>" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 4px; font-size: 0.7rem; font-weight: 500; cursor: pointer; white-space: nowrap;">
                                                <i class="fas fa-play" style="font-size: 0.55rem;"></i> Start Working
                                            </button>
                                        <?php elseif ($issue['status'] === STATUS_IN_PROGRESS): ?>
                                            <button type="button" class="btn-mark-fixed" data-id="<?php echo htmlspecialchars($issue['id']); ?>" data-notes="<?php echo htmlspecialchars($issue['maintenance_notes'] ?? ''); ?>" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.7rem; font-weight: 500; cursor: pointer; white-space: nowrap;">
                                                <i class="fas fa-check" style="font-size: 0.55rem;"></i> Mark as Fixed
                                            </button>
                                        <?php elseif ($issue['status'] === STATUS_COMPLETED): ?>
                                            <span style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 4px; font-size: 0.7rem; font-weight: 500; white-space: nowrap;">
                                                <i class="fas fa-check-circle" style="font-size: 0.55rem;"></i> Fixed
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

    <!-- Live Unified Polling & Real-time Sync -->
    <script src="../assets/js/issue-sync.js"></script>
    <script src="../assets/js/translations.js"></script>

    <script>
    // Theme Toggle Functionality
    (function initTheme() {
        const savedTheme = localStorage.getItem('maintenanceTheme');
        if (savedTheme === 'light') {
            document.body.classList.add('maintenance-light-mode');
        }
    })();
    </script>
</body>
</html>
