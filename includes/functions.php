<?php
/**
 * Unified Maintenance Domain Functions
 * Central source of truth operations for issues, stats, and synchronization
 * IMPORTANT: Used across Student, Maintenance Staff, and Admin interfaces
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/json.php';

/**
 * Fetch all maintenance requests, ordered by newest first
 * @return array
 */
function get_all_issues() {
    $issues = json_read(MAINTENANCE_REQUESTS_FILE);
    if (!is_array($issues)) {
        return [];
    }
    
    usort($issues, function($a, $b) {
        $tA = strtotime($a['created_at'] ?? '1970-01-01');
        $tB = strtotime($b['created_at'] ?? '1970-01-01');
        return $tB - $tA;
    });
    
    return array_values($issues);
}

/**
 * Fetch a single issue by its unique ID
 * @param string $issue_id
 * @return array|null
 */
function get_issue_by_id($issue_id) {
    if (empty($issue_id)) {
        return null;
    }
    return json_find_by_id(MAINTENANCE_REQUESTS_FILE, $issue_id);
}

/**
 * Fetch all issues reported by a specific student, newest first
 * @param string $student_id
 * @return array
 */
function get_student_issues($student_id) {
    if (empty($student_id)) {
        return [];
    }
    $all = get_all_issues();
    $student_issues = array_filter($all, function($issue) use ($student_id) {
        return isset($issue['student_id']) && $issue['student_id'] === $student_id;
    });
    return array_values($student_issues);
}

/**
 * Fetch staff directory lookup [id => staff_data]
 * @return array
 */
function get_staff_lookup() {
    $staff_list = json_read(STAFF_FILE);
    $lookup = [];
    if (is_array($staff_list)) {
        foreach ($staff_list as $member) {
            if (isset($member['id'])) {
                $lookup[$member['id']] = [
                    'id' => $member['id'],
                    'name' => $member['name'] ?? 'Staff',
                    'email' => $member['email'] ?? '',
                    'department' => $member['department'] ?? 'General Maintenance'
                ];
            }
        }
    }
    return $lookup;
}

/**
 * Calculate aggregate counts for issues
 * @param string|null $student_id If provided, stats are scoped to this student
 * @return array
 */
function get_issue_stats($student_id = null) {
    $issues = $student_id ? get_student_issues($student_id) : get_all_issues();
    
    $stats = [
        'total' => count($issues),
        'pending' => 0,
        'assigned' => 0,
        'in_progress' => 0,
        'completed' => 0
    ];
    
    foreach ($issues as $issue) {
        $status = $issue['status'] ?? STATUS_PENDING;
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }
    
    return $stats;
}

/**
 * Create a new maintenance issue in the central source of truth
 * @param array $data Issue input fields
 * @param string|null $user_id Creator user ID for activity log
 * @return array|false Created issue array on success, false on failure
 */
function create_maintenance_issue($data, $user_id = null) {
    $id = !empty($data['id']) ? $data['id'] : generate_id('issue_');
    $now = date('Y-m-d H:i:s');
    
    $issue_record = [
        'id' => $id,
        'student_id' => $data['student_id'] ?? '',
        'student_name' => $data['student_name'] ?? '',
        'student_no' => $data['student_no'] ?? '',
        'email' => $data['email'] ?? '',
        'residence' => $data['residence'] ?? '',
        'block' => $data['block'] ?? '',
        'room' => $data['room'] ?? '',
        'issue' => $data['issue'] ?? 'General Issue',
        'category' => $data['category'] ?? ($data['issue'] ?? 'General'),
        'priority' => !empty($data['priority']) ? strtolower($data['priority']) : 'medium',
        'gender' => $data['gender'] ?? '',
        'description' => $data['description'] ?? '',
        'image' => $data['image'] ?? '',
        'status' => STATUS_PENDING,
        'assigned_to' => null,
        'maintenance_notes' => '',
        'created_at' => $now,
        'updated_at' => $now
    ];
    
    if (json_add(MAINTENANCE_REQUESTS_FILE, $issue_record)) {
        if ($user_id && function_exists('log_activity')) {
            log_activity(
                $user_id,
                ROLE_STUDENT,
                'report_issue',
                "Reported issue: " . ($issue_record['issue']),
                $id
            );
        }
        return $issue_record;
    }
    
    return false;
}

/**
 * Update an existing maintenance issue in the central source of truth
 * Preserves all unmodified fields and updates timestamp
 * @param string $issue_id
 * @param array $updates Key-value updates
 * @param string|null $user_id User performing update
 * @param string|null $user_role Role of user
 * @param string|null $action Activity log action
 * @param string|null $description Activity log description
 * @return array|false Updated issue array or false on failure
 */
function update_maintenance_issue($issue_id, $updates, $user_id = null, $user_role = null, $action = null, $description = null) {
    $existing = get_issue_by_id($issue_id);
    if (!$existing) {
        return false;
    }
    
    // Disallow overriding ID and creation date
    unset($updates['id']);
    unset($updates['created_at']);
    $updates['updated_at'] = date('Y-m-d H:i:s');
    
    // Merge updates preserving all previous details
    $merged = array_merge($existing, $updates);
    
    if (json_update(MAINTENANCE_REQUESTS_FILE, $issue_id, $updates)) {
        if ($user_id && $user_role && $action && function_exists('log_activity')) {
            $desc = $description ?? "Updated issue $issue_id";
            log_activity($user_id, $user_role, $action, $desc, $issue_id);
        }
        return $merged;
    }
    
    return false;
}

/**
 * User-friendly display label for an issue status
 * @param string $status
 * @return string
 */
function get_status_label($status) {
    switch ($status) {
        case STATUS_PENDING:
            return 'Pending';
        case STATUS_ASSIGNED:
            return 'Assigned';
        case STATUS_IN_PROGRESS:
            return 'In Progress';
        case STATUS_COMPLETED:
            return 'Fixed';
        default:
            return ucfirst(str_replace('_', ' ', (string)$status));
    }
}
