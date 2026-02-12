<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $menu = isset($_POST["menu"]) ? $_POST["menu"] : '';
    $icon = isset($_POST["icon"]) ? $_POST["icon"] : '';

    // Validasi: Pastikan semua input terisi
    if (empty($menu) || empty($icon)) {
        // Jika ada input yang kosong, set notifikasi error
        $_SESSION['notification'] = "Semua kolom harus diisi.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../menu-submenu.php"); // Ganti dengan halaman yang benar
        exit();
    }

    // Query untuk menambahkan data ke tabel user_menu
    $insert_query = "INSERT INTO user_menu VALUES (NULL, '$menu', '$icon')";

    if (mysqli_query($conn, $insert_query)) {
        // Jika berhasil menambahkan data, set notifikasi dan redirect ke halaman yang diinginkan
        $_SESSION['notification'] = "Menu berhasil ditambahkan.";
        $_SESSION['notification_type'] = "success";
        header("Location: ../../menu-submenu.php"); // Ganti dengan halaman yang benar
        exit();
    } else {
        // Jika terjadi kesalahan, set notifikasi error
        $_SESSION['notification'] = "Error: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        header("Location: ../../menu-submenu.php"); // Ganti dengan halaman yang benar
        exit();
    }
}
?>
