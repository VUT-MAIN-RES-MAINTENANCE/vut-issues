<?php
require_once 'includes/config.php';
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Report maintenance issues at VUT Main Residence and get help keeping your living space in good shape.">
    <meta name="keywords" content="Maintenance, Services, MainRes, Property Management">
    <meta name="author" content="Student Project">
    <title>MainRes Maintenance - Home</title>
    
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo filemtime(__DIR__ . '/assets/css/styles.css'); ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="home-page">
    <!-- Navigation -->
    <nav class="navbar" id="main-nav">
        <input type="checkbox" id="nav-toggle" class="nav-toggle-input" aria-label="Toggle navigation menu" title="Toggle navigation menu">
        <div class="logo" id="brand-logo">
            <img src="assets/images/logo.png" alt="VUT Logo" class="nav-logo">
            VUT MainRes<span>Maintenance</span>
        </div>
        <!-- Toggle Button -->
        <label for="nav-toggle" class="menu-toggle-btn" id="mobile-menu-toggle">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </label>
        <ul class="nav-links" id="nav-links">
            <li><a href="index.php" id="nav-home">Home</a></li>
            <li><a href="index.php?page=about" class="nav-btn-text" id="nav-about">About</a></li>
            <li><a href="student/report-issue.php" class="nav-btn-text" id="nav-report">Report</a></li>
            <li><a href="student/login.php" class="nav-btn-text" id="nav-login">Log In</a></li>
            <li><a href="student/register.php" class="nav-btn-primary" id="nav-signup">Sign Up</a></li>
        </ul>
    </nav>

    <?php if ($page == 'home'): ?>
    <!-- Main Hero Section -->
    <main class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content">
                <p class="hero-kicker"><span></span>STUDENT RESIDENCE MAINTENANCE</p>
                <h1>A better stay.<br><span>Starts here.</span></h1>
                <p>Report a repair at VUT Main Residence and get the right help for the place you call home.</p>
            </div>
        </div>
        <div class="hero-buttons">
            <a href="student/report-issue.php" class="btn btn-primary" id="btn-services">Report issue <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a href="index.php?page=about" class="btn btn-secondary" id="btn-about">About VUT</a>
        </div>
    </main>
    <section class="home-service-strip" aria-labelledby="home-service-title">
        <div class="home-service-inner">
            <div class="home-service-heading">
                <span>HERE WHEN YOU NEED US</span>
                <h2 id="home-service-title">What needs attention?</h2>
            </div>
            <div class="home-service-links">
                <a href="student/report-issue.php"><i class="fas fa-faucet" aria-hidden="true"></i><span>Leaks &amp; plumbing</span><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php"><i class="fas fa-bolt" aria-hidden="true"></i><span>Electrical</span><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php"><i class="fas fa-door-open" aria-hidden="true"></i><span>Doors &amp; fittings</span><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>
    <?php elseif ($page == 'about'): ?>
    <div class="about-hero-banner">
        <span class="banner-badge">About Us</span>
        <h1>Keeping VUT Residences Safe and Comfortable</h1>
        <p>The VUT Maintenance website helps residence students quickly report maintenance problems and connect with experienced repair teams</p>
    </div>

    <section class="about-section page-container">
        <div class="about-layout">
            <div class="about-content">
                <span class="badge">Our Background</span>
                <h2>Dedicated to Your Comfort</h2>
                <p class="about-lead">VUT MainRes Maintenance, based in Vanderbijlpark 1911, Gauteng, keeps student
				residences safe, clean, and comfortable. With over 20 years of experience, the team handles plumbing,
				electrical, general building repairs, and emergency support — delivering fast, professional service 
				to the VUT student community.</p>
                
                <div class="vision-mission-grid">
                    <div class="vision-mission-card">
                        <div class="vm-icon"><i class="fas fa-eye"></i></div>
                        <div class="vm-text">
                            <h4>Vision</h4>
                            <p>A safe, comfortable, worry-free home for every VUT student.</p>
                        </div>
                    </div>
                    <div class="vision-mission-card">
                        <div class="vm-icon"><i class="fas fa-bullseye"></i></div>
                        <div class="vm-text">
                            <h4>Mission</h4>
                            <p>Fix residence issues fast and right — so students can focus on studying.</p>
                        </div>
                    </div>
                </div>

                <div class="about-features">
                    <div class="about-feature">
                        <div class="feature-icon"><i class="fas fa-clock"></i></div>
                        <div class="feature-text">
                            <h4>24/7 Support</h4>
                            <p>Emergency repairs available around the clock.</p>
                        </div>
                    </div>
                    <div class="about-feature">
                        <div class="feature-icon"><i class="fas fa-tools"></i></div>
                        <div class="feature-text">
                            <h4>Expert Repairs</h4>
                            <p>Qualified professionals for all maintenance needs.</p>
                        </div>
                    </div>
                    <div class="about-feature">
                        <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="feature-text">
                            <h4>Fast Response</h4>
                            <p>We aim to resolve most issues within 24 hours.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about-image-container">
                <div class="image-wrapper">
                    <img src="assets/images/Fix.png" alt="Maintenance Team at Work" class="about-image">
                    <div class="experience-card">
                        <span class="exp-number">20+</span>
                        <span class="exp-text">Years of Excellence</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Services List (No Cards) -->
        <div class="services-list-container">
            <h2 class="section-title">Our Maintenance Services</h2>
            <div class="services-simple-list">
                <div class="service-item"><i class="fas fa-pipe"></i> Plumbing</div>
                <div class="service-item"><i class="fas fa-lightbulb"></i> Bulb Replacement</div>
                <div class="service-item"><i class="fas fa-window-maximize"></i> Window Handle</div>
                <div class="service-item"><i class="fas fa-door-open"></i> Door Handle</div>
                <div class="service-item"><i class="fas fa-wifi"></i> WiFi Problems</div>
                <div class="service-item"><i class="fas fa-tint-slash"></i> Leakage Problems</div>
                <div class="service-item"><i class="fas fa-fire-burner"></i> Stove Problem</div>
                <div class="service-item"><i class="fas fa-wind"></i> HVAC</div>
                <div class="service-item"><i class="fas fa-plug"></i> Plugs</div>
                <div class="service-item"><i class="fas fa-bed"></i> Bed</div>
                <div class="service-item"><i class="fas fa-chair"></i> Chair</div>
                <div class="service-item"><i class="fas fa-paint-roller"></i> Painting</div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer Area -->
    <footer class="footer">
        <div class="footer-services">
            <div class="footer-column">
                <div class="footer-column-title">Our Services</div>
                <p class="footer-column-text">We offer a wide range of premium maintenance services to keep your property in perfect condition.</p>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Maintenance Services</div>
                <a href="index.php?page=about">Bulb Replacement</a>
                <a href="index.php?page=about">Window Handle</a>
                <a href="index.php?page=about">Door Handle</a>
                <a href="index.php?page=about">WiFi Problems</a>
                <a href="index.php?page=about">Leakage Problems</a>
                <a href="index.php?page=about">Stove Problem</a>
                <a href="index.php?page=about">HVAC</a>
                <a href="index.php?page=about">Painting</a>
            </div>
            <div class="footer-column">
                <div class="footer-column-title">Quick Navigation</div>
                <a href="index.php">Home</a>
                <a href="student/login.php">Log In</a>
                <a href="index.php?page=about">About</a>
                <a href="student/register.php">Sign Up</a>
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
                <img src="assets/images/logo.png" alt="MainRes Logo" class="footer-logo">
                <p>&copy; 2026 MainRes Maintenance. All rights reserved.</p>
            </div>
            <div class="footer-links">
                <!-- E-mail Hyperlink -->
                <a href="mailto:vut@mainresmaintenance.com" class="footer-link">vut@mainresmaintenance.com</a>
            </div>
        </div>
    </footer>
</body>
</html>
