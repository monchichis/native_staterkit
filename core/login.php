<?php
session_start();
// Include the connection file which initializes $db instance of Database class
include('../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input (though prepared statements handle the SQL part)
    $email = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['notification'] = "Error: Please fill in all fields.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../index.php");
        exit();
    }
    
    // Secure query using prepared statements via Database class
    // assuming $db is initialized in connection.php
    $query = "SELECT * FROM mst_user WHERE email = ?";
    
    // Ensure $db exists, otherwise fall back or error out (connection.php should handle this)
    if (isset($db)) {
        $user = $db->fetchRow($query, [$email]);
    } else {
        // Fallback for legacy if simple mysqli_query was expected but we want security
        // Ideally connection.php always gives us $db
        die("Database connection not initialized properly.");
    }

    if ($user && password_verify($password, $user['password'])) {
        // Login Success
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['username'] = $user['nama'];
        $_SESSION['level'] = $user['level'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role_id'] = $user['role_id']; // Required for Permission system
        $_SESSION['status'] = "login";
        $_SESSION['image'] = $user['image'];
        $_SESSION['notification'] = "Welcome, " . $_SESSION['username'] . "! You are logged in as " . $_SESSION['level'] . ".";
        $_SESSION['notification_type'] = "info";
        
        // Removed includes that cause "headers already sent" error
        header("Location: ../dashboard.php");
        exit();
    } else {
        // Login Failed
        $_SESSION['notification'] = "Error: Username or password is incorrect.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../index.php");
        exit();
    }
}
?>
