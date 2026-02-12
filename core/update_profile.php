<?php
session_start();
include('../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_user = $_POST["id_user"];
    $nama = $_POST["nama"];

    // Handle file upload jika ada perubahan gambar profil
    if ($_FILES["foto"]["error"] == 0) {
        $uploadDir = '../core/foto_profile/';  // Sesuaikan dengan direktori upload yang diinginkan
         // Membuat direktori jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
        $fotoFileName = uniqid() . '_' . basename($_FILES["foto"]["name"]);;
        move_uploaded_file($_FILES["foto"]["tmp_name"], $fotoFileName);

        // Perbarui data profil dengan gambar baru
        $query = "UPDATE mst_user SET nama = '$nama', image = '$fotoFileName' WHERE id_user = $id_user";
    } else {
        // Perbarui data profil tanpa mengubah gambar
        $query = "UPDATE mst_user SET nama = '$nama' WHERE id_user = $id_user";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = "Profile updated successfully.";
        $_SESSION['notification_type'] = "success";
        header("Location: ../profile.php"); // Ganti dengan halaman profil yang benar
        exit();
    } else {
        $_SESSION['notification'] = "Error updating profile: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        header("Location: ../profile.php"); // Ganti dengan halaman profil yang benar
        exit();
    }
}
?>
