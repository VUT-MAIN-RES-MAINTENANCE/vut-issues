<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

// Require login
require_login('login.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_ADMIN);

if (!$user) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $maintenance_enabled = isset($_POST['maintenance_enabled']) ? 'true' : 'false';
    
    // Validation
    if (empty($site_name)) {
        $error = 'Site name is required.';
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Update settings
        $settings = [
            'site_name' => htmlspecialchars($site_name),
            'contact_email' => htmlspecialchars($contact_email),
            'maintenance_enabled' => $maintenance_enabled === 'true',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (json_write(SETTINGS_FILE, $settings)) {
            $success = 'Settings updated successfully!';
            // Log activity
            log_activity($user_id, ROLE_ADMIN, 'update_settings', 'Updated system settings');
        } else {
            $error = 'Failed to update settings. Please try again.';
        }
    }
}

// Get current settings
$settings = json_read(SETTINGS_FILE);
if (empty($settings)) {
    $settings = [
        'site_name' => 'VUT MainRes Maintenance',
        'contact_email' => 'vut@mainresmaintenance.com',
        'maintenance_enabled' => false
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Settings - Admin - MainRes Maintenance">
    <meta name="keywords" content="VUT, Admin, Settings">
    <title>Settings - MainRes Maintenance</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="home-page">
    <nav class="navbar">
        <input type="checkbox" id="nav-toggle" class="nav-toggle-input">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="VUT Logo" class="nav-logo">
            VUT MainRes<span>Maintenance</span>
        </div>
        <label for="nav-toggle" class="menu-toggle-btn">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </label>
        <ul class="nav-links" id="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li><a href="students.php" class="nav-btn-text">Students</a></li>
            <li><a href="maintenance-staff.php" class="nav-btn-text">Maintenance Staff</a></li>
            <li><a href="issues.php" class="nav-btn-text">Issues</a></li>
            <li><a href="activities.php" class="nav-btn-text">Activities</a></li>
            <li><a href="settings.php" class="nav-btn-text">Settings</a></li>
            <li><a href="login.php?action=logout" class="nav-btn-primary">Log Out</a></li>
        </ul>
    </nav>

    <section class="page-container">
        <h2 class="section-title">Settings</h2>
        <p class="section-subtitle">Configure system settings.</p>
        
        <?php if ($error): ?>
            <div class="error-message" style="color: #ef4444; margin-bottom: 1rem; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message" style="color: #22c55e; margin-bottom: 1rem; padding: 1rem; background: rgba(34, 197, 94, 0.1); border-radius: 8px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form class="modern-form" method="POST" action="">
                <div class="form-group">
                    <label>Site Name</label>
                    <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="maintenance_enabled" value="true" <?php echo ($settings['maintenance_enabled'] ?? false) ? 'checked' : ''; ?> style="margin-right: 0.5rem;">
                        Enable Maintenance Mode
                    </label>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">When enabled, only admins can access the system.</small>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Settings</button>
            </form>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-services">
            <div class="footer-column">
                <div class="footer-column-title">Our Services</div>
                <p class="footer-column-text">We offer a wide range of premium maintenance services to keep your property in perfect condition.</p>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Quick Navigation</div>
                <a href="../index.php">Home</a>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Connect with us</div>
                <div class="social-icons">
                    <a href="https://www.facebook.com/VUT.ac.za/" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://x.com/VUT_Science" target="_blank" rel="noopener"><i class="fab fa-x-twitter"></i></a>
                    <a href="https://www.linkedin.com/school/vaal-university-of-technology/" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.instagram.com/vut_university/" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.tiktok.com/@vut_university" target="_blank" rel="noopener"><i class="fab fa-tiktok"></i></a>
                    <a href="https://www.youtube.com/user/VUTTV" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
                </div>
                <div class="footer-column-title">Official Site</div>
                <a href="https://vut.ac.za/" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Visit VUT Website</a>
            </div>
        </div>
        <div class="footer-content">
            <div class="footer-brand">
                <img src="../assets/images/logo.png" alt="MainRes Logo" class="footer-logo">
                <p>&copy; 2026 MainRes Maintenance. All rights reserved.</p>
            </div>
            <div class="footer-links">
                <a href="mailto:vut@mainresmaintenance.com" class="footer-link">vut@mainresmaintenance.com</a>
            </div>
        </div>
    </footer>
</body>
</html>
