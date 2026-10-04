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
    <?php if ($page == 'about'): ?>
    <link rel="stylesheet" href="assets/css/about.css?v=<?php echo filemtime(__DIR__ . '/assets/css/about.css'); ?>">
    <?php endif; ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="home-page<?php echo $page == 'about' ? ' about-page' : ''; ?>">
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
    <main class="about-main">
        <section class="about-hero" aria-labelledby="about-title">
            <div class="about-hero-copy" data-reveal>
                <span class="about-eyebrow"><span></span> VUT MAIN RESIDENCE · VAAL</span>
                <h1 id="about-title">A better stay<br>starts with <em>care.</em></h1>
                <p>We keep student residences working, safe, and comfortable, with a maintenance team that is ready when you need us.</p>
                <div class="about-actions">
                    <a class="about-button about-button-primary" href="student/report-issue.php">Report an issue <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    <a class="about-text-link" href="#about-services">Explore our services <i class="fas fa-arrow-down" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="about-hero-visual" data-reveal>
                <img src="assets/images/Fix.png" alt="A maintenance professional repairing a residence window">
                <div class="about-photo-caption"><span>HERE FOR THE EVERYDAY FIXES</span><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></div>
                <div class="about-years"><strong>20<span>+</span></strong><span>years caring<br>for VUT residences</span></div>
            </div>
            <div class="about-hero-index" aria-hidden="true">01 <span></span> 03</div>
        </section>

        <section class="about-story" aria-labelledby="about-story-title">
            <div class="about-story-heading" data-reveal>
                <span class="about-eyebrow">OUR BACKGROUND</span>
                <h2 id="about-story-title">A place to live<br>should feel like <em>home.</em></h2>
            </div>
            <div class="about-story-copy" data-reveal>
                <p>Based in Vanderbijlpark, Gauteng, VUT MainRes Maintenance supports the people and places that make residence life possible. Our team brings more than 20 years of experience across plumbing, electrical work, building repairs, and urgent maintenance.</p>
                <p>From the first report to the final repair, we make it easier for students to get help and get back to what matters.</p>
                <a class="about-inline-link" href="https://vut.ac.za/" target="_blank" rel="noopener">Discover VUT <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
            </div>
        </section>

        <section class="about-principles" aria-label="Our vision and mission">
            <article class="about-principle" data-reveal>
                <span class="about-principle-number">01 / VISION</span>
                <i class="fas fa-eye" aria-hidden="true"></i>
                <h3>Room to thrive.</h3>
                <p>A safe, comfortable, worry-free home for every VUT student.</p>
            </article>
            <article class="about-principle" data-reveal>
                <span class="about-principle-number">02 / MISSION</span>
                <i class="fas fa-bullseye" aria-hidden="true"></i>
                <h3>Care that follows through.</h3>
                <p>We fix residence issues promptly and properly, so students can focus on studying.</p>
            </article>
            <article class="about-principle about-principle-note" data-reveal>
                <span class="about-principle-number">THE WAY WE WORK</span>
                <p>Clear communication.<br>Skilled hands.<br>Respect for your space.</p>
                <a href="student/report-issue.php" aria-label="Report a maintenance issue"><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </article>
        </section>

        <section class="about-services" id="about-services" aria-labelledby="about-services-title">
            <div class="about-services-heading" data-reveal>
                <div><span class="about-eyebrow">PRACTICAL HELP, RIGHT AT HOME</span><h2 id="about-services-title">What we take care of.</h2></div>
                <a class="about-inline-link" href="student/report-issue.php">Request a repair <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="about-service-list">
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>01</span><i class="fas fa-faucet" aria-hidden="true"></i><strong>Plumbing &amp; leaks</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>02</span><i class="fas fa-bolt" aria-hidden="true"></i><strong>Electrical &amp; plugs</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>03</span><i class="fas fa-lightbulb" aria-hidden="true"></i><strong>Lighting</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>04</span><i class="fas fa-door-open" aria-hidden="true"></i><strong>Doors &amp; windows</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>05</span><i class="fas fa-wifi" aria-hidden="true"></i><strong>WiFi problems</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>06</span><i class="fas fa-fire-burner" aria-hidden="true"></i><strong>Stoves &amp; appliances</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>07</span><i class="fas fa-wind" aria-hidden="true"></i><strong>Heating &amp; cooling</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>08</span><i class="fas fa-bed" aria-hidden="true"></i><strong>Furniture &amp; fittings</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                <a href="student/report-issue.php" class="about-service-item" data-reveal><span>09</span><i class="fas fa-paint-roller" aria-hidden="true"></i><strong>Painting &amp; repairs</strong><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
            </div>
            <div class="about-response-row" data-reveal>
                <div class="about-response-mark"><i class="fas fa-headset" aria-hidden="true"></i></div>
                <div><span>WHEN SOMETHING NEEDS ATTENTION</span><h3>We’re here to help, day or night.</h3></div>
                <p>For urgent maintenance or everyday repairs, tell us what’s wrong and our team will take it from there.</p>
                <a class="about-button about-button-dark" href="student/report-issue.php">Get support <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </section>
    </main>
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
    <?php if ($page == 'about'): ?>
    <script src="assets/js/about.js?v=<?php echo filemtime(__DIR__ . '/assets/js/about.js'); ?>" defer></script>
    <?php endif; ?>
</body>
</html>
