<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_role = $_POST["id"]; // Mendapatkan ID dari AJAX request

    // Query untuk delete data role berdasarkan ID
    $query = "DELETE FROM user_role WHERE id = $id_role";

    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = "Role deleted successfully.";
        $_SESSION['notification_type'] = "success";
        exit(); // Exit to avoid additional output
    } else {
        $_SESSION['notification'] = "Error deleting role: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        exit(); // Exit to avoid additional output
    }
}
?>
