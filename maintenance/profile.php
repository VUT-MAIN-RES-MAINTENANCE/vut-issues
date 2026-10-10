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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($name)) {
        $error = 'Name is required.';
    } else {
        // Update staff record
        $updates = [
            'name' => htmlspecialchars($name),
            'phone' => htmlspecialchars($phone),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (json_update(STAFF_FILE, $user_id, $updates)) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user = get_user_by_id($user_id, ROLE_STAFF);
            // Log activity
            log_activity($user_id, ROLE_STAFF, 'update_profile', 'Updated profile information');
        } else {
            $error = 'Failed to update profile. Please try again.';
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
    <meta name="description" content="Maintenance Staff Profile - MainRes Maintenance">
    <meta name="keywords" content="VUT, Maintenance Staff, Profile">
    <title>Profile - MainRes Maintenance</title>
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
                <a href="profile.php" class="active">
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
                    <h1>My Profile</h1>
                    <p>Manage your maintenance staff profile information.</p>
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

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                <div style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; border: 1px solid var(--glass-border);">
                    <form method="POST" action="">
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required style="width: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: white; font-size: 0.95rem;">
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled style="width: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.3); border: 1px solid var(--glass-border); border-radius: 8px; color: var(--text-muted); font-size: 0.95rem; cursor: not-allowed;">
                            <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.5rem;">Email cannot be changed</small>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter your phone number" style="width: 100%; padding: 0.75rem; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: white; font-size: 0.95rem;">
                        </div>
                        
                        <button type="submit" style="width: 100%; padding: 0.875rem; background: var(--accent-color); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">Update Profile</button>
                    </form>
                </div>

                <div style="background: var(--glass-bg); padding: 2rem; border-radius: 12px; border: 1px solid var(--glass-border);">
                    <h3 style="font-size: 1.1rem; margin-bottom: 1rem;">Account Information</h3>
                    <div style="display: grid; gap: 1rem;">
                        <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid var(--glass-border);">
                            <span style="color: var(--text-muted);">User ID</span>
                            <span style="font-family: monospace;"><?php echo htmlspecialchars(substr($user['id'], -12)); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid var(--glass-border);">
                            <span style="color: var(--text-muted);">Role</span>
                            <span style="text-transform: capitalize;"><?php echo htmlspecialchars($user['role']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid var(--glass-border);">
                            <span style="color: var(--text-muted);">Member Since</span>
                            <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Last Updated</span>
                            <span><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
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
