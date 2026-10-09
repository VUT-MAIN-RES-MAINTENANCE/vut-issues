<?php
/**
 * Configuration File
 * Shared configuration settings for the VUT Residence Maintenance System
 * IMPORTANT: Changes to this file affect ALL interfaces (Student, Maintenance, Admin)
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set timezone to South Africa
    date_default_timezone_set('Africa/Johannesburg');
    
    // Configure session to persist for 30 days
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60, // 30 days in seconds
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => '' // Empty for maximum compatibility across directories
    ]);
    
    // Configure session garbage collection
    ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Session started more than 30 minutes ago
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('PICTURES_PATH', BASE_PATH . '/pictures');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// JSON file paths
define('STUDENTS_FILE', DATA_PATH . '/students.json');
define('STAFF_FILE', DATA_PATH . '/staff.json');
define('ADMINS_FILE', DATA_PATH . '/admins.json');
define('MAINTENANCE_REQUESTS_FILE', DATA_PATH . '/maintenance_requests.json');
define('ACTIVITIES_FILE', DATA_PATH . '/activities.json');
define('SETTINGS_FILE', DATA_PATH . '/settings.json');

// Picture folder paths
define('ISSUES_PICTURE_PATH', PICTURES_PATH . '/issues');

// User roles
define('ROLE_STUDENT', 'student');
define('ROLE_STAFF', 'staff');
define('ROLE_ADMIN', 'admin');

// Session keys
define('SESSION_USER_ID', 'user_id');
define('SESSION_USER_ROLE', 'user_role');
define('SESSION_USER_EMAIL', 'user_email');
define('SESSION_USER_NAME', 'user_name');

// Issue statuses
define('STATUS_PENDING', 'pending');
define('STATUS_ASSIGNED', 'assigned');
define('STATUS_IN_PROGRESS', 'in_progress');
define('STATUS_COMPLETED', 'completed');

// Allowed image file types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB

