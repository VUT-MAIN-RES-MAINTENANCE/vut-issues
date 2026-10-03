<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php?mode=signup');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!$terms) {
        $error = 'You must agree to the terms & policy.';
    } elseif (!in_array($role, [ROLE_STUDENT, ROLE_STAFF, ROLE_ADMIN])) {
        $error = 'Invalid role selected.';
    } else {
        // Determine file based on role
        switch ($role) {
            case ROLE_STUDENT:
                $file = STUDENTS_FILE;
                break;
            case ROLE_STAFF:
                $file = STAFF_FILE;
                break;
            case ROLE_ADMIN:
                $file = ADMINS_FILE;
                break;
            default:
                $file = null;
        }
        
        if (!$file) {
            $error = 'Invalid role selected.';
        } else {
            // Check if email already exists in the selected role file
            $existing = json_find($file, 'email', $email);
            if ($existing) {
                $error = 'Email already registered for this role.';
            } else {
                // Create new user
                $user = [
                    'id' => generate_id($role . '_'),
                    'name' => htmlspecialchars($name),
                    'email' => htmlspecialchars($email),
                    'password' => hash_password($password),
                    'role' => $role,
                    'phone' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Add role-specific fields
                if ($role === ROLE_STUDENT) {
                    $user['residence'] = '';
                    $user['room'] = '';
                }
                
                if (json_add($file, $user)) {
                    $success = 'Registration successful! Redirecting to login...';
                    // Log activity
                    log_activity($user['id'], $role, 'register', ucfirst($role) . ' registered: ' . $user['name']);
                    // Redirect to login after 2 seconds
                    header("refresh:2; url=login.php");
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign up or log in to MainRes Maintenance - Get started with your maintenance requests.">
    <meta name="keywords" content="VUT, Login, Sign Up, Maintenance">
    <title>Get Started - MainRes Maintenance</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="auth-page-body">
    <!-- Navigation -->
    <nav class="navbar" id="main-nav">
        <input type="checkbox" id="nav-toggle" class="nav-toggle-input" aria-label="Toggle navigation menu">
        <label for="nav-toggle" style="display: none;"></label>
        <div class="logo" id="brand-logo">
            <img src="../assets/images/logo.png" alt="VUT Logo" class="nav-logo">
            VUT MainRes<span>Maintenance</span>
        </div>
        <!-- Toggle Button -->
        <label for="nav-toggle" class="menu-toggle-btn" id="mobile-menu-toggle">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </label>
        <ul class="nav-links" id="nav-links">
            <li><a href="../index.php" id="nav-home">Home</a></li>
            <li><a href="../index.php?page=about" class="nav-btn-text" id="nav-about">About</a></li>
            <li><a href="report-issue.php" class="nav-btn-text" id="nav-report">Report</a></li>
            <li><a href="login.php" class="nav-btn-text" id="nav-login">Log In</a></li>
            <li><a href="register.php" class="nav-btn-primary" id="nav-signup">Sign Up</a></li>
        </ul>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-container">
            <!-- Left Side: Form -->
            <div class="auth-form-side">
                <div class="auth-header">
                    <a href="../index.php" class="auth-logo">
                        <img src="../assets/images/logo.png" alt="VUT Logo">
                        VUT MainRes
                    </a>
                </div>
                
                <div class="auth-content" id="signup-content">
                    <h1>Create Your Account</h1>
                    <p style="margin-bottom: 1.5rem; color: var(--text-muted);">Select your role and fill in your details to get started</p>
                    
                    <?php if ($error): ?>
                        <div class="error-message" style="color: #ef4444; margin-bottom: 1rem; padding: 0.75rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border-left: 4px solid #ef4444;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success-message" style="color: #22c55e; margin-bottom: 1rem; padding: 0.75rem; background: rgba(34, 197, 94, 0.1); border-radius: 8px; border-left: 4px solid #22c55e;">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form class="modern-form" method="POST" action="">
                        <div class="form-group">
                            <label>I am a...</label>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-top: 0.5rem;">
                                <label style="cursor: pointer;">
                                    <input type="radio" name="role" value="student" required style="display: none;">
                                    <div class="role-card" style="padding: 1rem; border: 2px solid rgba(255,255,255,0.2); border-radius: 8px; text-align: center; transition: all 0.3s; background: rgba(0,0,0,0.2);">
                                        <i class="fas fa-user-graduate" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #3b82f6;"></i>
                                        <div style="font-weight: 600;">Student</div>
                                    </div>
                                </label>
                                <label style="cursor: pointer;">
                                    <input type="radio" name="role" value="staff" required style="display: none;">
                                    <div class="role-card" style="padding: 1rem; border: 2px solid rgba(255,255,255,0.2); border-radius: 8px; text-align: center; transition: all 0.3s; background: rgba(0,0,0,0.2);">
                                        <i class="fas fa-tools" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #8b5cf6;"></i>
                                        <div style="font-weight: 600;">Staff</div>
                                    </div>
                                </label>
                                <label style="cursor: pointer;">
                                    <input type="radio" name="role" value="admin" required style="display: none;">
                                    <div class="role-card" style="padding: 1rem; border: 2px solid rgba(255,255,255,0.2); border-radius: 8px; text-align: center; transition: all 0.3s; background: rgba(0,0,0,0.2);">
                                        <i class="fas fa-user-shield" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #ef4444;"></i>
                                        <div style="font-weight: 600;">Admin</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" placeholder="Enter your full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="Enter your email address" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Create a password (min. 8 characters)" required minlength="8">
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="Confirm your password" required minlength="8">
                        </div>
                        
                        <div class="form-checkbox">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <span>terms & policy</span></label>
                        </div>
                        
                        <button type="submit" class="auth-submit-btn">Create Account</button>
                    </form>
                    
                    <p class="auth-footer-text">Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>

            <!-- Right Side: Image -->
            <div class="auth-image-side">
                <img src="../assets/images/Loginfix.png" alt="VUT MainRes Background" class="side-img">
            </div>
        </div>
    </div>
    <!-- Footer Area -->
    <footer class="footer">
        <div class="footer-services">
            <div class="footer-column">
                <div class="footer-column-title">Our Services</div>
                <p class="footer-column-text">We offer a wide range of premium maintenance services to keep your property in perfect condition.</p>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Maintenance Services</div>
                <a href="../index.php?page=about">Bulb Replacement</a>
                <a href="../index.php?page=about">Window Handle</a>
                <a href="../index.php?page=about">Door Handle</a>
                <a href="../index.php?page=about">WiFi Problems</a>
                <a href="../index.php?page=about">Leakage Problems</a>
                <a href="../index.php?page=about">Stove Problem</a>
                <a href="../index.php?page=about">HVAC</a>
                <a href="../index.php?page=about">Painting</a>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Quick Navigation</div>
                <a href="../index.php">Home</a>
                <a href="login.php">Log In</a>
                <a href="../index.php?page=about">About</a>
                <a href="register.php">Sign Up</a>
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
                <!-- E-mail Hyperlink -->
                <a href="mailto:vut@mainresmaintenance.com" class="footer-link">vut@mainresmaintenance.com</a>
            </div>
        </div>
    </footer>
    <script>
        // Handle role card selection
        document.querySelectorAll('input[name="role"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Reset all cards
                document.querySelectorAll('.role-card').forEach(card => {
                    card.style.borderColor = 'rgba(255,255,255,0.2)';
                    card.style.background = 'rgba(0,0,0,0.2)';
                });
                // Highlight selected card
                if (this.checked) {
                    const selectedCard = this.nextElementSibling;
                    selectedCard.style.borderColor = '#3b82f6';
                    selectedCard.style.background = 'rgba(59, 130, 246, 0.2)';
                }
            });
        });
    </script>
</body>
</html>
