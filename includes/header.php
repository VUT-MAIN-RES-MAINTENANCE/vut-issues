<?php
// Header include file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MainRes Maintenance - Premium maintenance services at your fingertips. Report and manage your services easily.">
    <meta name="keywords" content="Maintenance, Services, MainRes, Property Management">
    <meta name="author" content="Student Project">
    <title>MainRes Maintenance - Home</title>
    
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/styles.css">
    
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
