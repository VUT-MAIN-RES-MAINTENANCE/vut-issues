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

// Get issue ID from URL
$issue_id = $_GET['id'] ?? '';

if (empty($issue_id)) {
    header('Location: assigned-issues.php');
    exit;
}

// Get the issue
$issue = json_find_by_id(MAINTENANCE_REQUESTS_FILE, $issue_id);

if (!$issue) {
    header('Location: assigned-issues.php');
    exit;
}

// All maintenance staff can view and update any issue

$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $new_status = $_POST['status'] ?? '';
    $maintenance_notes = trim($_POST['maintenance_notes'] ?? '');

    // Validate status
    $valid_statuses = [STATUS_PENDING, STATUS_IN_PROGRESS, STATUS_COMPLETED];
    if (!in_array($new_status, $valid_statuses)) {
        $error = 'Invalid status.';
    } else {
        $updates = [
            'status' => $new_status,
            'maintenance_notes' => htmlspecialchars($maintenance_notes),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (json_update(MAINTENANCE_REQUESTS_FILE, $issue_id, $updates)) {
            $success = 'Issue updated successfully!';
            // Refresh issue data
            $issue = json_find_by_id(MAINTENANCE_REQUESTS_FILE, $issue_id);
            // Log activity
            log_activity($user_id, ROLE_STAFF, 'update_issue', "Updated issue status to: $new_status", $issue_id);
        } else {
            $error = 'Failed to update issue. Please try again.';
        }
    }
}

// Get user initials for avatar
$user_initials = strtoupper(substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Issue Details - Maintenance Staff - MainRes Maintenance">
    <meta name="keywords" content="VUT, Maintenance Staff, Issue Details">
    <title>Issue Details - MainRes Maintenance</title>
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
                    <h1>Issue Details</h1>
                    <p>View detailed information about a maintenance issue.</p>
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

            <div style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; border: 1px solid var(--glass-border); margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
                    <div>
                        <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Maintenance Issue #<?php echo htmlspecialchars(substr($issue['id'], -8)); ?></h2>
                        <p style="color: var(--text-muted);">Reported by <?php echo htmlspecialchars($issue['student_name']); ?></p>
                    </div>
                    <span style="padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; 
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
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div>
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Issue Type</label>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($issue['issue']); ?></div>
                    </div>
                    <div>
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Residence</label>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($issue['residence']); ?></div>
                    </div>
                    <div>
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Block</label>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($issue['block']); ?></div>
                    </div>
                    <div>
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Room</label>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($issue['room']); ?></div>
                    </div>
                    <div>
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Gender</label>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($issue['gender']); ?></div>
                    </div>
                    <div>
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Student No</label>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($issue['student_no']); ?></div>
                    </div>
                    <div>
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Reported</label>
                        <div style="font-weight: 500;"><?php echo date('M d, Y H:i', strtotime($issue['created_at'])); ?></div>
                    </div>
                </div>

                <?php if (!empty($issue['description'])): ?>
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--glass-border);">
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Description</label>
                        <div style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($issue['description'])); ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($issue['image'])): ?>
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--glass-border);">
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Issue Picture</label>
                        <img src="../<?php echo htmlspecialchars($issue['image']); ?>" alt="Issue picture" style="max-width: 400px; border-radius: 8px; border: 1px solid var(--glass-border);">
                    </div>
                <?php endif; ?>
            </div>

            <div style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; border: 1px solid var(--glass-border);">
                <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Update Status</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_status">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Status</label>
                        <select name="status" required style="width: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: white; font-size: 0.95rem;">
                            <option value="<?php echo STATUS_PENDING; ?>" <?php echo $issue['status'] === STATUS_PENDING ? 'selected' : ''; ?>>Pending</option>
                            <option value="<?php echo STATUS_IN_PROGRESS; ?>" <?php echo $issue['status'] === STATUS_IN_PROGRESS ? 'selected' : ''; ?>>In Progress</option>
                            <option value="<?php echo STATUS_COMPLETED; ?>" <?php echo $issue['status'] === STATUS_COMPLETED ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Maintenance Notes</label>
                        <textarea name="maintenance_notes" placeholder="Add notes about the maintenance work..." style="width: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: white; font-size: 0.95rem; min-height: 120px; resize: vertical;"><?php echo htmlspecialchars($issue['maintenance_notes'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" style="width: 100%; padding: 0.875rem; background: var(--accent-color); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">Update Issue</button>
                </form>
            </div>

            <div style="margin-top: 2rem;">
                <a href="assigned-issues.php" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--glass-bg); color: white; text-decoration: none; border-radius: 8px; border: 1px solid var(--glass-border); transition: all 0.2s ease;">
                    <i class="fas fa-arrow-left"></i> Back to Assigned Issues
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
