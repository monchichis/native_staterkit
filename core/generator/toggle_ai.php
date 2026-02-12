<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    die("Access Denied");
}

if (isset($_GET['table']) && isset($_GET['column']) && isset($_GET['enable'])) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['column']);
    $enable = (int)$_GET['enable'];

    try {
        // 1. Get Column Type
        $result = $conn->query("SHOW FULL COLUMNS FROM `$table` WHERE Field = '$column'");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $type = $row['Type'];
            $key = $row['Key'];
            $isRel = false; // TODO: preserve logic if needed

            // AI requires a Key. Check if Key exists.
            if ($enable && empty($key)) {
                 $_SESSION['notification'] = "Error: Auto Increment requires a Primary Key or Index on the column.";
                 $_SESSION['notification_type'] = "error";
                 header("Location: ../../generator_table_structure.php?table=$table");
                 exit();
            }

            // Construct Query
            $extra = $enable ? "AUTO_INCREMENT" : "";
            // Preserve NOT NULL if PK? AI implies NOT NULL usually.
            $nullability = "NOT NULL"; 

            $sql = "ALTER TABLE `$table` MODIFY `$column` $type $nullability $extra";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['notification'] = "Auto Increment " . ($enable ? "enabled" : "disabled") . " for $column.";
                $_SESSION['notification_type'] = "success";
            } else {
                throw new Exception($conn->error);
            }

        } else {
            throw new Exception("Column not found.");
        }

    } catch (Exception $e) {
        $_SESSION['notification'] = "Error: " . $e->getMessage();
        $_SESSION['notification_type'] = "error";
    }

    header("Location: ../../generator_table_structure.php?table=$table");
    exit();
}
?>
