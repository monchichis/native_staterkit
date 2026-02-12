<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_user = intval($_POST["id_user"] ?? 0);
    $nama = trim($_POST["nama"] ?? '');
    $email = trim($_POST["email"] ?? '');

    if ($id_user <= 0 || empty($nama) || empty($email)) {
        $_SESSION['notification'] = "Error: Invalid input data.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../user.php");
        exit();
    }
    
    // Update User
    try {
        $updateData = [
            'nama' => $nama,
            'email' => $email
        ];
        $whereClause = "id_user = ?";
        $whereParams = [$id_user];
        
        $db->update('mst_user', $updateData, $whereClause, $whereParams);
        
        $_SESSION['notification'] = "User updated successfully.";
        $_SESSION['notification_type'] = "success";
    } catch (Exception $e) {
         $_SESSION['notification'] = "Error updating: " . $e->getMessage();
         $_SESSION['notification_type'] = "error";
    }
    
    header("Location: ../../user.php");
    exit();
}
?>
