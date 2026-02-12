<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    die("Access Denied");
}

if (isset($_GET['table']) && isset($_GET['column'])) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['column']);
    
    $sql = "ALTER TABLE `$table` DROP COLUMN `$column`";

    try {
        if ($conn->query($sql) === TRUE) {
            $_SESSION['notification'] = "Column `$column` dropped successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['notification'] = "Error dropping column: " . $e->getMessage();
        $_SESSION['notification_type'] = "error";
    }
    
    header("Location: ../../generator_table_structure.php?table=" . $table);
} else {
    header("Location: ../../generator_table.php");
}
exit();
?>
