<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Connection
    include('../connection/connection.php'); 
    
    // Check if connection is valid
    if (!$conn || !isset($database_name)) {
         $_SESSION['notification'] = "Database connection invalid.";
         $_SESSION['notification_type'] = "error";
         header("Location: ../index.php");
         exit();
    }

    // 2. File Validation
    if (!isset($_FILES["sql_file"]) || $_FILES["sql_file"]["error"] != 0) {
         $_SESSION['notification'] = "No file uploaded or upload error.";
         $_SESSION['notification_type'] = "error";
         header("Location: ../index.php");
         exit();
    }

    $sqlFile = $_FILES["sql_file"];
    $fileExtension = pathinfo($sqlFile['name'], PATHINFO_EXTENSION);

    if (strtolower($fileExtension) !== 'sql') {
        $_SESSION['notification'] = "File must be .sql format.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../index.php");
        exit();
    }

    // 3. Import Process
    // Use the database name from connection.php (don't trust POST if we want to enforce the setup)
    // Or if allowing switch, validate input. For now, let's use the one in connection.php
    $targetDb = $database_name;

    // Read content
    $sqlContent = file_get_contents($sqlFile["tmp_name"]);

    // Execute Multi Query
    // Note: Database class might not support multi_query directly via simple query() method if it uses prepared statements for everything.
    // So we use the mysqli object directly ($conn).
    
    mysqli_report(MYSQLI_REPORT_OFF); // Disable strict reporting for import to handle minor SQL errors gracefully if needed
    
    if (mysqli_multi_query($conn, $sqlContent)) {
        // Consume all results to clear the buffer
        do {
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
        } while (mysqli_more_results($conn) && mysqli_next_result($conn));
        
        $_SESSION['notification'] = "Tables imported successfully to $targetDb.";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Import Error: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
    }
}

// Redirect
header("Location: ../index.php");
exit();
?>
