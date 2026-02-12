<?php
/**
 * Save permissions for a specific role
 * Expects POST with role_id and permissions array
 */
session_start();
header('Content-Type: application/json');

include('../../connection/connection.php');

// Security check
if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

$roleId = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;
$permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

if (!$roleId) {
    echo json_encode(['error' => 'Invalid role ID']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete existing permissions for this role
    $stmt = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->bind_param("i", $roleId);
    $stmt->execute();
    $stmt->close();

    // Insert new permissions
    $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, module_name, can_view, can_create, can_update, can_delete) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($permissions as $module => $perms) {
        $canView = isset($perms['view']) && $perms['view'] ? 1 : 0;
        $canCreate = isset($perms['create']) && $perms['create'] ? 1 : 0;
        $canUpdate = isset($perms['update']) && $perms['update'] ? 1 : 0;
        $canDelete = isset($perms['delete']) && $perms['delete'] ? 1 : 0;
        
        // Only insert if at least one permission is granted
        if ($canView || $canCreate || $canUpdate || $canDelete) {
            $stmt->bind_param("isiiii", $roleId, $module, $canView, $canCreate, $canUpdate, $canDelete);
            $stmt->execute();
        }
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Permissions saved successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Failed to save permissions: ' . $e->getMessage()]);
}
