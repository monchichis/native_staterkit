<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (isset($_GET['table'])) {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
    $columns = [];
    
    $result = $conn->query("SHOW FULL COLUMNS FROM `$table`");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($columns);
    exit();
}

if (isset($_GET['list_all_tables'])) {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($tables);
    exit();
}
?>
