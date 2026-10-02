<?php
/**
 * Authentication Helper Functions
 * Reusable functions for user authentication, sessions, and access control
 * IMPORTANT: Changes to this file affect ALL interfaces (Student, Maintenance, Admin)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/json.php';

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION[SESSION_USER_ID]) && isset($_SESSION[SESSION_USER_ROLE]);
}

/**
 * Get current logged-in user ID
 * @return string|null User ID or null if not logged in
 */
function get_current_user_id() {
    return $_SESSION[SESSION_USER_ID] ?? null;
}

/**
 * Get current logged-in user role
 * @return string|null User role or null if not logged in
 */
function get_current_user_role() {
    return $_SESSION[SESSION_USER_ROLE] ?? null;
}

/**
 * Get current logged-in user email
 * @return string|null User email or null if not logged in
 */
function get_current_user_email() {
    return $_SESSION[SESSION_USER_EMAIL] ?? null;
}

/**
 * Get current logged-in user name
 * @return string|null User name or null if not logged in
 */
function get_current_user_name() {
    return $_SESSION[SESSION_USER_NAME] ?? null;
}

/**
 * Check if current user has a specific role
 * @param string $role Role to check
 * @return bool True if user has the role, false otherwise
 */
function has_role($role) {
    return get_current_user_role() === $role;
}

/**
 * Require user to be logged in, redirect to login if not
 * @param string $login_url URL to redirect to if not logged in
 */
function require_login($login_url) {
    if (!is_logged_in()) {
        header('Location: ' . $login_url);
        exit;
    }
}

/**
 * Require specific role, redirect if user doesn't have it
 * @param string $role Required role
 * @param string $redirect_url URL to redirect to if role doesn't match
 */
function require_role($role, $redirect_url) {
    require_login($redirect_url);
    if (!has_role($role)) {
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * Login user by setting session variables
 * @param string $user_id User ID
 * @param string $email User email
 * @param string $name User name
 * @param string $role User role
 */
function login_user($user_id, $email, $name, $role) {
    $_SESSION[SESSION_USER_ID] = $user_id;
    $_SESSION[SESSION_USER_EMAIL] = $email;
    $_SESSION[SESSION_USER_NAME] = $name;
    $_SESSION[SESSION_USER_ROLE] = $role;
}

/**
 * Logout current user
 */
function logout_user() {
    $_SESSION = [];
    session_destroy();
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Password hash
 * @return bool True if password matches, false otherwise
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Hash password
 * @param string $password Plain text password
 * @return string Password hash
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Get user record by email from appropriate file based on role
 * @param string $email User email
 * @param string $role User role (student, staff, admin)
 * @return array|null User record or null if not found
 */
function get_user_by_email($email, $role) {
    $file = null;
    
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
            return null;
    }
    
    return json_find($file, 'email', $email);
}

/**
 * Get user record by ID from appropriate file based on role
 * @param string $user_id User ID
 * @param string $role User role (student, staff, admin)
 * @return array|null User record or null if not found
 */
function get_user_by_id($user_id, $role) {
    $file = null;
    
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
            return null;
    }
    
    return json_find_by_id($file, $user_id);
}

/**
 * Authenticate user with email and password
 * @param string $email User email
 * @param string $password Plain text password
 * @param string $role User role (student, staff, admin)
 * @return array|null User record if authentication successful, null otherwise
 */
function authenticate_user($email, $password, $role) {
    $user = get_user_by_email($email, $role);
    
    if ($user && isset($user['password']) && verify_password($password, $user['password'])) {
        return $user;
    }
    
    return null;
}

/**
 * Log an activity to the activities.json file
 * @param string $user_id User ID who performed the action
 * @param string $role User role (student, staff, admin)
 * @param string $action Action performed
 * @param string $description Description of the action
 * @param string|null $related_issue Optional related issue ID
 * @return bool True on success, false on failure
 */
function log_activity($user_id, $role, $action, $description, $related_issue = null) {
    $activities = json_read(ACTIVITIES_FILE);
    
    $activity = [
        'id' => generate_id('activity_'),
        'user_id' => $user_id,
        'role' => $role,
        'action' => $action,
        'description' => $description,
        'related_issue' => $related_issue,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $activities[] = $activity;
    return json_write(ACTIVITIES_FILE, $activities);
}

