<?php
session_start(); // Panggil ini di awal file jika belum dipanggil

include('../../connection/connection.php'); // Sesuaikan dengan path ke connection.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST["role"];

    // Query untuk menambahkan data ke tabel role
    $insert_query = "INSERT INTO user_role VALUES (NULL,'$role')";
    // var_dump($insert_query);
    if (mysqli_query($conn, $insert_query)) {
        // Jika berhasil menambahkan data, set notifikasi dan redirect ke halaman yang diinginkan
        $_SESSION['notification'] = "Role berhasil ditambahkan.";
        $_SESSION['notification_type'] = "success";
        header("Location: ../../list_role.php"); // Ganti dengan halaman yang benar
        exit();
    } else {
        // Jika terjadi kesalahan, set notifikasi error
        $_SESSION['notification'] = "Error: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        header("Location: ../../list_role.php"); // Ganti dengan halaman yang benar
        exit();
    }
}
?>
