<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_menu = $_POST["id_menu"]; // Mendapatkan ID submenu dari form

    // Query untuk menghapus data submenu berdasarkan ID
    $query = "DELETE FROM user_menu WHERE id = $id_menu";

    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = "Menu deleted successfully.";
        $_SESSION['notification_type'] = "success";
        // Jika berhasil dihapus, tidak perlu redirect ke halaman menu karena kita melakukan operasi AJAX
        // header("Location: ../../menu-submenu.php");
        exit();
    } else {
        $_SESSION['notification'] = "Error deleting menu: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        // Jika terjadi kesalahan, tidak perlu redirect ke halaman menu karena kita melakukan operasi AJAX
        // header("Location: ../../menu-submenu.php");
        exit();
    }
}
?>
