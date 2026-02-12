<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    $query = "INSERT INTO `jenis_produk` (nama_jenis) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nama_jenis);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Data added successfully.";
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