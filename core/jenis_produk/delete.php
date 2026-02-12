<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username'])) { exit(); }

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM `jenis_produk` WHERE `id` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Data deleted successfully.";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Error: " . $stmt->error;
        $_SESSION['notification_type'] = "error";
    }
    $stmt->close();
}
header("Location: ../../jenis_produk.php");
exit();
?>