<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_menu = $_POST["id_menu"]; // Mendapatkan ID submenu dari form
    $menu = $_POST["menu"]; // Mendapatkan ID menu dari form
    $icon = $_POST["icon"]; // Mendapatkan judul submenu dari form

    // Query untuk update data submenu berdasarkan ID
    $query = "UPDATE user_menu SET menu = '$menu', icon = '$icon' WHERE id = $id_menu";

    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = "Menu updated successfully.";
        $_SESSION['notification_type'] = "success";
        header("Location: ../../menu-submenu.php"); // Redirect ke halaman menu setelah update
        exit();
    } else {
        $_SESSION['notification'] = "Error updating menu: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        header("Location: ../../menu-submenu.php"); // Redirect ke halaman menu dengan pesan error
        exit();
    }
}
?>
