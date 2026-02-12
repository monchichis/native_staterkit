<?php
session_start();
include('../../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = $_POST["database"];
    
    // --- 1. Get list of CRUD generated files from sidebar ---
    $sidebarPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'sidebar.php';
    $rootPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
    $corePath = $rootPath . DIRECTORY_SEPARATOR . 'core';
    
    // Find CRUD generated files by scanning core folder for non-system directories
    $systemFolders = ['generator', 'menu', 'permission', 'role', 'uninstall', 'user']; // System folders to keep
    $deletedFiles = [];
    $deletedFolders = [];
    
    // Scan core directory for CRUD generated folders
    if (is_dir($corePath)) {
        $folders = scandir($corePath);
        foreach ($folders as $folder) {
            if ($folder === '.' || $folder === '..') continue;
            $folderPath = $corePath . DIRECTORY_SEPARATOR . $folder;
            
            // Skip system folders and files
            if (!is_dir($folderPath) || in_array($folder, $systemFolders)) continue;
            
            // Check if this looks like a CRUD folder (has create.php, update.php, delete.php)
            if (file_exists($folderPath . DIRECTORY_SEPARATOR . 'create.php') || 
                file_exists($folderPath . DIRECTORY_SEPARATOR . 'update.php') ||
                file_exists($folderPath . DIRECTORY_SEPARATOR . 'delete.php')) {
                
                // Delete the corresponding view file (e.g., produk.php)
                $viewFile = $rootPath . DIRECTORY_SEPARATOR . $folder . '.php';
                if (file_exists($viewFile)) {
                    unlink($viewFile);
                    $deletedFiles[] = $folder . '.php';
                }
                
                // Delete the core folder recursively
                deleteDirectory($folderPath);
                $deletedFolders[] = 'core/' . $folder;
            }
        }
    }
    
    // --- 2. Delete connection.php ---
    $connectionFilePath = $rootPath . DIRECTORY_SEPARATOR . 'connection' . DIRECTORY_SEPARATOR . 'connection.php';
    if (file_exists($connectionFilePath)) {
        unlink($connectionFilePath);
    }

    // --- 3. Drop database ---
    $dropDatabaseQuery = "DROP DATABASE `$database`";
    
    if (mysqli_query($conn, $dropDatabaseQuery)) {
        $_SESSION['notification'] = "Uninstall successful. Deleted " . count($deletedFiles) . " view files and " . count($deletedFolders) . " core folders.";
        $_SESSION['notification_type'] = "success";
        header("Location: ../../index.php");
        exit();
    } else {
        $_SESSION['notification'] = "Database drop failed: " . mysqli_error($conn) . ". But deleted " . count($deletedFiles) . " files.";
        $_SESSION['notification_type'] = "warning";
        header("Location: ../../index.php");
        exit();
    }
    
} else {
    echo 'Invalid request method.';
}

/**
 * Recursively delete a directory
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}
?>
