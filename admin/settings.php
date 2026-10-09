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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required.';
    } elseif (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (!verify_password($current_password, $user['password'])) {
        $error = 'Current password is incorrect.';
    } else {
        $updates = [
            'password' => hash_password($new_password),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (json_update(ADMINS_FILE, $user['id'], $updates)) {
            $success = 'Password changed successfully!';
            log_activity($user_id, ROLE_ADMIN, 'change_password', 'Admin changed password');
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

$settings = json_read(SETTINGS_FILE);

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
    <title>Settings - Admin Dashboard</title>
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
                <li><a href="issues.php"><i class="fas fa-clipboard-list"></i> Issues</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                <li>
                    <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="admin-header">
                <h1>System Settings</h1>
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
                <div class="alert-error"><i class="fas fa-circle-exclamation" style="margin-right: 8px;"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-success"><i class="fas fa-circle-check" style="margin-right: 8px;"></i><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="settings-card">
                <!-- Language Section -->
                <div class="settings-section">
                    <h4 class="settings-section-title">
                        <i class="fas fa-globe"></i> Language
                    </h4>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <label>Interface Language</label>
                            <p>Choose your preferred display language.</p>
                        </div>
                        <select class="form-control" style="width: 220px;" onchange="changeLanguage(this.value)">
                            <option value="en" <?php echo (!isset($_COOKIE['lang']) || $_COOKIE['lang'] === 'en') ? 'selected' : ''; ?>>
                                <i class="fas fa-flag-usa"></i> English
                            </option>
                            <option value="ts" <?php echo (isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'ts') ? 'selected' : ''; ?>>Xitsonga</option>
                            <option value="zu" <?php echo (isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'zu') ? 'selected' : ''; ?>>isiZulu</option>
                        </select>
                    </div>
                </div>

                <!-- Appearance Section -->
                <div class="settings-section">
                    <h4 class="settings-section-title">
                        <i class="fas fa-palette"></i> Appearance
                    </h4>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <label>Light Mode</label>
                            <p>Toggle between dark (default) and light interface theme.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="themeToggle" onchange="toggleTheme()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Account Section -->
                <div class="settings-section">
                    <h4 class="settings-section-title">
                        <i class="fas fa-user-shield"></i> Account
                    </h4>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <label>Change Password</label>
                            <p>Update your admin account password regularly.</p>
                        </div>
                        <button class="btn btn-primary" onclick="showPasswordModal()">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                    <div class="settings-item" style="border-top: 1px solid var(--admin-glass-border); margin-top: 0.5rem; padding-top: 1.25rem;">
                        <div class="settings-item-info">
                            <label>Current Session</label>
                            <p>
                                Signed in as <strong style="color: var(--admin-accent-cyan);"><?php echo htmlspecialchars($user['email']); ?></strong>
                                <br>
                                <span style="font-size: 0.78rem;">Role: System Administrator</span>
                            </p>
                        </div>
                        <button class="btn btn-danger" onclick="if(confirm('Sign out of admin panel?')) window.location.href='login.php?action=logout';">
                            <i class="fas fa-right-from-bracket"></i> Sign Out
                        </button>
                    </div>
                </div>
            </div>

            <div style="margin-top: 1.75rem; padding: 1.25rem 1.5rem; border-radius: var(--admin-radius-lg); background: linear-gradient(135deg, rgba(6, 182, 212, 0.08) 0%, rgba(212, 175, 55, 0.06) 100%); border: 1px solid rgba(6, 182, 212, 0.15); display: flex; align-items: center; gap: 1rem;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(212, 175, 55, 0.18)); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-circle-info" style="font-size: 1.25rem; color: var(--admin-accent-cyan);"></i>
                </div>
                <div>
                    <div style="font-weight: 700; color: var(--admin-text-primary);">System Status: <span style="color: var(--admin-accent-green);"><i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 4px; animation: pulse 2s infinite;"></i>Operational</span></div>
                    <div style="font-size: 0.825rem; color: var(--admin-text-muted); margin-top: 2px;">
                        Settings last updated: <?php echo isset($settings['updated_at']) ? date('M d, Y @ H:i', strtotime($settings['updated_at'])) : 'Never'; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Password Change Modal -->
    <div class="modal-wrap" id="passwordModal" style="display: none;">
        <div class="modal">
            <div class="modal-head">
                <h3><i class="fas fa-key" style="margin-right: 8px; color: var(--admin-accent-cyan);"></i>Change Admin Password</h3>
                <button class="modal-close" onclick="hidePasswordModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="passwordForm" method="POST" action="">
                    <input type="hidden" name="change_password" value="1">
                    <div class="form-group">
                        <label><i class="fas fa-lock" style="margin-right: 6px; color: var(--admin-text-dim); font-size: 0.8rem;"></i>Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="Enter your current password" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-key" style="margin-right: 6px; color: var(--admin-text-dim); font-size: 0.8rem;"></i>New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Min. 8 characters" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check-double" style="margin-right: 6px; color: var(--admin-text-dim); font-size: 0.8rem;"></i>Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Re-enter new password" required minlength="8">
                    </div>
                    <div style="font-size: 0.78rem; color: var(--admin-text-muted); display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-shield-halved" style="color: var(--admin-accent-green);"></i>
                        Passwords are securely hashed before storage.
                    </div>
                </form>
            </div>
            <div class="modal-foot">
                <button class="btn btn-secondary" onclick="hidePasswordModal()">Cancel</button>
                <button class="btn btn-primary" onclick="validateAndSubmit()">
                    <i class="fas fa-save"></i> Update Password
                </button>
            </div>
        </div>
    </div>

    <script>
    function showPasswordModal() {
        document.getElementById('passwordModal').style.display = 'flex';
        document.getElementById('currentPassword').focus();
    }

    function hidePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
        document.getElementById('passwordForm').reset();
    }

    function validateAndSubmit() {
        const np = document.getElementById('newPassword').value;
        const cp = document.getElementById('confirmPassword').value;
        if (np.length < 8) {
            showToast('New password must be at least 8 characters', 'error');
            return;
        }
        if (np !== cp) {
            showToast('Passwords do not match', 'error');
            return;
        }
        document.getElementById('passwordForm').submit();
    }

    function changeLanguage(lang) {
        document.cookie = `lang=${lang}; path=/; max-age=31536000; SameSite=Lax`;
        showToast('Language preference saved', 'success');
    }

    function applyTheme(isLight) {
        const root = document.documentElement;
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
            document.body.classList.add('admin-light-mode');
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
            document.body.classList.remove('admin-light-mode');
        }
    }

    function toggleTheme() {
        const isLight = document.getElementById('themeToggle').checked;
        localStorage.setItem('adminTheme', isLight ? 'light' : 'dark');
        applyTheme(isLight);
        showToast(isLight ? 'Switched to Light Mode' : 'Switched to Dark Mode', 'success');
    }

    (function loadPrefs() {
        const saved = localStorage.getItem('adminTheme');
        const isLight = saved === 'light';
        const toggle = document.getElementById('themeToggle');
        if (toggle) toggle.checked = isLight;
        applyTheme(isLight);
    })();

    function showToast(message, type = 'success') {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const icon = type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-xmark' : 'fa-circle-exclamation';
        toast.innerHTML = `<i class="fas ${icon}" style="margin-right: 8px;"></i>${message}`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(() => toast.remove(), 200);
        }, 2800);
    }

    document.getElementById('passwordModal').addEventListener('click', function(e) {
        if (e.target === this) hidePasswordModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('passwordModal').style.display === 'flex') {
            hidePasswordModal();
        }
    });
    </script>
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .toast {
            transition: opacity 0.2s, transform 0.2s;
        }
    </style>
</body>
</html>
