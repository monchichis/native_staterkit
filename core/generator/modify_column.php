<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    die("Access Denied");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name']);
    $oldName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['old_name']);
    $newName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['name']);
    $definition = $_POST['definition']; // User manually types "VARCHAR(255) NOT NULL"
    
    // Construct SQL
    // ALTER TABLE `table` CHANGE `old` `new` DEFINITION
    
    $sql = "ALTER TABLE `$table` CHANGE `$oldName` `$newName` $definition";

    try {
        if ($conn->query($sql) === TRUE) {
            $_SESSION['notification'] = "Column updated successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['notification'] = "Error updating column: " . $e->getMessage();
        $_SESSION['notification_type'] = "error";
    }
}

header("Location: ../../generator_table_structure.php?table=" . $table);
exit();
?>
