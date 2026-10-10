<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

require_login('login.php');
require_role(ROLE_STAFF, 'login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_STAFF);

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

        if (json_update(STAFF_FILE, $user['id'], $updates)) {
            $success = 'Password changed successfully!';
            log_activity($user_id, ROLE_STAFF, 'change_password', 'Staff changed password');
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

$settings = json_read(SETTINGS_FILE);

$display_date = date('d/m/Y');
$display_time = date('H:i');
$display_name = !empty($user['name']) ? $user['name'] : 'Maintenance Staff';
$display_email = $user['email'] ?? '';

// Get user initials for avatar
$user_initials = strtoupper(substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Maintenance Staff</title>
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
                <a href="settings.php" class="active">
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
                    <h1>Settings</h1>
                    <p>Manage your account preferences and settings.</p>
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
                <!-- Language Section -->
                <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--glass-border);">
                    <h4 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; font-size: 1.1rem;">
                        <i class="fas fa-globe" style="color: var(--accent-color);"></i> Language
                    </h4>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.25rem;">Interface Language</label>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Choose your preferred display language.</p>
                        </div>
                        <select style="padding: 0.5rem 1rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 6px; color: white; font-size: 0.9rem;" onchange="changeLanguage(this.value)">
                            <option value="en" <?php echo (!isset($_COOKIE['lang']) || $_COOKIE['lang'] === 'en') ? 'selected' : ''; ?>>
                                English
                            </option>
                            <option value="ts" <?php echo (isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'ts') ? 'selected' : ''; ?>>Xitsonga</option>
                            <option value="zu" <?php echo (isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'zu') ? 'selected' : ''; ?>>isiZulu</option>
                        </select>
                    </div>
                </div>

                <!-- Appearance Section -->
                <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--glass-border);">
                    <h4 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; font-size: 1.1rem;">
                        <i class="fas fa-palette" style="color: var(--accent-color);"></i> Appearance
                    </h4>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.25rem;">Light Mode</label>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Toggle between dark and light interface theme.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="themeToggle" onchange="toggleTheme()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Account Section -->
                <div>
                    <h4 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; font-size: 1.1rem;">
                        <i class="fas fa-user-shield" style="color: var(--accent-color);"></i> Account
                    </h4>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.25rem;">Change Password</label>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Update your account password regularly.</p>
                        </div>
                        <button onclick="showPasswordModal()" style="padding: 0.5rem 1rem; background: var(--accent-color); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--glass-border);">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.25rem;">Current Session</label>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">
                                Signed in as <strong style="color: var(--accent-color);"><?php echo htmlspecialchars($user['email']); ?></strong>
                                <br>
                                <span style="font-size: 0.8rem;">Role: Maintenance Staff</span>
                            </p>
                        </div>
                        <button onclick="if(confirm('Sign out of maintenance panel?')) window.location.href='login.php?action=logout';" style="padding: 0.5rem 1rem; background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-right-from-bracket"></i> Sign Out
                        </button>
                    </div>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, rgba(79, 143, 192, 0.08) 0%, rgba(79, 143, 192, 0.04) 100%); padding: 1.25rem 1.5rem; border-radius: 12px; border: 1px solid rgba(79, 143, 192, 0.15); display: flex; align-items: center; gap: 1rem;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(79, 143, 192, 0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-circle-info" style="font-size: 1.25rem; color: var(--accent-color);"></i>
                </div>
                <div>
                    <div style="font-weight: 700;">System Status: <span style="color: #22c55e;"><i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 4px;"></i>Operational</span></div>
                    <div style="font-size: 0.825rem; color: var(--text-muted); margin-top: 2px;">
                        Settings last updated: <?php echo isset($settings['updated_at']) ? date('M d, Y @ H:i', strtotime($settings['updated_at'])) : 'Never'; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.7); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-color); padding: 2rem; border-radius: 12px; border: 1px solid var(--glass-border); max-width: 450px; width: 90%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0; font-size: 1.25rem;"><i class="fas fa-key" style="margin-right: 8px; color: var(--accent-color);"></i>Change Password</h3>
                <button onclick="hidePasswordModal()" style="background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form id="passwordForm" method="POST" action="">
                <input type="hidden" name="change_password" value="1">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Current Password</label>
                    <input type="password" id="currentPassword" name="current_password" placeholder="Enter your current password" required style="width: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 6px; color: white; font-size: 0.95rem;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">New Password</label>
                    <input type="password" id="newPassword" name="new_password" placeholder="Min. 8 characters" required minlength="8" style="width: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 6px; color: white; font-size: 0.95rem;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter new password" required minlength="8" style="width: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 6px; color: white; font-size: 0.95rem;">
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-bottom: 1.5rem;">
                    <i class="fas fa-shield-halved" style="color: #22c55e;"></i)
                    Passwords are securely hashed before storage.
                </div>
            </form>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button onclick="hidePasswordModal()" style="padding: 0.5rem 1rem; background: var(--glass-bg); color: white; border: 1px solid var(--glass-border); border-radius: 6px; cursor: pointer;">Cancel</button>
                <button onclick="validateAndSubmit()" style="padding: 0.5rem 1rem; background: var(--accent-color); color: white; border: none; border-radius: 6px; cursor: pointer;">
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
            alert('New password must be at least 8 characters');
            return;
        }
        if (np !== cp) {
            alert('Passwords do not match');
            return;
        }
        document.getElementById('passwordForm').submit();
    }

    function changeLanguage(lang) {
        document.cookie = `lang=${lang}; path=/; max-age=31536000; SameSite=Lax`;
        alert('Language preference saved');
    }

    function applyTheme(isLight) {
        const root = document.documentElement;
        if (isLight) {
            document.body.classList.add('maintenance-light-mode');
        } else {
            document.body.classList.remove('maintenance-light-mode');
        }
    }

    function toggleTheme() {
        const isLight = document.getElementById('themeToggle').checked;
        localStorage.setItem('maintenanceTheme', isLight ? 'light' : 'dark');
        applyTheme(isLight);
        alert(isLight ? 'Switched to Light Mode' : 'Switched to Dark Mode');
    }

    (function loadPrefs() {
        const saved = localStorage.getItem('maintenanceTheme');
        const isLight = saved === null ? true : saved === 'light';
        const toggle = document.getElementById('themeToggle');
        if (toggle) toggle.checked = isLight;
        applyTheme(isLight);
    })();

    document.getElementById('passwordModal').addEventListener('click', function(e) {
        if (e.target === this) hidePasswordModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('passwordModal').style.display === 'flex') {
            hidePasswordModal();
        }
    });
    </script>
</body>
</html>
