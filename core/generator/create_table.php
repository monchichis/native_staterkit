<?php
session_start();
include('../../connection/connection.php');

// Check Session & SuperAdmin
if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    $_SESSION['notification'] = "Unauthorized access.";
    $_SESSION['notification_type'] = "error";
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name']);
    
    if (empty($tableName)) {
        $_SESSION['notification'] = "Table Name is required.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_table.php");
        exit();
    }

    $columns = $_POST['columns']; // Array of columns
    
    if (empty($columns)) {
        $_SESSION['notification'] = "At least one column is required.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_table.php");
        exit();
    }

    $sqlParts = [];
    $primaryKeys = [];

    foreach ($columns as $col) {
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', $col['name']);
        $type = $col['type'];
        $length = isset($col['length']) && $col['length'] !== '' ? "({$col['length']})" : "";
        $default = isset($col['default_value']) && $col['default_value'] !== '' ? "DEFAULT '{$col['default_value']}'" : "";
        $nullable = isset($col['is_null']) ? "NULL" : "NOT NULL";
        $autoIncrement = isset($col['is_ai']) ? "AUTO_INCREMENT" : "";

        if (empty($name)) continue;

        // Validation for types that don't need length
        if (in_array(strtoupper($type), ['TEXT', 'DATE', 'DATETIME', 'TIMESTAMP', 'BOOLEAN'])) {
            $length = "";
        }

        $line = "`$name` $type$length $nullable $default $autoIncrement";
        $sqlParts[] = $line;

        if (isset($col['is_pk'])) {
            $primaryKeys[] = "`$name`";
        }
    }

    if (empty($sqlParts)) {
        $_SESSION['notification'] = "Invalid column definitions.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_table.php");
        exit();
    }

    $sql = "CREATE TABLE `$tableName` (" . implode(", ", $sqlParts);

    if (!empty($primaryKeys)) {
        $sql .= ", PRIMARY KEY (" . implode(", ", $primaryKeys) . ")";
    }

    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // Execute via Conn (mysqli)
    try {
        if ($conn->query($sql) === TRUE) {
            $_SESSION['notification'] = "Table `$tableName` created successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['notification'] = "Error creating table: " . $e->getMessage();
        $_SESSION['notification_type'] = "error";
    }

    header("Location: ../../generator_table.php");
    exit();
}
?>
