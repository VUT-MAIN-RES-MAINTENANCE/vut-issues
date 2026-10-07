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

// Handle applications status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_applications_status'])) {
    $applications_open = ($_POST['applications_open'] ?? '') === '1';
    
    // Read current settings
    $settings = json_read(SETTINGS_FILE);
    if (empty($settings)) {
        $settings = [];
    }
    
    $settings['applications_open'] = $applications_open;
    $settings['updated_at'] = date('Y-m-d H:i:s');
    
    if (json_write(SETTINGS_FILE, $settings)) {
        $success = 'Applications are now ' . ($applications_open ? 'OPEN.' : 'CLOSED.');
        log_activity($user_id, ROLE_ADMIN, 'update_settings', 'Updated applications status to: ' . ($applications_open ? 'OPEN' : 'CLOSED'));
    } else {
        $error = 'Unable to save the application status. Please try again.';
    }
}

// Handle password change
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

// Get current settings
$settings = json_read(SETTINGS_FILE);
$applications_open = $settings['applications_open'] ?? true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
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
        
        .settings-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .settings-section {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .settings-section:last-child {
            border-bottom: none;
        }
        
        .settings-section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 1.5rem;
        }
        
        .settings-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .settings-item-info label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        
        .settings-item-info p {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 26px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        .toggle-switch input:checked + .toggle-slider {
            background-color: #26648E;
        }
        
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        
        .form-control {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-primary {
            background: #26648E;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e3a5f;
        }
        
        .applications-status-line {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        .applications-status-line.is-open {
            background: #d1fae5;
            color: #065f46;
        }
        
        .applications-status-line.is-closed {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .applications-status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        .is-open .applications-status-dot {
            background: #22c55e;
        }
        
        .is-closed .applications-status-dot {
            background: #ef4444;
        }
        
        .modal-wrap {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-head h3 {
            margin: 0;
            color: #1e3a5f;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        
        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            z-index: 2000;
            animation: slideIn 0.3s ease;
        }
        
        .toast.success {
            background: #22c55e;
        }
        
        .toast.error {
            background: #ef4444;
        }
        
        .toast.warning {
            background: #f59e0b;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
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
                <li><a href="issues.php"><i class="fas fa-clipboard-list"></i> Issues</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1rem;">
                    <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Settings</h1>
                <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
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
            
            <div class="settings-card">
                <!-- Language Section -->
                <div class="settings-section">
                    <h4 class="settings-section-title">
                        <i class="fas fa-globe"></i> Language
                    </h4>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <label class="settings-item-label">Select Language</label>
                            <p class="settings-item-description">Choose your preferred language</p>
                        </div>
                        <select class="form-control" style="width: 200px;" onchange="changeLanguage(this.value)">
                            <option value="en" <?php echo (!isset($_COOKIE['lang']) || $_COOKIE['lang'] === 'en') ? 'selected' : ''; ?>>English</option>
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
                            <label class="settings-item-label">Dark mode</label>
                            <p class="settings-item-description">Switch between light and dark theme</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="darkModeToggle" onchange="toggleDarkMode()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Student Registration Section -->
                <div class="settings-section">
                    <h4 class="settings-section-title">
                        <i class="fas fa-clipboard-check"></i> Student Registration / Applications
                    </h4>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <label for="applicationsOpen" class="settings-item-label">Allow student registration and applications</label>
                            <p class="settings-item-description">When closed, students cannot create accounts or submit residence applications.</p>
                        </div>
                        <label class="toggle-switch" for="applicationsOpen">
                            <input type="checkbox" id="applicationsOpen" name="applications_open" value="1" <?php echo $applications_open ? 'checked' : ''; ?> aria-label="Allow student registration and applications" onchange="updateApplicationsStatus()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <p class="applications-status-line <?php echo $applications_open ? 'is-open' : 'is-closed'; ?>" role="status" id="applicationsStatusLine">
                        <span class="applications-status-dot" aria-hidden="true"></span>
                        Applications: <strong id="applicationsStatusText"><?php echo $applications_open ? 'OPEN' : 'CLOSED'; ?></strong>
                    </p>
                </div>

                <!-- Account Section -->
                <div class="settings-section">
                    <h4 class="settings-section-title">
                        <i class="fas fa-user-shield"></i> Account
                    </h4>
                    <div class="settings-item">
                        <div class="settings-item-info">
                            <label class="settings-item-label">Change password</label>
                            <p class="settings-item-description">Update your account password</p>
                        </div>
                        <button class="btn btn-secondary" onclick="showPasswordModal()">Change</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Password Change Modal -->
    <div class="modal-wrap" id="passwordModal" style="display: none;">
        <div class="modal">
            <div class="modal-head">
                <h3>Change Password</h3>
                <button class="modal-close" onclick="hidePasswordModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="passwordForm" method="POST" action="">
                    <input type="hidden" name="change_password" value="1">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required minlength="8">
                    </div>
                </form>
            </div>
            <div class="modal-foot">
                <button class="btn btn-secondary" onclick="hidePasswordModal()">Cancel</button>
                <button class="btn btn-primary" onclick="document.getElementById('passwordForm').submit()">Change Password</button>
            </div>
        </div>
    </div>

    <script>
    // Update applications status display dynamically and save automatically
    function updateApplicationsStatus() {
        const checkbox = document.getElementById('applicationsOpen');
        const statusLine = document.getElementById('applicationsStatusLine');
        const statusText = document.getElementById('applicationsStatusText');
        const isOpen = checkbox.checked ? '1' : '0';

        if (checkbox.checked) {
            statusLine.classList.remove('is-closed');
            statusLine.classList.add('is-open');
            statusText.textContent = 'OPEN';
        } else {
            statusLine.classList.remove('is-open');
            statusLine.classList.add('is-closed');
            statusText.textContent = 'CLOSED';
        }

        // Auto-save via AJAX
        const formData = new FormData();
        formData.append('save_applications_status', '1');
        formData.append('applications_open', isOpen);

        fetch('settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            showToast('Applications status updated', 'success');
        })
        .catch(error => {
            console.error('Error saving status:', error);
            showToast('Error saving status', 'error');
            // Revert the toggle on error
            checkbox.checked = !checkbox.checked;
            updateApplicationsStatus();
        });
    }

    // Password modal functions
    function showPasswordModal() {
        document.getElementById('passwordModal').style.display = 'flex';
    }

    function hidePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
        document.getElementById('passwordForm').reset();
    }

    // Language change
    function changeLanguage(lang) {
        document.cookie = `lang=${lang}; path=/; max-age=31536000`;
        showToast('Language changed', 'success');
    }

    // Dark mode toggle
    function toggleDarkMode() {
        const isDark = document.getElementById('darkModeToggle').checked;
        localStorage.setItem('darkMode', isDark);
        if (isDark) {
            document.body.style.background = '#1a1a2e';
            document.querySelector('.admin-content').style.background = '#16213e';
        } else {
            document.body.style.background = '';
            document.querySelector('.admin-content').style.background = '#f5f7fa';
        }
        showToast('Theme changed', 'success');
    }

    // Load dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
        document.getElementById('darkModeToggle').checked = true;
        document.body.style.background = '#1a1a2e';
        document.querySelector('.admin-content').style.background = '#16213e';
    }

    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // Close modal when clicking outside
    document.getElementById('passwordModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hidePasswordModal();
        }
    });
    </script>
</body>
</html>
