<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_submenu = $_POST["id"]; // Mendapatkan ID submenu dari form
    $menu_id = $_POST["menu_id"]; // Mendapatkan ID menu dari form
    $title = $_POST["title"]; // Mendapatkan judul submenu dari form

    // Query untuk update data submenu berdasarkan ID
    $query = "UPDATE user_sub_menu SET title = '$title' WHERE id = $id_submenu";

    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = "Submenu updated successfully.";
        $_SESSION['notification_type'] = "success";
        header("Location: ../../menu-submenu.php"); // Redirect ke halaman menu setelah update
        exit();
    } else {
        $_SESSION['notification'] = "Error updating submenu: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        header("Location: ../../menu-submenu.php"); // Redirect ke halaman menu dengan pesan error
        exit();
    }
}
?>
