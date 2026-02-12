<?php
include 'connection/connection.php';

// Create crud_history table
$sql1 = "CREATE TABLE IF NOT EXISTS `crud_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `table_name` varchar(128) NOT NULL,
  `module_title` varchar(128) NOT NULL,
  `file_name` varchar(128) NOT NULL,
  `columns_config` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

// Create role_permissions table
$sql2 = "CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module_name` varchar(128) NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_update` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_module` (`role_id`, `module_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$errors = [];
$success = [];

if($conn->query($sql1)) {
    $success[] = "Table crud_history created/verified successfully!";
} else {
    $errors[] = "Error creating crud_history: " . $conn->error;
}

if($conn->query($sql2)) {
    $success[] = "Table role_permissions created/verified successfully!";
} else {
    $errors[] = "Error creating role_permissions: " . $conn->error;
}

echo "<h2>Migration Results</h2>";
if (!empty($success)) {
    echo "<div style='color:green;'>";
    foreach ($success as $msg) {
        echo "<p>✅ $msg</p>";
    }
    echo "</div>";
}
if (!empty($errors)) {
    echo "<div style='color:red;'>";
    foreach ($errors as $msg) {
        echo "<p>❌ $msg</p>";
    }
    echo "</div>";
}
echo "<p><a href='generator_crud.php'>Go to CRUD Generator</a> | <a href='role_permissions.php'>Go to Role Permissions</a></p>";
?>
