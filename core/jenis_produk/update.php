<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username'])) { exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $errors = [];
    if (empty($_POST['nama_jenis'])) {
        $errors[] = "Nama jenis is required.";
    }
    if (!empty($errors)) {
        $_SESSION['notification'] = implode('<br>', $errors);
        $_SESSION['notification_type'] = "error";
        header("Location: ../../jenis_produk.php");
        exit();
    }
    $nama_jenis = $_POST['nama_jenis'];

    $query = "UPDATE `jenis_produk` SET `nama_jenis` = ? WHERE `id` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $nama_jenis, $id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Data updated successfully.";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Error: " . $stmt->error;
        $_SESSION['notification_type'] = "error";
    }
    $stmt->close();
    header("Location: ../../jenis_produk.php");
    exit();
}
?>