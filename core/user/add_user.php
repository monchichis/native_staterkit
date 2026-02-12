<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and Validate
    $nama = trim($_POST["nama"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $role_id = intval($_POST["role"] ?? 0);
    $raw_password = $_POST["password"] ?? '';

    if (empty($nama) || empty($email) || empty($raw_password) || $role_id <= 0) {
        $_SESSION['notification'] = "Error: All fields are required.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../user.php");
        exit();
    }

    // Get Role Name safely
    // Assuming $db is initialized in connection.php
    $roleData = $db->fetchRow("SELECT role FROM user_role WHERE id = ?", [$role_id]);
    
    if (!$roleData) {
         $_SESSION['notification'] = "Error: Invalid Role selected.";
         $_SESSION['notification_type'] = "error";
         header("Location: ../../user.php");
         exit();
    }
    
    $level = $roleData['role'];
    $nik = uniqid();
    $password = password_hash($raw_password, PASSWORD_DEFAULT);
    $date_created = date("Y-m-d");
    $image = "default.png";
    $is_active = 1;
    
    // Insert User
    $insertData = [
        'nama' => $nama,
        'nik' => $nik,
        'email' => $email,
        'password' => $password,
        'level' => $level,
        'date_created' => $date_created,
        'image' => $image,
        'is_active' => $is_active,
        'role_id' => $role_id
    ];

    try {
        $db->insert('mst_user', $insertData);
        $_SESSION['notification'] = "User added successfully.";
        $_SESSION['notification_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['notification'] = "Error adding user: " . $e->getMessage();
        $_SESSION['notification_type'] = "error";
    }

    header("Location: ../../user.php");
    exit();
}
?>
