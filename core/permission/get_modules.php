<?php
/**
 * Get available modules for permission assignment
 * Returns JSON with module names and display names
 */
session_start();
header('Content-Type: application/json');

include('../../connection/connection.php');

// Security check
if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$modules = [];

// Check if connection is successful
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get from user_sub_menu (actual pages with URLs)
$result = $conn->query("SELECT DISTINCT LOWER(REPLACE(title, ' ', '_')) as module_name, title as display_name FROM user_sub_menu WHERE is_active = 1 ORDER BY title");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $modules[] = [
            'name' => $row['module_name'],
            'display' => $row['display_name']
        ];
    }
}

// Get standalone modules from user_menu that don't have submenus
$result = $conn->query("SELECT LOWER(REPLACE(menu, ' ', '_')) as module_name, menu as display_name FROM user_menu um WHERE NOT EXISTS (SELECT 1 FROM user_sub_menu usm WHERE usm.menu_id = um.id) ORDER BY menu");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $modules[] = [
            'name' => $row['module_name'],
            'display' => $row['display_name']
        ];
    }
}

// Get modules from CRUD history
$crudHistoryExists = $conn->query("SHOW TABLES LIKE 'crud_history'");
if ($crudHistoryExists && $crudHistoryExists->num_rows > 0) {
    $result = $conn->query("SELECT file_name as module_name, module_title as display_name FROM crud_history ORDER BY module_title");
    if ($result) {
        $existingModules = array_column($modules, 'name');
        while ($row = $result->fetch_assoc()) {
            if (!in_array($row['module_name'], $existingModules)) {
                $modules[] = [
                    'name' => $row['module_name'],
                    'display' => $row['display_name']
                ];
            }
        }
    }
}

// Also add any custom modules from role_permissions that might not be in menus
$result = $conn->query("SELECT DISTINCT module_name FROM role_permissions ORDER BY module_name");
if ($result) {
    $existingModules = array_column($modules, 'name');
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['module_name'], $existingModules)) {
            $modules[] = [
                'name' => $row['module_name'],
                'display' => ucwords(str_replace('_', ' ', $row['module_name']))
            ];
        }
    }
}

echo json_encode(['success' => true, 'modules' => $modules]);

