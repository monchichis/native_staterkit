<?php
session_start();
include('../../connection/connection.php');

header('Content-Type: application/json');

// Check if user has permission (Superadmin only or appropriate role)
// Adjust this check based on your specific role management system
if (!isset($_SESSION['level'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_user = $_POST['id_user'] ?? '';

    if (empty($id_user)) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        exit;
    }

    // Generate random 8 character password
    $random_password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
    
    // Hash the password using BCRYPT (as per add_user.php)
    $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);

    // Update database
    // Using mysqli directly as per pattern seen in other files, assuming $conn is mysqli object
    // Or use $db helper if available. Based on user.php review, $conn is used.
    
    $stmt = $conn->prepare("UPDATE mst_user SET password = ? WHERE id_user = ?");
    $stmt->bind_param("si", $hashed_password, $id_user);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Password reset successful',
            'new_password' => $random_password
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to update password: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
