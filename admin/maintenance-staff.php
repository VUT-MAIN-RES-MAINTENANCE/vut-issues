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

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $staff_id = $_GET['id'];
    if (json_delete(STAFF_FILE, $staff_id)) {
        log_activity($user_id, ROLE_ADMIN, 'delete_staff', "Deleted staff: $staff_id", $staff_id);
        header('Location: maintenance-staff.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $existing = json_find(STAFF_FILE, 'email', $email);
        if ($existing) {
            $error = 'Email already registered.';
        } else {
            $new_staff = [
                'id' => generate_id('staff_'),
                'name' => htmlspecialchars($name),
                'email' => htmlspecialchars($email),
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => htmlspecialchars($phone),
                'role' => ROLE_STAFF,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (json_add(STAFF_FILE, $new_staff)) {
                $success = 'Staff member added successfully!';
                log_activity($user_id, ROLE_ADMIN, 'add_staff', "Added staff: $email");
            } else {
                $error = 'Failed to add staff member. Please try again.';
            }
        }
    }
}

$staff = json_read(STAFF_FILE);

usort($staff, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

$issues = json_read(MAINTENANCE_REQUESTS_FILE);
$staff_load = [];
foreach ($staff as $s) {
    $sid = $s['id'];
    $assigned = array_filter($issues, fn($i) => ($i['assigned_to'] ?? '') === $sid);
    $completed = array_filter($assigned, fn($i) => ($i['status'] ?? '') === 'completed');
    $staff_load[$sid] = ['total' => count($assigned), 'done' => count($completed)];
}

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
    <title>Maintenance Staff - Admin Dashboard</title>
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
                <li><a href="maintenance-staff.php" class="active"><i class="fas fa-tools"></i> Maintenance Staff</a></li>
                <li><a href="issues.php"><i class="fas fa-clipboard-list"></i> Issues</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li>
                    <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Maintenance Staff</h1>
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

            <?php if ($error): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="form-container" style="margin-bottom: 1.75rem;">
                <h3 style="display: flex; align-items: center; gap: 0.65rem;">
                    <i class="fas fa-user-plus" style="color: var(--admin-accent-cyan);"></i>
                    Add New Staff Member
                </h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label><i class="fas fa-user" style="margin-right: 6px; color: var(--admin-text-dim); font-size: 0.8rem;"></i>Full Name</label>
                            <input type="text" name="name" placeholder="Risima Kubayi" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope" style="margin-right: 6px; color: var(--admin-text-dim); font-size: 0.8rem;"></i>Email</label>
                            <input type="email" name="email" placeholder="staff@vut.ac.za" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock" style="margin-right: 6px; color: var(--admin-text-dim); font-size: 0.8rem;"></i>Password</label>
                            <input type="password" name="password" placeholder="Min. 6 characters" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone" style="margin-right: 6px; color: var(--admin-text-dim); font-size: 0.8rem;"></i>Phone (optional)</label>
                            <input type="tel" name="phone" placeholder="+27 00 000 0000">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                        <i class="fas fa-plus"></i>
                        Add Staff Member
                    </button>
                </form>
            </div>

            <?php if (empty($staff)): ?>
                <div class="empty-state">
                    <i class="fas fa-tools"></i>
                    <h3>No Maintenance Staff</h3>
                    <p>No maintenance staff have been added yet. Use the form above to add team members.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="services-table">
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th>Contact</th>
                                <th>Assigned</th>
                                <th>Completed</th>
                                <th>Performance</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff as $member):
                                $sid = $member['id'];
                                $load = $staff_load[$sid] ?? ['total' => 0, 'done' => 0];
                                $rate = $load['total'] > 0 ? round(($load['done'] / $load['total']) * 100) : 0;
                                $rateColor = $rate >= 75 ? '#22c55e' : ($rate >= 40 ? '#f59e0b' : '#ef4444');
                            ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #ef4444); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: #fff; flex-shrink: 0;">
                                            <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--admin-text-primary);"><?php echo htmlspecialchars($member['name']); ?></div>
                                            <div style="display: flex; align-items: center; gap: 4px; font-size: 0.72rem; color: var(--admin-accent-green);">
                                                <i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                                                Active
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; color: var(--admin-accent-cyan);"><?php echo htmlspecialchars($member['email']); ?></div>
                                    <div style="font-size: 0.78rem; color: var(--admin-text-muted); margin-top: 2px;">
                                        <i class="fas fa-phone" style="font-size: 0.7rem; margin-right: 3px;"></i>
                                        <?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                                        <i class="fas fa-clipboard-list" style="font-size: 0.75rem; color: #3b82f6;"></i>
                                        <?php echo $load['total']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: var(--admin-accent-green);">
                                        <i class="fas fa-check-circle" style="font-size: 0.75rem;"></i>
                                        <?php echo $load['done']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 140px;">
                                        <div style="flex: 1; height: 6px; background: rgba(100, 116, 139, 0.2); border-radius: 3px; overflow: hidden;">
                                            <div style="height: 100%; width: <?php echo $rate; ?>%; background: <?php echo $rateColor; ?>; border-radius: 3px;"></div>
                                        </div>
                                        <span style="font-size: 0.8rem; font-weight: 700; color: <?php echo $rateColor; ?>;"><?php echo $rate; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo date('M d, Y', strtotime($member['created_at'] ?? 'now')); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo date('H:i', strtotime($member['created_at'] ?? 'now')); ?></div>
                                </td>
                                <td>
                                    <a href="maintenance-staff.php?action=delete&id=<?php echo htmlspecialchars($member['id']); ?>"
                                       onclick="return confirm('Are you sure you want to delete this staff member?');"
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
