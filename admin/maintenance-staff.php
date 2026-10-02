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

// Handle staff deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $staff_id = $_GET['id'];
    if (json_delete(STAFF_FILE, $staff_id)) {
        // Log activity
        log_activity($user_id, ROLE_ADMIN, 'delete_staff', "Deleted staff: $staff_id", $staff_id);
        header('Location: maintenance-staff.php');
        exit;
    }
}

// Handle staff addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $existing = json_find_by_field(STAFF_FILE, 'email', $email);
        if ($existing) {
            $error = 'Email already registered.';
        } else {
            // Add staff member
            $new_staff = [
                'id' => json_generate_id(),
                'name' => htmlspecialchars($name),
                'email' => htmlspecialchars($email),
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => htmlspecialchars($phone),
                'role' => ROLE_STAFF,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if (json_add(STAFF_FILE, $new_staff)) {
                $success = 'Staff member added successfully!';
                // Log activity
                log_activity($user_id, ROLE_ADMIN, 'add_staff', "Added staff: $email");
            } else {
                $error = 'Failed to add staff member. Please try again.';
            }
        }
    }
}

// Get all staff
$staff = json_read(STAFF_FILE);

// Sort by created date (newest first)
usort($staff, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage Maintenance Staff - Admin - MainRes Maintenance">
    <meta name="keywords" content="VUT, Admin, Maintenance Staff">
    <title>Maintenance Staff - MainRes Maintenance</title>
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
        <h2 class="section-title">Manage Maintenance Staff</h2>
        <p class="section-subtitle">View and manage maintenance staff accounts.</p>
        
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
        
        <div class="form-container" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">Add New Staff Member</h3>
            <form class="modern-form" method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Add Staff Member</button>
            </form>
        </div>
        
        <?php if (empty($staff)): ?>
            <div class="about-hero-banner" style="margin-bottom: 2rem;">
                <h1>No Maintenance Staff</h1>
                <p>No maintenance staff have been added yet.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($member['created_at'] ?? 'now')); ?></td>
                            <td>
                                <a href="maintenance-staff.php?action=delete&id=<?php echo htmlspecialchars($member['id']); ?>" onclick="return confirm('Are you sure you want to delete this staff member?');" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background: #ef4444;">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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
