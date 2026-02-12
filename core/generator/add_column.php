<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    die("Access Denied");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name']);
    
    if (empty($table)) {
        $_SESSION['notification'] = "Invalid table.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_table.php");
        exit();
    }

    $columns = $_POST['columns']; // Array of columns

    if(empty($columns)) {
        $_SESSION['notification'] = "No columns provided.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_table_structure.php?table=" . $table);
        exit();
    }

    // Build massive ALTER TABLE statement or loop?
    // Doing one by one is safer for error reporting, but massive is atomic-ish.
    // Let's do one single ALTER query with multiple ADD clauses.

    $addClauses = [];

    foreach ($columns as $col) {
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', $col['name']);
        if (empty($name)) continue;

        $type = $col['type'];
        $length = isset($col['length']) && $col['length'] !== '' ? "({$col['length']})" : "";
        $default = isset($col['default_value']) && $col['default_value'] !== '' ? "DEFAULT '{$col['default_value']}'" : "";
        $nullable = isset($col['is_null']) ? "NULL" : "NOT NULL";
        // AI is rarely added via Alter Add, but if requested, it usually requires KEY.
        // For simplicity in Add Column context, we might skip AI or assume user knows to add Index later
        // But to be consistent with Create, let's include it.
        $autoIncrement = isset($col['is_ai']) ? "AUTO_INCREMENT PRIMARY KEY" : ""; // Careful: adding PK to existing table might fail if PK exists.
        
        // Simplification: In Add Column to existing table, adding AI/PK is risky/complex. 
        // Let's stick to basic structure but match the backend Create Create logic as much as safe.
        // If user selects AI here, it might error if PK exists. That's "Consistent" behavior (DB returns error).
        
        if (isset($col['is_ai'])) {
            $autoIncrement = "AUTO_INCREMENT"; 
            // Note: will likely fail unless we also add PRIMARY KEY or UNIQUE INDEX in the same statement, 
            // and table doesn't have one.
            // For now, let's just append it and let MySQL throw the error if invalid.
        }

        // Validation for types
        if (in_array(strtoupper($type), ['TEXT', 'DATE', 'DATETIME', 'TIMESTAMP', 'BOOLEAN'])) {
            $length = "";
        }

        $addClauses[] = "ADD `$name` $type$length $nullable $default $autoIncrement";
    }

    if (empty($addClauses)) {
        header("Location: ../../generator_table_structure.php?table=" . $table);
        exit();
    }

    $sql = "ALTER TABLE `$table` " . implode(", ", $addClauses);

    try {
        if ($conn->query($sql) === TRUE) {
            $_SESSION['notification'] = "Columns added successfully.";
            $_SESSION['notification_type'] = "success";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['notification'] = "Error adding columns: " . $e->getMessage();
        $_SESSION['notification_type'] = "error";
    }
}

header("Location: ../../generator_table_structure.php?table=" . $table);
exit();
?>
