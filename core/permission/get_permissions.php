<?php
/**
 * Get permissions for a specific role
 * Returns JSON with module permissions
 */
session_start();
header('Content-Type: application/json');

include('../../connection/connection.php');

// Security check
if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$roleId = isset($_GET['role_id']) ? intval($_GET['role_id']) : 0;

if (!$roleId) {
    echo json_encode(['error' => 'Invalid role ID']);
    exit();
}

$permissions = [];

$stmt = $conn->prepare("SELECT module_name, can_view, can_create, can_update, can_delete FROM role_permissions WHERE role_id = ?");
$stmt->bind_param("i", $roleId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $permissions[$row['module_name']] = [
        'view' => (bool)$row['can_view'],
        'create' => (bool)$row['can_create'],
        'update' => (bool)$row['can_update'],
        'delete' => (bool)$row['can_delete']
    ];
}

$stmt->close();

echo json_encode(['success' => true, 'permissions' => $permissions]);
