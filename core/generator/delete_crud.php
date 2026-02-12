<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    die("Access Denied");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $historyId = intval($_POST['history_id'] ?? 0);
    $dropTable = isset($_POST['drop_table']) && $_POST['drop_table'] === '1';
    
    if ($historyId <= 0) {
        $_SESSION['notification'] = "Invalid history ID.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_crud.php");
        exit();
    }
    
    // Get history record
    $stmt = $conn->prepare("SELECT * FROM crud_history WHERE id = ?");
    $stmt->bind_param("i", $historyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result->fetch_assoc();
    $stmt->close();
    
    if (!$history) {
        $_SESSION['notification'] = "CRUD history not found.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_crud.php");
        exit();
    }
    
    $tableName = $history['table_name'];
    $fileName = $history['file_name'];
    $moduleTitle = $history['module_title'];
    
    $errors = [];
    
    // 1. Delete view file
    $viewFile = "../../" . $fileName . ".php";
    if (file_exists($viewFile)) {
        if (!unlink($viewFile)) {
            $errors[] = "Failed to delete $fileName.php";
        }
    }
    
    // 2. Delete core folder recursively
    $coreFolder = "../../core/" . $fileName;
    if (is_dir($coreFolder)) {
        if (!deleteDirectory($coreFolder)) {
            $errors[] = "Failed to delete core/$fileName folder";
        }
    }
    
    // 3. Remove from sidebar
    $sidebarFile = "../../template/sidebar.php";
    if (file_exists($sidebarFile)) {
        $sidebarContent = file_get_contents($sidebarFile);
        
        // Pattern to match the menu item
        $pattern = '/\s*<li>\s*<a href="' . preg_quote($fileName, '/') . '\.php">.*?<\/a>\s*<\/li>\s*/s';
        $sidebarContent = preg_replace($pattern, "\n", $sidebarContent);
        
        file_put_contents($sidebarFile, $sidebarContent);
    }
    
    // 4. Optionally drop the table
    if ($dropTable) {
        $dropResult = $conn->query("DROP TABLE IF EXISTS `$tableName`");
        if (!$dropResult) {
            $errors[] = "Failed to drop table $tableName: " . $conn->error;
        }
    }
    
    // 5. Delete history record
    $deleteStmt = $conn->prepare("DELETE FROM crud_history WHERE id = ?");
    $deleteStmt->bind_param("i", $historyId);
    $deleteStmt->execute();
    $deleteStmt->close();
    
    if (empty($errors)) {
        $msg = "CRUD '$moduleTitle' deleted successfully.";
        if ($dropTable) {
            $msg .= " Table '$tableName' was also dropped.";
        }
        $_SESSION['notification'] = $msg;
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Partial deletion: " . implode(", ", $errors);
        $_SESSION['notification_type'] = "warning";
    }
    
    header("Location: ../../generator_crud.php");
    exit();
}

/**
 * Recursively delete a directory
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) return true;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            if (!deleteDirectory($path)) return false;
        } else {
            if (!unlink($path)) return false;
        }
    }
    return rmdir($dir);
}
?>
