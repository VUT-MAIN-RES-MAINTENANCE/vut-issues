<?php
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Profile - MainRes Maintenance">
    <meta name="keywords" content="VUT, Profile, Student">
    <title>Profile - MainRes Maintenance</title>
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
            <li><a href="../index.php?page=about">About</a></li>
            <li><a href="report-issue.php">Report</a></li>
            <li><a href="login.php?action=logout" class="nav-btn-primary">Log Out</a></li>
        </ul>
    </nav>

    <section class="page-container">
        <h2 class="section-title">My Profile</h2>
        <p class="section-subtitle">Manage your student profile information.</p>
        
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
                    <label>Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled style="background: rgba(0, 0, 0, 0.2); cursor: not-allowed;">
                    <small style="color: var(--text-muted);">Email cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label>Residence</label>
                    <select name="residence">
                        <option value="" <?php echo empty($user['residence']) ? 'selected' : ''; ?>>Select Residence</option>
                        <option value="Nkandla" <?php echo ($user['residence'] ?? '') === 'Nkandla' ? 'selected' : ''; ?>>Nkandla</option>
                        <option value="Malema" <?php echo ($user['residence'] ?? '') === 'Malema' ? 'selected' : ''; ?>>Malema</option>
                        <option value="Leseding" <?php echo ($user['residence'] ?? '') === 'Leseding' ? 'selected' : ''; ?>>Leseding</option>
                        <option value="Khomanani" <?php echo ($user['residence'] ?? '') === 'Khomanani' ? 'selected' : ''; ?>>Khomanani</option>
                        <option value="Meropa" <?php echo ($user['residence'] ?? '') === 'Meropa' ? 'selected' : ''; ?>>Meropa</option>
                        <option value="Khayelethu" <?php echo ($user['residence'] ?? '') === 'Khayelethu' ? 'selected' : ''; ?>>Khayelethu</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" name="room" value="<?php echo htmlspecialchars($user['room'] ?? ''); ?>" placeholder="e.g., G1, F2, S3">
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter your phone number">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Profile</button>
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
                <a href="report-issue.php">Report Issue</a>
                <a href="../index.php?page=about">About</a>
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
