<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_submenu = $_POST["id_submenu"]; // Mendapatkan ID submenu dari form

    // Query untuk menghapus data submenu berdasarkan ID
    $query = "DELETE FROM user_sub_menu WHERE id = $id_submenu";

    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = "Submenu deleted successfully.";
        $_SESSION['notification_type'] = "success";
        // Jika berhasil dihapus, tidak perlu redirect ke halaman menu karena kita melakukan operasi AJAX
        // header("Location: ../../menu-submenu.php");
        exit();
    } else {
        $_SESSION['notification'] = "Error deleting submenu: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        // Jika terjadi kesalahan, tidak perlu redirect ke halaman menu karena kita melakukan operasi AJAX
        // header("Location: ../../menu-submenu.php");
        exit();
    }
}
?>
