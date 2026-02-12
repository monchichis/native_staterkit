<?php
session_start(); // Panggil ini di awal file jika belum dipanggil

include('../../connection/connection.php'); // Sesuaikan dengan path ke connection.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $menu_id = $_POST["menu_id"] ? $_POST["menu_id"] : '';
    $submenu = $_POST["submenu"] ? $_POST["submenu"] : '';

    // Validasi: Pastikan semua input terisi
    if (empty($menu_id) || empty($submenu)) {
        // Jika ada input yang kosong, set notifikasi error
        $_SESSION['notification'] = "Semua kolom harus diisi.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../menu-submenu.php"); // Ganti dengan halaman yang benar
        exit();
    }
    
    // Query untuk menambahkan data ke tabel user_menu
    $insert_query = "INSERT INTO user_sub_menu VALUES (NULL,'$menu_id','$submenu',1)";
    // var_dump($insert_query);
    // die();
    if (mysqli_query($conn, $insert_query)) {
        // Jika berhasil menambahkan data, set notifikasi dan redirect ke halaman yang diinginkan
        $_SESSION['notification'] = "Submenu berhasil ditambahkan.";
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
