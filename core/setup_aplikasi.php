<?php
session_start(); // Panggil ini di awal file index.php
include('../connection/connection.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $namaAplikasi = $_POST["nama_aplikasi"];
    $alamat = $_POST["alamat"];
    $telp = $_POST["telp"];
    $namaDeveloper = $_POST["nama_developer"];

    // Proses upload logo (jika diperlukan)
    $uploadDir = '../core/logo_aplikasi/';
    $logoFileName = '';

    if ($_FILES["logo"]["error"] == 0) {
        $allowedExtensions = ['jpg', 'png'];
        $maxFileSize = 1 * 1024 * 1024; // 1 MB

        $logoFileName = uniqid() . '_' . basename($_FILES["logo"]["name"]);
        $logoFilePath = $uploadDir . $logoFileName;

        $fileExtension = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
        $fileSize = $_FILES["logo"]["size"];

        // Validasi ekstensi file
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            $_SESSION['notification'] = "Ekstensi file tidak valid. Harap upload file dengan ekstensi JPG atau PNG.";
            $_SESSION['notification_type'] = "error";
            header("Location: ../index.php");
            exit();
        }

        // Validasi ukuran file
        if ($fileSize > $maxFileSize) {
            $_SESSION['notification'] = "Ukuran file terlalu besar. Maksimal 1 MB.";
            $_SESSION['notification_type'] = "error";
            header("Location: ../index.php");
            exit();
        }

        move_uploaded_file($_FILES["logo"]["tmp_name"], $logoFilePath);
    }

    // Masukkan data ke database
    $query = "INSERT INTO tbl_aplikasi (nama_aplikasi, alamat, telp, nama_developer, logo) 
              VALUES ('$namaAplikasi', '$alamat', '$telp', '$namaDeveloper', '$logoFileName')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['notification'] = "Aplikasi berhasil di-setup.";
        $_SESSION['notification_type'] = "success";
        header("Location: ../index.php");
    } else {
        $_SESSION['notification'] = "Error: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        header("Location: ../index.php");
    }

    // Tutup koneksi
    mysqli_close($conn);
}
?>
