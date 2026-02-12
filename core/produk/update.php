<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username'])) { exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
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

    $query = "UPDATE `produk` SET `nama_produk` = ?, `id_jenis` = ?, `stok` = ?, `harga` = ? WHERE `id` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $nama_produk, $id_jenis, $stok, $harga, $id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Data updated successfully.";
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