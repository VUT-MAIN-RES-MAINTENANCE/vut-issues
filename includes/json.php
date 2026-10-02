<?php
/**
 * JSON Helper Functions
 * Reusable functions for reading, writing, and manipulating JSON data files
 * IMPORTANT: Changes to this file affect ALL interfaces (Student, Maintenance, Admin)
 */

require_once __DIR__ . '/config.php';

/**
 * Read JSON file and return decoded data
 * @param string $file Path to JSON file
 * @return array Decoded JSON data as array, or empty array if file doesn't exist/invalid
 */
function json_read($file) {
    if (!file_exists($file)) {
        return [];
    }
    
    $content = file_get_contents($file);
    if ($content === false) {
        return [];
    }
    
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }
    
    return is_array($data) ? $data : [];
}

/**
 * Write data to JSON file
 * @param string $file Path to JSON file
 * @param array $data Data to write
 * @return bool True on success, false on failure
 */
function json_write($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    
    return file_put_contents($file, $json, LOCK_EX) !== false;
}

/**
 * Find a record by field value
 * @param string $file Path to JSON file
 * @param string $field Field name to search
 * @param mixed $value Value to match
 * @return array|null Matching record or null if not found
 */
function json_find($file, $field, $value) {
    $data = json_read($file);
    
    foreach ($data as $record) {
        if (isset($record[$field]) && $record[$field] == $value) {
            return $record;
        }
    }
    
    return null;
}

/**
 * Find a record by ID
 * @param string $file Path to JSON file
 * @param string $id ID to search for
 * @return array|null Matching record or null if not found
 */
function json_find_by_id($file, $id) {
    return json_find($file, 'id', $id);
}

/**
 * Add a new record to JSON file
 * @param string $file Path to JSON file
 * @param array $record Record to add (must have 'id' field)
 * @return bool True on success, false on failure
 */
function json_add($file, $record) {
    if (!isset($record['id'])) {
        return false;
    }
    
    $data = json_read($file);
    
    // Check if ID already exists
    foreach ($data as $existing) {
        if (isset($existing['id']) && $existing['id'] == $record['id']) {
            return false;
        }
    }
    
    $data[] = $record;
    return json_write($file, $data);
}

/**
 * Update an existing record in JSON file
 * @param string $file Path to JSON file
 * @param string $id ID of record to update
 * @param array $updates Key-value pairs to update
 * @return bool True on success, false on failure
 */
function json_update($file, $id, $updates) {
    $data = json_read($file);
    $found = false;
    
    foreach ($data as &$record) {
        if (isset($record['id']) && $record['id'] == $id) {
            $record = array_merge($record, $updates);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        return false;
    }
    
    return json_write($file, $data);
}

/**
 * Delete a record from JSON file
 * @param string $file Path to JSON file
 * @param string $id ID of record to delete
 * @return bool True on success, false on failure
 */
function json_delete($file, $id) {
    $data = json_read($file);
    $original_count = count($data);
    
    $data = array_filter($data, function($record) use ($id) {
        return !isset($record['id']) || $record['id'] != $id;
    });
    
    if (count($data) === $original_count) {
        return false; // Record not found
    }
    
    return json_write($file, array_values($data));
}

/**
 * Generate a unique ID
 * @param string $prefix Optional prefix for the ID
 * @return string Unique ID
 */
function generate_id($prefix = '') {
    return $prefix . uniqid() . bin2hex(random_bytes(4));
}
