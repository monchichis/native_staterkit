<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    if (empty($_POST['nama_produk'])) {
        $errors[] = "Nama produk is required.";
    }
    if (empty($_POST['id_jenis'])) {
        $errors[] = "Id jenis is required.";
    }
    if (empty($_POST['stok'])) {
        $errors[] = "Stok is required.";
    }
    if (!empty($_POST['stok']) && !is_numeric($_POST['stok'])) {
        $errors[] = "Stok must be a number.";
    }
    if (empty($_POST['harga'])) {
        $errors[] = "Harga is required.";
    }
    // Clean Rupiah format for harga
    $_POST['harga'] = preg_replace('/[^0-9]/', '', $_POST['harga']);
    if (!empty($_POST['harga']) && !is_numeric($_POST['harga'])) {
        $errors[] = "Harga must be a valid Rupiah amount.";
    }
    if (!empty($errors)) {
        $_SESSION['notification'] = implode('<br>', $errors);
        $_SESSION['notification_type'] = "error";
        header("Location: ../../produk.php");
        exit();
    }
    $nama_produk = $_POST['nama_produk'];
    $id_jenis = $_POST['id_jenis'];
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];

    $query = "INSERT INTO `produk` (nama_produk, id_jenis, stok, harga) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $nama_produk, $id_jenis, $stok, $harga);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Data added successfully.";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Error: " . $stmt->error;
        $_SESSION['notification_type'] = "error";
    }
    $stmt->close();
    header("Location: ../../produk.php");
    exit();
}
?>