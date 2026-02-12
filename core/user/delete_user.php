<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_user = intval($_POST["id_user"] ?? 0);

    if ($id_user <= 0) {
         echo json_encode(array('status' => 'error', 'message' => 'Invalid User ID.'));
         exit();
    }

    try {
         $db->delete('mst_user', "id_user = ?", [$id_user]);
         $_SESSION['notification'] = "User deleted successfully.";
         $_SESSION['notification_type'] = "success";
         echo json_encode(array('status' => 'success', 'message' => 'User deleted successfully.'));
    } catch (Exception $e) {
         $_SESSION['notification'] = "Error deleting: " . $e->getMessage();
         $_SESSION['notification_type'] = "error";
         echo json_encode(array('status' => 'error', 'message' => 'Error deleting user.'));
    }
    exit();
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request method.'));
    exit();
}
?>
