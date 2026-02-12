<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mendapatkan data dari formulir
    $role_ids = $_POST["role_id"];
    $menu_ids = $_POST["menu_id"];

    // Proses insert ke tabel user_access_menu
    foreach ($role_ids as $role_id) {
        foreach ($menu_ids as $menu_id) {
            $query = "INSERT INTO user_access_menu (role_id, menu_id) VALUES ('$role_id', '$menu_id')";
            mysqli_query($conn, $query);
        }
    }
    
    $_SESSION['notification'] = "Access menu added successfully.";
    $_SESSION['notification_type'] = "success";
    header("Location: ../../access_menu.php");
    exit();
} else {
    $_SESSION['notification'] = "Invalid request.";
    $_SESSION['notification_type'] = "error";
    header("Location: ../../access_menu.php");
    exit();
}
?>
