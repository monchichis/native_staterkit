<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_role = $_POST["id"]; // Mendapatkan ID dari form
    $nama_role = $_POST["role"]; // Mendapatkan nama role dari form

    // Query untuk update data role berdasarkan ID
    $query = "UPDATE user_role SET role = '$nama_role' WHERE id = $id_role";

    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = "Role updated successfully.";
        $_SESSION['notification_type'] = "success";
        header("Location: ../../list_role.php"); // Redirect ke halaman role setelah update
        exit();
    } else {
        $_SESSION['notification'] = "Error updating role: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        header("Location: ../../list_role.php"); // Redirect ke halaman role dengan pesan error
        exit();
    }
}
?>
