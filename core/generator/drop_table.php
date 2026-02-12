<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    die("Access Denied");
}

if (isset($_GET['table'])) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
    
    // Prevent dropping system tables if necessary, though SuperAdmin power implies ability.
    // For safety, maybe prevent dropping mst_user?
    if ($table === 'mst_user') {
        $_SESSION['notification'] = "Cannot drop system table 'mst_user'.";
        $_SESSION['notification_type'] = "error";
    } else {
        $sql = "DROP TABLE `$table`";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['notification'] = "Table `$table` dropped successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            $_SESSION['notification'] = "Error dropping table: " . $conn->error;
            $_SESSION['notification_type'] = "error";
        }
    }
}

header("Location: ../../generator_table.php");
exit();
?>
