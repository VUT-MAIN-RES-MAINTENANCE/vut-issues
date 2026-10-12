<?php
/**
 * Unified Maintenance Issues API
 * Handles live synchronization polling and async issue actions across roles
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure user is authenticated
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required.'
    ]);
    exit;
}

$user_id = get_current_user_id();
$user_role = get_current_user_role();
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET: Poll or fetch issues & stats
if ($method === 'GET') {
    // Role-based scoping: Students only see their own; Staff and Admin see all
    if ($user_role === ROLE_STUDENT) {
        $issues = get_student_issues($user_id);
        $stats = get_issue_stats($user_id);
    } else {
        $issues = get_all_issues();
        $stats = get_issue_stats(null);
    }

    $staff_lookup = get_staff_lookup();
    $data_hash = file_exists(MAINTENANCE_REQUESTS_FILE) ? md5_file(MAINTENANCE_REQUESTS_FILE) : '';

    echo json_encode([
        'success' => true,
        'role' => $user_role,
        'user_id' => $user_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'data_hash' => $data_hash,
        'stats' => $stats,
        'staff_lookup' => $staff_lookup,
        'issues' => $issues
    ]);
    exit;
}

// Handle POST: Actions (start_working, mark_fixed, assign, update_status)
if ($method === 'POST') {
    // Parse input from JSON body or POST form data
    $input = [];
    $raw_body = file_get_contents('php://input');
    if (!empty($raw_body)) {
        $decoded = json_decode($raw_body, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }
    if (empty($input)) {
        $input = $_POST;
    }

    $action = $input['action'] ?? '';
    $issue_id = $input['issue_id'] ?? '';

    if (empty($action) || empty($issue_id)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing action or issue_id parameter.'
        ]);
        exit;
    }

    $issue = get_issue_by_id($issue_id);
    if (!$issue) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Maintenance issue not found.'
        ]);
        exit;
    }

    $updates = [];
    $activity_desc = '';

    switch ($action) {
        case 'start_working':
            if ($user_role !== ROLE_STAFF && $user_role !== ROLE_ADMIN) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Only maintenance staff and admins can start work.']);
                exit;
            }

            $updates['status'] = STATUS_IN_PROGRESS;
            // If currently unassigned and staff clicks start working, assign to this staff
            if (empty($issue['assigned_to']) && $user_role === ROLE_STAFF) {
                $updates['assigned_to'] = $user_id;
            }
            if (isset($input['maintenance_notes']) && trim($input['maintenance_notes']) !== '') {
                $updates['maintenance_notes'] = trim($input['maintenance_notes']);
            }
            $activity_desc = "Started working on issue: " . ($issue['issue'] ?? $issue_id);
            break;

        case 'mark_fixed':
            if ($user_role !== ROLE_STAFF && $user_role !== ROLE_ADMIN) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Only maintenance staff and admins can mark issues as fixed.']);
                exit;
            }

            $notes = trim($input['maintenance_notes'] ?? '');
            $updates['status'] = STATUS_COMPLETED;
            if ($notes !== '') {
                $updates['maintenance_notes'] = $notes;
            }
            $activity_desc = "Marked issue as fixed: " . ($issue['issue'] ?? $issue_id) . ($notes ? " (Notes: $notes)" : "");
            break;

        case 'assign':
            if ($user_role !== ROLE_ADMIN) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Only administrators can assign issues.']);
                exit;
            }

            $staff_id = $input['staff_id'] ?? '';
            if (empty($staff_id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Staff ID is required for assignment.']);
                exit;
            }

            $updates['assigned_to'] = $staff_id;
            $updates['status'] = STATUS_ASSIGNED;
            $activity_desc = "Assigned issue to staff ID: $staff_id";
            break;

        case 'update_status':
            if ($user_role !== ROLE_STAFF && $user_role !== ROLE_ADMIN) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized to update status.']);
                exit;
            }

            $new_status = $input['status'] ?? '';
            $valid_statuses = [STATUS_PENDING, STATUS_ASSIGNED, STATUS_IN_PROGRESS, STATUS_COMPLETED];
            if (!in_array($new_status, $valid_statuses)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid status provided.']);
                exit;
            }

            $updates['status'] = $new_status;
            if (isset($input['maintenance_notes'])) {
                $updates['maintenance_notes'] = trim($input['maintenance_notes']);
            }
            if (isset($input['assigned_to'])) {
                $updates['assigned_to'] = $input['assigned_to'] ?: null;
            }
            $activity_desc = "Updated issue status to $new_status";
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unsupported action.']);
            exit;
    }

    $updated_issue = update_maintenance_issue(
        $issue_id,
        $updates,
        $user_id,
        $user_role,
        $action,
        $activity_desc
    );

    if ($updated_issue) {
        $stats = ($user_role === ROLE_STUDENT) ? get_issue_stats($user_id) : get_issue_stats(null);
        echo json_encode([
            'success' => true,
            'message' => 'Issue updated successfully.',
            'issue' => $updated_issue,
            'stats' => $stats
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save issue updates.'
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
