<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    die("Access Denied");
}

/* Helper function to remove Auto Increment if exists */
function removeAutoIncrement($conn, $table) {
    // Find column with auto_increment
    $result = $conn->query("SHOW COLUMNS FROM `$table` WHERE Extra LIKE '%auto_increment%'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $colName = $row['Field'];
        $colType = $row['Type'];
        // Assume NOT NULL for PK columns slightly safer to preserve
        // Usually AI columns are PKs and thus NOT NULL.
        
        $sql = "ALTER TABLE `$table` MODIFY `$colName` $colType NOT NULL";
        if (!$conn->query($sql)) {
            throw new Exception("Failed to remove Auto Increment from `$colName`: " . $conn->error);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name']);
    $action = $_POST['action']; // 'set' or 'drop'

    if (empty($table)) {
        $_SESSION['notification'] = "Invalid table.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_table.php");
        exit();
    }

    try {
        if ($action === 'drop') {
            // 1. Remove AI if exists
            removeAutoIncrement($conn, $table);
            
            // 2. Drop PK
            $sql = "ALTER TABLE `$table` DROP PRIMARY KEY";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['notification'] = "Primary Key dropped successfully.";
                $_SESSION['notification_type'] = "success";
            } else {
                throw new Exception($conn->error);
            }

        } elseif ($action === 'set') {
            // 1. Remove AI if exists (Crucial step to avoid 1075 error)
            removeAutoIncrement($conn, $table);

            // 2. Drop existing PK (Ignore error if none)
            try {
               $conn->query("ALTER TABLE `$table` DROP PRIMARY KEY"); 
            } catch (Exception $e) {
                // Ignore, maybe no PK existed
            }

            // 3. Add New PK
            $columns = $_POST['pk_columns'] ?? [];
            if (empty($columns)) {
                throw new Exception("No columns selected for Primary Key.");
            }

            // Sanitize column names
            $sanitizedCols = array_map(function($col) {
                return "`" . preg_replace('/[^a-zA-Z0-9_]/', '', $col) . "`";
            }, $columns);
            
            $colString = implode(", ", $sanitizedCols);
            $sql = "ALTER TABLE `$table` ADD PRIMARY KEY ($colString)";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['notification'] = "Primary Key updated successfully.";
                $_SESSION['notification_type'] = "success";
            } else {
                throw new Exception($conn->error);
            }
        }
    } catch (Exception $e) {
        $_SESSION['notification'] = "Error: " . $e->getMessage();
        $_SESSION['notification_type'] = "error";
    }
}

header("Location: ../../generator_table_structure.php?table=" . $table);
exit();
?>
