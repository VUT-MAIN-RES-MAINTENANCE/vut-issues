<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

require_login('login.php');
require_role(ROLE_STUDENT, 'login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_STUDENT);

if (!$user) {
    header('Location: login.php');
    exit;
}

$settings = json_read(SETTINGS_FILE);

// Get student issues & stats using unified domain helpers
$my_issues = get_student_issues($user_id);
$stats = get_issue_stats($user_id);
$staff_lookup = get_staff_lookup();

$total_issues = $stats['total'];
$pending_issues = $stats['pending'];
$in_progress_issues = $stats['in_progress'];
$completed_issues = $stats['completed'];

// Get user initials for avatar
$user_initials = strtoupper(substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Interface</title>
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
                <a href="index.php" class="active">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="my-issues.php">
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
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?>. Here's your maintenance overview.</p>
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

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-top: 2rem; margin-bottom: 2.5rem;">
                <div style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 data-stat="total" style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $total_issues; ?></h3>
                        <div style="background: rgba(59, 130, 246, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-clipboard-list" style="font-size: 1.25rem; color: #3b82f6;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Total Reported Issues</p>
                </div>

                <div style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 data-stat="pending" style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $pending_issues; ?></h3>
                        <div style="background: rgba(245, 158, 11, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-clock" style="font-size: 1.25rem; color: #f59e0b;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Pending Awaiting Action</p>
                </div>

                <div style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 data-stat="in_progress" style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $in_progress_issues; ?></h3>
                        <div style="background: rgba(139, 92, 246, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-tools" style="font-size: 1.25rem; color: #8b5cf6;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Active In Progress</p>
                </div>

                <div style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <h3 data-stat="completed" style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-main);"><?php echo $completed_issues; ?></h3>
                        <div style="background: rgba(34, 197, 94, 0.15); padding: 0.5rem; border-radius: 8px;">
                            <i class="fas fa-check-circle" style="font-size: 1.25rem; color: #22c55e;"></i>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Done Completed</p>
                </div>
            </div>

            <!-- Recent Issues Overview -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Recent Reported Issues</h2>
                <a href="my-issues.php" style="color: var(--accent-color, #3b82f6); text-decoration: none; font-size: 0.9rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.35rem;">
                    View All Issues <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>

            <?php if (empty($my_issues)): ?>
                <div style="background: var(--glass-bg); padding: 2.5rem; border-radius: 12px; border: 1px solid var(--glass-border); text-align: center; margin-bottom: 2.5rem;">
                    <i class="fas fa-clipboard-check" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 0.75rem; opacity: 0.6;"></i>
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1.15rem;">No Issues Reported Yet</h3>
                    <p style="color: var(--text-muted); margin: 0 0 1rem 0; font-size: 0.9rem;">You haven't submitted any maintenance requests yet.</p>
                    <a href="report-issue.php" style="display: inline-block; padding: 0.6rem 1.2rem; background: var(--accent-color, #3b82f6); color: white; text-decoration: none; border-radius: 8px; font-size: 0.85rem; font-weight: 500;">Report An Issue</a>
                </div>
            <?php else: ?>
                <div style="background: var(--glass-bg); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--glass-border); overflow-x: auto; margin-bottom: 2.5rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">Issue</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">Location</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">Assigned Staff</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">Status</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">Reported</th>
                            </tr>
                        </thead>
                        <tbody id="recent-issues-body" data-sync-container="issues">
                            <?php 
                            $recent_slice = array_slice($my_issues, 0, 5);
                            foreach ($recent_slice as $issue): 
                                $status_labels = [
                                    STATUS_PENDING => ['Pending', 'background: rgba(245, 158, 11, 0.2); color: #f59e0b;'],
                                    STATUS_ASSIGNED => ['Assigned', 'background: rgba(59, 130, 246, 0.2); color: #3b82f6;'],
                                    STATUS_IN_PROGRESS => ['In Progress', 'background: rgba(139, 92, 246, 0.2); color: #8b5cf6;'],
                                    STATUS_COMPLETED => ['Fixed', 'background: rgba(34, 197, 94, 0.2); color: #22c55e;']
                                ];
                                $st_info = $status_labels[$issue['status']] ?? [ucfirst($issue['status']), 'background: rgba(107, 114, 128, 0.2); color: #6b7280;'];
                            ?>
                                <tr data-issue-id="<?php echo htmlspecialchars($issue['id']); ?>" style="border-bottom: 1px solid var(--glass-border);">
                                    <td style="padding: 0.75rem 1rem;">
                                        <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($issue['issue']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($issue['description'] ?? ''); ?></div>
                                        <div data-field="notes">
                                            <?php if (!empty($issue['maintenance_notes'])): ?>
                                                <div style="background: rgba(34, 197, 94, 0.1); border-left: 3px solid #22c55e; padding: 0.3rem 0.5rem; border-radius: 4px; font-size: 0.75rem; color: #86efac; margin-top: 0.3rem;">
                                                    <strong>Resolution:</strong> <?php echo htmlspecialchars($issue['maintenance_notes']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; font-size: 0.85rem;">
                                        <div><?php echo htmlspecialchars($issue['residence']); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">Block <?php echo htmlspecialchars($issue['block']); ?>, Room <?php echo htmlspecialchars($issue['room']); ?></div>
                                    </td>
                                    <td style="padding: 0.75rem 1rem;" data-field="assigned_to">
                                        <?php if (!empty($issue['assigned_to']) && isset($staff_lookup[$issue['assigned_to']])): ?>
                                            <span style="color: #60a5fa; font-size: 0.85rem;"><i class="fas fa-user-cog" style="font-size: 0.75rem;"></i> <?php echo htmlspecialchars($staff_lookup[$issue['assigned_to']]['name']); ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.85rem;">— Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem 1rem;" data-field="status">
                                        <span style="display: inline-flex; align-items: center; justify-content: center; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; <?php echo $st_info[1]; ?>">
                                            <?php echo $st_info[0]; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; color: var(--text-muted); font-size: 0.85rem; white-space: nowrap;">
                                        <?php echo date('M d, Y', strtotime($issue['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <a href="report-issue.php" style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; text-decoration: none; color: white; border: 1px solid var(--glass-border); transition: all 0.3s ease; display: block;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="background: rgba(59, 130, 246, 0.15); padding: 1.25rem; border-radius: 12px;">
                            <i class="fas fa-plus-circle" style="font-size: 1.75rem; color: #3b82f6;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 600;">Report New Issue</h3>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">Submit a new maintenance request</p>
                        </div>
                    </div>
                </a>

                <a href="my-issues.php" style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; text-decoration: none; color: white; border: 1px solid var(--glass-border); transition: all 0.3s ease; display: block;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="background: rgba(139, 92, 246, 0.15); padding: 1.25rem; border-radius: 12px;">
                            <i class="fas fa-clipboard-list" style="font-size: 1.75rem; color: #8b5cf6;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 600;">View My Issues</h3>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">Track all your reported issues</p>
                        </div>
                    </div>
                </a>

                <a href="profile.php" style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; text-decoration: none; color: white; border: 1px solid var(--glass-border); transition: all 0.3s ease; display: block;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div style="background: rgba(34, 197, 94, 0.15); padding: 1.25rem; border-radius: 12px;">
                            <i class="fas fa-user" style="font-size: 1.75rem; color: #22c55e;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 600;">My Profile</h3>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text-muted); font-size: 0.9rem;">Update your profile information</p>
                        </div>
                    </div>
                </a>
            </div>
        </main>
    </div>

    <!-- Live Polling & Real-time Sync -->
    <script src="../assets/js/issue-sync.js"></script>
    <script src="../assets/js/translations.js"></script>

    <script>
    // Theme Toggle Functionality
    (function initTheme() {
        const savedTheme = localStorage.getItem('studentTheme');
        if (savedTheme === 'light') {
            document.body.classList.add('student-light-mode');
        }
    })();
    </script>
</body>
</html>
