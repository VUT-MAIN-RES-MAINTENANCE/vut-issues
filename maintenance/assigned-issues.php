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

// Get all issues and filter for assigned to this staff member
$all_issues = json_read(MAINTENANCE_REQUESTS_FILE);
$assigned_issues = array_filter($all_issues, function($issue) use ($user_id) {
    return isset($issue['assigned_to']) && $issue['assigned_to'] == $user_id;
});

// Re-index array
$assigned_issues = array_values($assigned_issues);

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
                    <h1>Assigned Issues</h1>
                    <p>View and manage maintenance issues assigned to you.</p>
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

            <?php if (empty($assigned_issues)): ?>
                <div style="background: var(--glass-bg); padding: 4rem 2rem; border-radius: 12px; border: 1px solid var(--glass-border); text-align: center;">
                    <i class="fas fa-inbox" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem; opacity: 0.5;"></i>
                    <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Assigned Issues</h2>
                    <p style="color: var(--text-muted);">No maintenance issues have been assigned to you yet.</p>
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
                            <?php foreach ($assigned_issues as $index => $issue): ?>
                            <tr style="border-top: 1px solid var(--glass-border); transition: background 0.2s ease;">
                                <td style="padding: 1rem; font-family: monospace; font-size: 0.9rem;"><?php echo htmlspecialchars(substr($issue['id'], -8)); ?></td>
                                <td style="padding: 1rem; font-weight: 500;"><?php echo htmlspecialchars($issue['issue']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($issue['student_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($issue['residence']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($issue['block']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($issue['room']); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; 
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
                                    <a href="issue-details.php?id=<?php echo htmlspecialchars($issue['id']); ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--accent-color); color: white; text-decoration: none; border-radius: 6px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease;">
                                        View Details <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
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
