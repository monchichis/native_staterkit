<?php
session_start();
include('../../connection/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    die("Access Denied");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tableName = $_POST['table_name'];
    $moduleTitle = $_POST['module_title'];
    $columns = $_POST['cols'] ?? [];
    $addToSidebar = isset($_POST['add_to_sidebar']) ? true : false;
    
    // Generate clean file name from module title (lowercase, underscores)
    // e.g., "Produk" -> "produk", "Data Karyawan" -> "data_karyawan"
    $fileName = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($moduleTitle)));
    $fileName = trim($fileName, '_'); // Remove leading/trailing underscores
    
    // Validation
    if (empty($tableName) || empty($columns)) {
        $_SESSION['notification'] = "Invalid input.";
        $_SESSION['notification_type'] = "error";
        header("Location: ../../generator_crud.php");
        exit();
    }

    // Prepare Folders - use clean fileName for folder too
    $coreDir = "../../core/" . $fileName;
    if (!is_dir($coreDir)) {
        mkdir($coreDir, 0777, true);
    }

    // --- 1. Analyze Columns & Relations ---
    $pk = 'id'; // Default Assumption
    $validCols = []; // Columns to be processed
    $relations = []; // Stores relation info: [col => [ref_table, ref_col]]
    $fileColumns = []; // Stores file upload columns: [col => [type, extensions, max_size]]
    
    // Analyze PK first
    foreach ($columns as $field => $cfg) {
         if ($cfg['key'] === 'PRI') $pk = $field;
    }

    foreach ($columns as $field => $cfg) {
        if (!isset($cfg['include'])) continue;
        
        $validCols[] = $field;
        
        // Relation?
        if (isset($cfg['is_relation']) && !empty($cfg['ref_table']) && !empty($cfg['ref_col'])) {
            $relations[$field] = [
                'table' => $cfg['ref_table'],
                'display' => $cfg['ref_col']
            ];
        }
        
        // File Upload?
        $inputType = $cfg['input_type'] ?? 'text';
        if ($inputType === 'file' || $inputType === 'file_dropzone') {
            $fileColumns[$field] = [
                'type' => $inputType,
                'extensions' => $cfg['file_extensions'] ?? 'jpg,jpeg,png,gif,pdf',
                'max_size' => intval($cfg['file_max_size'] ?? 2),
                'multiple' => isset($cfg['file_multiple']) && $cfg['file_multiple'] == '1'
            ];
        }
    }
    
    // Check if we have any file uploads - will need enctype multipart
    $hasFileUpload = !empty($fileColumns);

    // --- Helper for Validation Logic ---
    function generateValidationCode($cols, $columnsData, $fileName, $pk = 'id') {
        $code = "    \$errors = [];\n";
        foreach ($cols as $field) {
            // Skip primary key from validation (auto-increment)
            if ($field === $pk) continue;
            
            $rules = $columnsData[$field]['validations'] ?? [];
            if (empty($rules)) continue; // No rules

            $label = ucfirst(str_replace('_', ' ', $field));
            
            if (in_array('required', $rules)) {
                 $code .= "    if (empty(\$_POST['$field'])) {\n        \$errors[] = \"$label is required.\";\n    }\n";
            }
            if (in_array('numeric', $rules)) {
                 $code .= "    if (!empty(\$_POST['$field']) && !is_numeric(\$_POST['$field'])) {\n        \$errors[] = \"$label must be a number.\";\n    }\n";
            }
            if (in_array('email', $rules)) {
                 $code .= "    if (!empty(\$_POST['$field']) && !filter_var(\$_POST['$field'], FILTER_VALIDATE_EMAIL)) {\n        \$errors[] = \"Inavlid email format for $label.\";\n    }\n";
            }
            if (in_array('rupiah', $rules)) {
                 // Clean rupiah format (remove Rp, dots, spaces) and validate as numeric
                 $code .= "    // Clean Rupiah format for $field\n";
                 $code .= "    \$_POST['$field'] = preg_replace('/[^0-9]/', '', \$_POST['$field']);\n";
                 $code .= "    if (!empty(\$_POST['$field']) && !is_numeric(\$_POST['$field'])) {\n        \$errors[] = \"$label must be a valid Rupiah amount.\";\n    }\n";
            }
        }
        $code .= "    if (!empty(\$errors)) {\n";
        $code .= "        \$_SESSION['notification'] = implode('<br>', \$errors);\n";
        $code .= "        \$_SESSION['notification_type'] = \"error\";\n";
        $code .= "        header(\"Location: ../../" . $fileName . ".php\");\n";
        $code .= "        exit();\n";
        $code .= "    }\n";
        return $code;
    }

    // --- 2. Generate Core Actions ---

    // 2a. CREATE Action
    $createCode = "<?php\nsession_start();\ninclude('../../connection/connection.php');\n\n";
    $createCode .= "if (!isset(\$_SESSION['username'])) {\n    header('Location: ../../index.php');\n    exit();\n}\n\n";
    
    // Add file upload helper function if needed
    if ($hasFileUpload) {
        $createCode .= "// File Upload Helper (Single)\n";
        $createCode .= "function uploadFile(\$file, \$allowedExt, \$maxSize, \$uploadDir = '../../uploads/') {\n";
        $createCode .= "    if (!is_dir(\$uploadDir)) mkdir(\$uploadDir, 0777, true);\n";
        $createCode .= "    \$fileName = \$file['name'];\n";
        $createCode .= "    \$fileTmp = \$file['tmp_name'];\n";
        $createCode .= "    \$fileSize = \$file['size'];\n";
        $createCode .= "    \$fileError = \$file['error'];\n";
        $createCode .= "    \$fileExt = strtolower(pathinfo(\$fileName, PATHINFO_EXTENSION));\n";
        $createCode .= "    \$allowed = array_map('trim', explode(',', \$allowedExt));\n";
        $createCode .= "    if (\$fileError !== 0) return ['error' => 'Upload error code: ' . \$fileError];\n";
        $createCode .= "    if (!in_array(\$fileExt, \$allowed)) return ['error' => 'Invalid file type. Allowed: ' . \$allowedExt];\n";
        $createCode .= "    if (\$fileSize > \$maxSize * 1024 * 1024) return ['error' => 'File too large. Max: ' . \$maxSize . 'MB'];\n";
        $createCode .= "    \$newName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', \$fileName);\n";
        $createCode .= "    if (move_uploaded_file(\$fileTmp, \$uploadDir . \$newName)) {\n";
        $createCode .= "        return ['success' => true, 'filename' => \$newName];\n";
        $createCode .= "    }\n";
        $createCode .= "    return ['error' => 'Failed to move uploaded file'];\n";
        $createCode .= "}\n\n";
        
        $createCode .= "// File Upload Helper (Multiple)\n";
        $createCode .= "function uploadMultipleFiles(\$files, \$allowedExt, \$maxSize, \$uploadDir = '../../uploads/') {\n";
        $createCode .= "    if (!is_dir(\$uploadDir)) mkdir(\$uploadDir, 0777, true);\n";
        $createCode .= "    \$allowed = array_map('trim', explode(',', \$allowedExt));\n";
        $createCode .= "    \$uploadedFiles = [];\n";
        $createCode .= "    \$fileCount = count(\$files['name']);\n";
        $createCode .= "    for (\$i = 0; \$i < \$fileCount; \$i++) {\n";
        $createCode .= "        if (\$files['error'][\$i] === UPLOAD_ERR_NO_FILE) continue;\n";
        $createCode .= "        if (\$files['error'][\$i] !== 0) return ['error' => 'Upload error on file ' . (\$i+1)];\n";
        $createCode .= "        \$fileExt = strtolower(pathinfo(\$files['name'][\$i], PATHINFO_EXTENSION));\n";
        $createCode .= "        if (!in_array(\$fileExt, \$allowed)) return ['error' => 'Invalid file type: ' . \$files['name'][\$i]];\n";
        $createCode .= "        if (\$files['size'][\$i] > \$maxSize * 1024 * 1024) return ['error' => 'File too large: ' . \$files['name'][\$i]];\n";
        $createCode .= "        \$newName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', \$files['name'][\$i]);\n";
        $createCode .= "        if (move_uploaded_file(\$files['tmp_name'][\$i], \$uploadDir . \$newName)) {\n";
        $createCode .= "            \$uploadedFiles[] = \$newName;\n";
        $createCode .= "        } else {\n";
        $createCode .= "            return ['error' => 'Failed to move: ' . \$files['name'][\$i]];\n";
        $createCode .= "        }\n";
        $createCode .= "    }\n";
        $createCode .= "    return ['success' => true, 'filenames' => json_encode(\$uploadedFiles)];\n";
        $createCode .= "}\n\n";
    }
    
    $createCode .= "if (\$_SERVER['REQUEST_METHOD'] == 'POST') {\n";
    
    // Add Validation
    $createCode .= generateValidationCode($validCols, $columns, $fileName, $pk);
    
    $insertFields = [];
    $paramTypes = "";
    $postVars = [];

    foreach ($validCols as $col) {
        if ($col === $pk && strpos($columns[$col]['key'], 'PRI') !== false) {
             continue; // Skip AI PK
        }
        $insertFields[] = $col;
        $paramTypes .= "s"; // Simplified
        
        // Check if this is a file column
        if (isset($fileColumns[$col])) {
            $ext = $fileColumns[$col]['extensions'];
            $maxSize = $fileColumns[$col]['max_size'];
            $isMultiple = $fileColumns[$col]['multiple'];
            
            $postVars[] = "// Handle file upload for $col";
            $postVars[] = "\$$col = '';";
            
            if ($isMultiple) {
                // Multiple files - stored as JSON
                $postVars[] = "if (isset(\$_FILES['{$col}']) && !empty(\$_FILES['{$col}']['name'][0])) {";
                $postVars[] = "    \$uploadResult = uploadMultipleFiles(\$_FILES['{$col}'], '$ext', $maxSize);";
                $postVars[] = "    if (isset(\$uploadResult['error'])) {";
                $postVars[] = "        \$_SESSION['notification'] = \$uploadResult['error'];";
                $postVars[] = "        \$_SESSION['notification_type'] = 'error';";
                $postVars[] = "        header('Location: ../../$fileName.php');";
                $postVars[] = "        exit();";
                $postVars[] = "    }";
                $postVars[] = "    \$$col = \$uploadResult['filenames'];";
                $postVars[] = "}";
            } else {
                // Single file
                $postVars[] = "if (isset(\$_FILES['$col']) && \$_FILES['$col']['error'] !== UPLOAD_ERR_NO_FILE) {";
                $postVars[] = "    \$uploadResult = uploadFile(\$_FILES['$col'], '$ext', $maxSize);";
                $postVars[] = "    if (isset(\$uploadResult['error'])) {";
                $postVars[] = "        \$_SESSION['notification'] = \$uploadResult['error'];";
                $postVars[] = "        \$_SESSION['notification_type'] = 'error';";
                $postVars[] = "        header('Location: ../../$fileName.php');";
                $postVars[] = "        exit();";
                $postVars[] = "    }";
                $postVars[] = "    \$$col = \$uploadResult['filename'];";
                $postVars[] = "}";
            }
        } else {
            $postVars[] = "\$$col = \$_POST['$col'];";
        }
    }
    
    $createCode .= "    " . implode("\n    ", $postVars) . "\n\n";
    $createCode .= "    \$query = \"INSERT INTO `$tableName` (" . implode(", ", $insertFields) . ") VALUES (" . str_repeat("?, ", count($insertFields) - 1) . "?)\";\n";
    $createCode .= "    \$stmt = \$conn->prepare(\$query);\n";
    $createCode .= "    \$stmt->bind_param(\"$paramTypes\", " . implode(", ", array_map(function($c){return "\$$c";}, $insertFields)) . ");\n\n";
    $createCode .= "    if (\$stmt->execute()) {\n";
    $createCode .= "        \$_SESSION['notification'] = \"Data added successfully.\";\n";
    $createCode .= "        \$_SESSION['notification_type'] = \"success\";\n";
    $createCode .= "    } else {\n";
    $createCode .= "        \$_SESSION['notification'] = \"Error: \" . \$stmt->error;\n";
    $createCode .= "        \$_SESSION['notification_type'] = \"error\";\n";
    $createCode .= "    }\n";
    $createCode .= "    \$stmt->close();\n";
    $createCode .= "    header(\"Location: ../../$fileName.php\");\n    exit();\n}\n?>";
    
    file_put_contents("$coreDir/create.php", $createCode);

    // 2b. UPDATE Action
    $updateCode = "<?php\nsession_start();\ninclude('../../connection/connection.php');\n\n";
    $updateCode .= "if (!isset(\$_SESSION['username'])) { exit(); }\n\n";
    
    // Add file upload helper function if needed
    if ($hasFileUpload) {
        $updateCode .= "// File Upload Helper (Single)\n";
        $updateCode .= "function uploadFile(\$file, \$allowedExt, \$maxSize, \$uploadDir = '../../uploads/') {\n";
        $updateCode .= "    if (!is_dir(\$uploadDir)) mkdir(\$uploadDir, 0777, true);\n";
        $updateCode .= "    \$fileName = \$file['name'];\n";
        $updateCode .= "    \$fileTmp = \$file['tmp_name'];\n";
        $updateCode .= "    \$fileSize = \$file['size'];\n";
        $updateCode .= "    \$fileError = \$file['error'];\n";
        $updateCode .= "    \$fileExt = strtolower(pathinfo(\$fileName, PATHINFO_EXTENSION));\n";
        $updateCode .= "    \$allowed = array_map('trim', explode(',', \$allowedExt));\n";
        $updateCode .= "    if (\$fileError !== 0) return ['error' => 'Upload error code: ' . \$fileError];\n";
        $updateCode .= "    if (!in_array(\$fileExt, \$allowed)) return ['error' => 'Invalid file type. Allowed: ' . \$allowedExt];\n";
        $updateCode .= "    if (\$fileSize > \$maxSize * 1024 * 1024) return ['error' => 'File too large. Max: ' . \$maxSize . 'MB'];\n";
        $updateCode .= "    \$newName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', \$fileName);\n";
        $updateCode .= "    if (move_uploaded_file(\$fileTmp, \$uploadDir . \$newName)) {\n";
        $updateCode .= "        return ['success' => true, 'filename' => \$newName];\n";
        $updateCode .= "    }\n";
        $updateCode .= "    return ['error' => 'Failed to move uploaded file'];\n";
        $updateCode .= "}\n\n";
        
        $updateCode .= "// File Upload Helper (Multiple)\n";
        $updateCode .= "function uploadMultipleFiles(\$files, \$allowedExt, \$maxSize, \$uploadDir = '../../uploads/') {\n";
        $updateCode .= "    if (!is_dir(\$uploadDir)) mkdir(\$uploadDir, 0777, true);\n";
        $updateCode .= "    \$allowed = array_map('trim', explode(',', \$allowedExt));\n";
        $updateCode .= "    \$uploadedFiles = [];\n";
        $updateCode .= "    \$fileCount = count(\$files['name']);\n";
        $updateCode .= "    for (\$i = 0; \$i < \$fileCount; \$i++) {\n";
        $updateCode .= "        if (\$files['error'][\$i] === UPLOAD_ERR_NO_FILE) continue;\n";
        $updateCode .= "        if (\$files['error'][\$i] !== 0) return ['error' => 'Upload error on file ' . (\$i+1)];\n";
        $updateCode .= "        \$fileExt = strtolower(pathinfo(\$files['name'][\$i], PATHINFO_EXTENSION));\n";
        $updateCode .= "        if (!in_array(\$fileExt, \$allowed)) return ['error' => 'Invalid file type: ' . \$files['name'][\$i]];\n";
        $updateCode .= "        if (\$files['size'][\$i] > \$maxSize * 1024 * 1024) return ['error' => 'File too large: ' . \$files['name'][\$i]];\n";
        $updateCode .= "        \$newName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', \$files['name'][\$i]);\n";
        $updateCode .= "        if (move_uploaded_file(\$files['tmp_name'][\$i], \$uploadDir . \$newName)) {\n";
        $updateCode .= "            \$uploadedFiles[] = \$newName;\n";
        $updateCode .= "        } else {\n";
        $updateCode .= "            return ['error' => 'Failed to move: ' . \$files['name'][\$i]];\n";
        $updateCode .= "        }\n";
        $updateCode .= "    }\n";
        $updateCode .= "    return ['success' => true, 'filenames' => json_encode(\$uploadedFiles)];\n";
        $updateCode .= "}\n\n";
    }
    
    $updateCode .= "if (\$_SERVER['REQUEST_METHOD'] == 'POST') {\n";
    $updateCode .= "    \$id = \$_POST['$pk'];\n"; 
    
    // Add Validation
    $updateCode .= generateValidationCode($validCols, $columns, $fileName, $pk);
    
    $updateSets = [];
    $uParamTypes = "";
    $uPostVars = [];
    $uFields = [];
    
    foreach ($validCols as $col) {
        if ($col === $pk) continue;
        $updateSets[] = "`$col` = ?";
        $uParamTypes .= "s";
        $uFields[] = "\$$col";
        
        // Check if this is a file column
        if (isset($fileColumns[$col])) {
            $ext = $fileColumns[$col]['extensions'];
            $maxSize = $fileColumns[$col]['max_size'];
            $isMultiple = $fileColumns[$col]['multiple'];
            
            $uPostVars[] = "// Handle file upload for $col";
            $uPostVars[] = "\$$col = \$_POST['{$col}_existing'] ?? '';";
            
            if ($isMultiple) {
                // Multiple files
                $uPostVars[] = "if (isset(\$_FILES['{$col}']) && !empty(\$_FILES['{$col}']['name'][0])) {";
                $uPostVars[] = "    \$uploadResult = uploadMultipleFiles(\$_FILES['{$col}'], '$ext', $maxSize);";
                $uPostVars[] = "    if (isset(\$uploadResult['error'])) {";
                $uPostVars[] = "        \$_SESSION['notification'] = \$uploadResult['error'];";
                $uPostVars[] = "        \$_SESSION['notification_type'] = 'error';";
                $uPostVars[] = "        header('Location: ../../$fileName.php');";
                $uPostVars[] = "        exit();";
                $uPostVars[] = "    }";
                $uPostVars[] = "    // Merge with existing files";
                $uPostVars[] = "    \$existingFiles = json_decode(\$$col, true) ?: [];";
                $uPostVars[] = "    \$newFiles = json_decode(\$uploadResult['filenames'], true) ?: [];";
                $uPostVars[] = "    \$$col = json_encode(array_merge(\$existingFiles, \$newFiles));";
                $uPostVars[] = "}";
            } else {
                // Single file
                $uPostVars[] = "if (isset(\$_FILES['$col']) && \$_FILES['$col']['error'] !== UPLOAD_ERR_NO_FILE) {";
                $uPostVars[] = "    \$uploadResult = uploadFile(\$_FILES['$col'], '$ext', $maxSize);";
                $uPostVars[] = "    if (isset(\$uploadResult['error'])) {";
                $uPostVars[] = "        \$_SESSION['notification'] = \$uploadResult['error'];";
                $uPostVars[] = "        \$_SESSION['notification_type'] = 'error';";
                $uPostVars[] = "        header('Location: ../../$fileName.php');";
                $uPostVars[] = "        exit();";
                $uPostVars[] = "    }";
                $uPostVars[] = "    \$$col = \$uploadResult['filename'];";
                $uPostVars[] = "}";
            }
        } else {
            $uPostVars[] = "\$$col = \$_POST['$col'];";
        }
    }
    // Add PK
    $uParamTypes .= "s"; 
    $uFields[] = "\$id";

    $updateCode .= "    " . implode("\n    ", $uPostVars) . "\n\n";
    $updateCode .= "    \$query = \"UPDATE `$tableName` SET " . implode(", ", $updateSets) . " WHERE `$pk` = ?\";\n";
    $updateCode .= "    \$stmt = \$conn->prepare(\$query);\n";
    $updateCode .= "    \$stmt->bind_param(\"$uParamTypes\", " . implode(", ", $uFields) . ");\n\n";
    $updateCode .= "    if (\$stmt->execute()) {\n";
    $updateCode .= "        \$_SESSION['notification'] = \"Data updated successfully.\";\n";
    $updateCode .= "        \$_SESSION['notification_type'] = \"success\";\n";
    $updateCode .= "    } else {\n";
    $updateCode .= "        \$_SESSION['notification'] = \"Error: \" . \$stmt->error;\n";
    $updateCode .= "        \$_SESSION['notification_type'] = \"error\";\n";
    $updateCode .= "    }\n";
    $updateCode .= "    \$stmt->close();\n";
    $updateCode .= "    header(\"Location: ../../$fileName.php\");\n    exit();\n}\n?>";

    file_put_contents("$coreDir/update.php", $updateCode);

    // 2c. DELETE Action (No change)
    $deleteCode = "<?php\nsession_start();\ninclude('../../connection/connection.php');\n\n";
    $deleteCode .= "if (!isset(\$_SESSION['username'])) { exit(); }\n\n";
    $deleteCode .= "if (isset(\$_GET['id'])) {\n";
    $deleteCode .= "    \$id = \$_GET['id'];\n";
    $deleteCode .= "    \$query = \"DELETE FROM `$tableName` WHERE `$pk` = ?\";\n";
    $deleteCode .= "    \$stmt = \$conn->prepare(\$query);\n";
    $deleteCode .= "    \$stmt->bind_param(\"s\", \$id);\n";
    $deleteCode .= "    if (\$stmt->execute()) {\n";
    $deleteCode .= "        \$_SESSION['notification'] = \"Data deleted successfully.\";\n";
    $deleteCode .= "        \$_SESSION['notification_type'] = \"success\";\n";
    $deleteCode .= "    } else {\n";
    $deleteCode .= "        \$_SESSION['notification'] = \"Error: \" . \$stmt->error;\n";
    $deleteCode .= "        \$_SESSION['notification_type'] = \"error\";\n";
    $deleteCode .= "    }\n";
    $deleteCode .= "    \$stmt->close();\n}\n";
    $deleteCode .= "header(\"Location: ../../$fileName.php\");\nexit();\n?>";

    file_put_contents("$coreDir/delete.php", $deleteCode);

    // --- 3. Generate View File (Single Page CRUD) ---

    // 3a. Query Logic with Joins
    $selectFields = ["t1.*"];
    $joins = "";
    
    foreach ($relations as $field => $rel) {
        $alias = $field . "_display"; 
        $refTable = $rel['table'];
        $refCol = $rel['display'];
        $selectFields[] = "`$refTable`.`$refCol` AS `$alias`";
        $joins .= " LEFT JOIN `$refTable` ON t1.`$field` = `$refTable`.`id` "; 
    }

    $selectQuery = "SELECT " . implode(", ", $selectFields) . " FROM `$tableName` t1 $joins ORDER BY t1.`$pk` DESC";

    // 3b. Build View Content
    $viewContent = "<?php\nsession_start();\ninclude('connection/connection.php');\n";
    $viewContent .= "if (isset(\$_SESSION['notification'])) {\n";
    $viewContent .= "    \$notification = \$_SESSION['notification'];\n";
    $viewContent .= "    \$notificationType = \$_SESSION['notification_type'];\n";
    $viewContent .= "    unset(\$_SESSION['notification']);\n    unset(\$_SESSION['notification_type']);\n";
    $viewContent .= "    echo \"<script>setTimeout(function() { toastr.options = { closeButton: true, progressBar: true, timeOut: 4000 }; toastr.\$notificationType('\$notification'); }, 1000);</script>\";\n}\n";
    
    // Fetch Relation Data for Dropdowns
    foreach ($relations as $field => $rel) {
        $varName = $field . "_options"; 
        $viewContent .= "\$$varName = \$conn->query(\"SELECT id, `{$rel['display']}` as label FROM `{$rel['table']}`\");\n";
    }

    $viewContent .= "?>\n";
    $viewContent .= "<!DOCTYPE html>\n<html>\n<head>\n";
    $viewContent .= "    <meta charset=\"utf-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    $viewContent .= "    <title>$moduleTitle | Native Starterkit</title>\n";
    $viewContent .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
    $viewContent .= "    <link href=\"assets/font-awesome/css/font-awesome.css\" rel=\"stylesheet\">\n";
    $viewContent .= "    <link href=\"assets/css/plugins/toastr/toastr.min.css\" rel=\"stylesheet\">\n";
    $viewContent .= "    <link href=\"assets/css/animate.css\" rel=\"stylesheet\">\n";
    $viewContent .= "    <link href=\"assets/css/style.css\" rel=\"stylesheet\">\n";
    $viewContent .= "    <link href=\"https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.css\" rel=\"stylesheet\">\n";
    $viewContent .= "    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\">\n";
    
    // Add dropzone CSS if file uploads are used
    if ($hasFileUpload) {
        $viewContent .= "    <style>\n";
        $viewContent .= "        .dropzone-wrapper {\n";
        $viewContent .= "            border: 2px dashed #ccc;\n";
        $viewContent .= "            border-radius: 8px;\n";
        $viewContent .= "            padding: 30px;\n";
        $viewContent .= "            text-align: center;\n";
        $viewContent .= "            cursor: pointer;\n";
        $viewContent .= "            transition: all 0.3s ease;\n";
        $viewContent .= "            position: relative;\n";
        $viewContent .= "            background: #fafafa;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .dropzone-wrapper:hover {\n";
        $viewContent .= "            border-color: #1ab394;\n";
        $viewContent .= "            background: #f0fff0;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .dropzone-wrapper.dragover {\n";
        $viewContent .= "            border-color: #1ab394;\n";
        $viewContent .= "            background: #e8f5e9;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .dropzone-desc {\n";
        $viewContent .= "            color: #777;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .dropzone-desc i {\n";
        $viewContent .= "            color: #1ab394;\n";
        $viewContent .= "            margin-bottom: 10px;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .dropzone-desc p {\n";
        $viewContent .= "            margin-bottom: 5px;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .dropzone-input {\n";
        $viewContent .= "            position: absolute;\n";
        $viewContent .= "            top: 0;\n";
        $viewContent .= "            left: 0;\n";
        $viewContent .= "            width: 100%;\n";
        $viewContent .= "            height: 100%;\n";
        $viewContent .= "            opacity: 0;\n";
        $viewContent .= "            cursor: pointer;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .current-file {\n";
        $viewContent .= "            margin-bottom: 10px;\n";
        $viewContent .= "            padding: 8px;\n";
        $viewContent .= "            background: #f5f5f5;\n";
        $viewContent .= "            border-radius: 4px;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .current-file img {\n";
        $viewContent .= "            border-radius: 4px;\n";
        $viewContent .= "            border: 1px solid #ddd;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .file-preview {\n";
        $viewContent .= "            display: flex;\n";
        $viewContent .= "            flex-wrap: wrap;\n";
        $viewContent .= "            gap: 10px;\n";
        $viewContent .= "            margin-bottom: 10px;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .file-preview-item {\n";
        $viewContent .= "            position: relative;\n";
        $viewContent .= "            width: 80px;\n";
        $viewContent .= "            height: 80px;\n";
        $viewContent .= "            border-radius: 8px;\n";
        $viewContent .= "            overflow: hidden;\n";
        $viewContent .= "            border: 2px solid #ddd;\n";
        $viewContent .= "            background: #f5f5f5;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .file-preview-item img {\n";
        $viewContent .= "            width: 100%;\n";
        $viewContent .= "            height: 100%;\n";
        $viewContent .= "            object-fit: cover;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .file-preview-item .file-icon {\n";
        $viewContent .= "            display: flex;\n";
        $viewContent .= "            align-items: center;\n";
        $viewContent .= "            justify-content: center;\n";
        $viewContent .= "            width: 100%;\n";
        $viewContent .= "            height: 100%;\n";
        $viewContent .= "            font-size: 24px;\n";
        $viewContent .= "            color: #666;\n";
        $viewContent .= "        }\n";
        $viewContent .= "        .file-preview-item .file-name {\n";
        $viewContent .= "            position: absolute;\n";
        $viewContent .= "            bottom: 0;\n";
        $viewContent .= "            left: 0;\n";
        $viewContent .= "            right: 0;\n";
        $viewContent .= "            background: rgba(0,0,0,0.7);\n";
        $viewContent .= "            color: #fff;\n";
        $viewContent .= "            font-size: 9px;\n";
        $viewContent .= "            padding: 2px 4px;\n";
        $viewContent .= "            white-space: nowrap;\n";
        $viewContent .= "            overflow: hidden;\n";
        $viewContent .= "            text-overflow: ellipsis;\n";
        $viewContent .= "        }\n";
        $viewContent .= "    </style>\n";
    }
    
    $viewContent .= "</head>\n<body>\n";
    $viewContent .= "    <div id=\"wrapper\">\n";
    $viewContent .= "        <?php include 'template/sidebar.php'; ?>\n";
    $viewContent .= "        <div id=\"page-wrapper\" class=\"gray-bg\">\n";
    $viewContent .= "            <?php include 'template/header.php'; ?>\n";
    $viewContent .= "            <div class=\"row wrapper border-bottom white-bg page-heading\">\n";
    $viewContent .= "                <div class=\"col-lg-10\">\n";
    $viewContent .= "                    <h2>$moduleTitle</h2>\n";
    $viewContent .= "                    <ol class=\"breadcrumb\">\n";
    $viewContent .= "                        <li class=\"breadcrumb-item\"><a href=\"dashboard.php\">Home</a></li>\n";
    $viewContent .= "                        <li class=\"breadcrumb-item active\"><strong>$moduleTitle</strong></li>\n";
    $viewContent .= "                    </ol>\n";
    $viewContent .= "                </div>\n";
    $viewContent .= "            </div>\n";
    
    $viewContent .= "            <div class=\"wrapper wrapper-content animated fadeInRight\">\n";
    $viewContent .= "                <div class=\"row\">\n";
    $viewContent .= "                    <div class=\"col-lg-12\">\n";
    $viewContent .= "                        <div class=\"ibox\">\n";
    $viewContent .= "                            <div class=\"ibox-title\">\n";
    $viewContent .= "                                <h5>List of $moduleTitle</h5>\n";
    $viewContent .= "                                <div class=\"ibox-tools\">\n";
    $viewContent .= "                                    <button class=\"btn btn-primary btn-xs\" data-toggle=\"modal\" data-target=\"#addModal\">\n";
    $viewContent .= "                                        <i class=\"fa fa-plus\"></i> Add New\n";
    $viewContent .= "                                    </button>\n";
    $viewContent .= "                                </div>\n";
    $viewContent .= "                            </div>\n";
    
    $viewContent .= "                            <div class=\"ibox-content\">\n";
    $viewContent .= "                                <div class=\"table-responsive\">\n";
    $viewContent .= "                                    <table class=\"table table-striped table-bordered table-hover\" id=\"dataTables\">\n";
    $viewContent .= "                                        <thead>\n";
    $viewContent .= "                                            <tr>\n";
    $viewContent .= "                                                <th width=\"5%\">No</th>\n";
    
    foreach ($validCols as $col) {
        if ($col === $pk) continue; 
        $viewContent .= "                                                <th>" . ucfirst(str_replace('_', ' ', $col)) . "</th>\n";
    }
    
    $viewContent .= "                                                <th width=\"15%\">Action</th>\n";
    $viewContent .= "                                            </tr>\n";
    $viewContent .= "                                        </thead>\n";
    $viewContent .= "                                        <tbody>\n";
    $viewContent .= "                                            <?php\n";
    $viewContent .= "                                            \$no = 1;\n";
    $viewContent .= "                                            \$data = \$conn->query(\"$selectQuery\");\n";
    $viewContent .= "                                            while (\$row = \$data->fetch_assoc()):\n";
    $viewContent .= "                                            ?>\n";
    $viewContent .= "                                            <tr>\n";
    $viewContent .= "                                                <td><?= \$no++ ?></td>\n";
    
    foreach ($validCols as $col) {
        if ($col === $pk) continue;
        if (isset($relations[$col])) {
            $alias = $col . "_display";
             $viewContent .= "                                                <td><?= \$row['$alias'] ?></td>\n";
        } else if (isset($fileColumns[$col])) {
            // File column - show as link/thumbnail
            $isMultiple = $fileColumns[$col]['multiple'] ?? false;
            $viewContent .= "                                                <td>\n";
            $viewContent .= "                                                    <?php if (!empty(\$row['$col'])): ?>\n";
            
            if ($isMultiple) {
                // Multiple files - stored as JSON array
                $viewContent .= "                                                        <?php \n";
                $viewContent .= "                                                        \$files = json_decode(\$row['$col'], true);\n";
                $viewContent .= "                                                        if (is_array(\$files) && count(\$files) > 0):\n";
                $viewContent .= "                                                        ?>\n";
                $viewContent .= "                                                        <div style=\"display:flex;gap:5px;flex-wrap:wrap;\">\n";
                $viewContent .= "                                                        <?php foreach(\$files as \$file): ?>\n";
                $viewContent .= "                                                            <?php \n";
                $viewContent .= "                                                            \$fileExt = strtolower(pathinfo(\$file, PATHINFO_EXTENSION));\n";
                $viewContent .= "                                                            \$isImage = in_array(\$fileExt, ['jpg','jpeg','png','gif','webp']);\n";
                $viewContent .= "                                                            ?>\n";
                $viewContent .= "                                                            <?php if (\$isImage): ?>\n";
                $viewContent .= "                                                                <a href=\"uploads/<?= \$file ?>\" target=\"_blank\">\n";
                $viewContent .= "                                                                    <img src=\"uploads/<?= \$file ?>\" style=\"max-width:40px;max-height:40px;border-radius:4px;\">\n";
                $viewContent .= "                                                                </a>\n";
                $viewContent .= "                                                            <?php else: ?>\n";
                $viewContent .= "                                                                <a href=\"uploads/<?= \$file ?>\" target=\"_blank\" class=\"btn btn-xs btn-default\"><i class=\"fa fa-file\"></i></a>\n";
                $viewContent .= "                                                            <?php endif; ?>\n";
                $viewContent .= "                                                        <?php endforeach; ?>\n";
                $viewContent .= "                                                        </div>\n";
                $viewContent .= "                                                        <?php else: ?>\n";
                $viewContent .= "                                                        <span class=\"text-muted\">-</span>\n";
                $viewContent .= "                                                        <?php endif; ?>\n";
            } else {
                // Single file
                $viewContent .= "                                                        <?php \n";
                $viewContent .= "                                                        \$fileExt = strtolower(pathinfo(\$row['$col'], PATHINFO_EXTENSION));\n";
                $viewContent .= "                                                        \$isImage = in_array(\$fileExt, ['jpg','jpeg','png','gif','webp']);\n";
                $viewContent .= "                                                        ?>\n";
                $viewContent .= "                                                        <?php if (\$isImage): ?>\n";
                $viewContent .= "                                                            <a href=\"uploads/<?= \$row['$col'] ?>\" target=\"_blank\">\n";
                $viewContent .= "                                                                <img src=\"uploads/<?= \$row['$col'] ?>\" style=\"max-width:50px;max-height:50px;border-radius:4px;\">\n";
                $viewContent .= "                                                            </a>\n";
                $viewContent .= "                                                        <?php else: ?>\n";
                $viewContent .= "                                                            <a href=\"uploads/<?= \$row['$col'] ?>\" target=\"_blank\" class=\"btn btn-xs btn-default\">\n";
                $viewContent .= "                                                                <i class=\"fa fa-file\"></i> <?= \$row['$col'] ?>\n";
                $viewContent .= "                                                            </a>\n";
                $viewContent .= "                                                        <?php endif; ?>\n";
            }
            
            $viewContent .= "                                                    <?php else: ?>\n";
            $viewContent .= "                                                        <span class=\"text-muted\">-</span>\n";
            $viewContent .= "                                                    <?php endif; ?>\n";
            $viewContent .= "                                                </td>\n";
        } else {
             $viewContent .= "                                                <td><?= \$row['$col'] ?></td>\n";
        }
    }
    
    $viewContent .= "                                                <td>\n";
    $viewContent .= "                                                    <button class=\"btn btn-warning btn-xs\" onclick=\"editData('<?= \$row['$pk'] ?>', " . 
                    implode(", ", array_map(function($c) { return "'<?= \$row['$c'] ?>'"; }, $validCols)) . 
                    ")\"><i class=\"fa fa-edit\"></i> Edit</button>\n";
    $viewContent .= "                                                    <button class=\"btn btn-danger btn-xs\" onclick=\"confirmDelete('<?= \$row['$pk'] ?>')\"><i class=\"fa fa-trash\"></i> Delete</button>\n";
    $viewContent .= "                                                </td>\n";
    $viewContent .= "                                            </tr>\n";
    $viewContent .= "                                            <?php endwhile; ?>\n";
    $viewContent .= "                                        </tbody>\n";
    $viewContent .= "                                    </table>\n";
    $viewContent .= "                                </div>\n";
    $viewContent .= "                            </div>\n";
    $viewContent .= "                        </div>\n";
    $viewContent .= "                    </div>\n";
    $viewContent .= "                </div>\n";
    $viewContent .= "            </div>\n";
    $viewContent .= "            <?php include 'template/footer.php'; ?>\n";
    $viewContent .= "        </div>\n";
    $viewContent .= "    </div>\n\n";

    // --- MODALS (Enhanced) ---
    
    // Add Modal
    $viewContent .= "    <!-- Add Modal -->\n";
    $viewContent .= "    <div class=\"modal fade\" id=\"addModal\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">\n";
    $viewContent .= "        <div class=\"modal-dialog\">\n";
    $viewContent .= "            <div class=\"modal-content\">\n";
    $viewContent .= "                <div class=\"modal-header\">\n";
    $viewContent .= "                    <h4 class=\"modal-title\">Add $moduleTitle</h4>\n";
    $viewContent .= "                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span aria-hidden=\"true\">&times;</span></button>\n";
    $viewContent .= "                </div>\n";
    $viewContent .= "                <div class=\"modal-body\">\n";
    
    // Add enctype if has file uploads
    $formEnctype = $hasFileUpload ? ' enctype="multipart/form-data"' : '';
    $viewContent .= "                    <form action=\"core/$fileName/create.php\" method=\"POST\"$formEnctype>\n";
    
    foreach ($validCols as $col) {
        if ($col === $pk) continue; 
        
        $inputType = $columns[$col]['input_type'] ?? 'text';
        $rules = $columns[$col]['validations'] ?? [];
        $required = in_array('required', $rules) ? 'required' : '';
        $label = ucfirst(str_replace('_', ' ', $col));

        $viewContent .= "                        <div class=\"form-group\">\n";
        $viewContent .= "                            <label>$label</label>\n";
        
        if (isset($relations[$col])) {
             $viewContent .= "                            <select name=\"$col\" class=\"form-control\" $required>\n";
             $viewContent .= "                                <option value=\"\">-- Select --</option>\n";
             $viewContent .= "                                <?php mysqli_data_seek(\${$col}_options, 0); while(\$opt = \${$col}_options->fetch_assoc()): ?>\n";
             $viewContent .= "                                    <option value=\"<?= \$opt['id'] ?>\"><?= \$opt['label'] ?></option>\n";
             $viewContent .= "                                <?php endwhile; ?>\n";
             $viewContent .= "                            </select>\n";
        } else {
            // Check if rupiah validation is enabled
            $isRupiah = in_array('rupiah', $rules);
            $rupiahClass = $isRupiah ? ' rupiah-input' : '';
            
            // Switch based on selected input type
            switch ($inputType) {
                case 'textarea':
                    $viewContent .= "                            <textarea name=\"$col\" class=\"form-control\" $required></textarea>\n";
                    break;
                case 'select':
                    $viewContent .= "                            <select name=\"$col\" class=\"form-control\" $required>\n";
                    $viewContent .= "                                <option value=\"\">-- Select --</option>\n";
                    $viewContent .= "                                <option value=\"Option 1\">Option 1</option>\n";
                    $viewContent .= "                                <option value=\"Option 2\">Option 2</option>\n";
                    $viewContent .= "                            </select>\n";
                    break;
                case 'radio':
                    $viewContent .= "                            <div>\n";
                    $viewContent .= "                                <label><input type=\"radio\" name=\"$col\" value=\"1\" checked> Active</label>\n";
                    $viewContent .= "                                <label><input type=\"radio\" name=\"$col\" value=\"0\"> Inactive</label>\n";
                    $viewContent .= "                            </div>\n";
                    break;
                case 'file':
                    $ext = $fileColumns[$col]['extensions'] ?? 'jpg,jpeg,png,gif,pdf';
                    $maxSize = $fileColumns[$col]['max_size'] ?? 2;
                    $isMultiple = $fileColumns[$col]['multiple'] ?? false;
                    $multiAttr = $isMultiple ? ' multiple' : '';
                    $inputName = $isMultiple ? "{$col}[]" : $col;
                    $multiText = $isMultiple ? 's' : '';
                    $viewContent .= "                            <div class=\"file-preview\" id=\"preview_$col\"></div>\n";
                    $viewContent .= "                            <input type=\"file\" name=\"$inputName\" class=\"form-control file-input-preview\" data-preview=\"preview_$col\"$multiAttr accept=\"." . str_replace(',', ',.', $ext) . "\">\n";
                    $viewContent .= "                            <small class=\"text-muted\">Allowed: $ext (Max: {$maxSize}MB per file$multiText)</small>\n";
                    break;
                case 'file_dropzone':
                    $ext = $fileColumns[$col]['extensions'] ?? 'jpg,jpeg,png,gif,pdf';
                    $maxSize = $fileColumns[$col]['max_size'] ?? 2;
                    $isMultiple = $fileColumns[$col]['multiple'] ?? false;
                    $multiAttr = $isMultiple ? ' multiple' : '';
                    $inputName = $isMultiple ? "{$col}[]" : $col;
                    $multiText = $isMultiple ? ' (Multiple allowed)' : '';
                    $viewContent .= "                            <div class=\"file-preview\" id=\"preview_$col\"></div>\n";
                    $viewContent .= "                            <div class=\"dropzone-wrapper\">\n";
                    $viewContent .= "                                <div class=\"dropzone-desc\">\n";
                    $viewContent .= "                                    <i class=\"fa fa-cloud-upload fa-2x\"></i>\n";
                    $viewContent .= "                                    <p>Drag & drop file here or click to browse$multiText</p>\n";
                    $viewContent .= "                                    <small>Allowed: $ext (Max: {$maxSize}MB per file)</small>\n";
                    $viewContent .= "                                </div>\n";
                    $viewContent .= "                                <input type=\"file\" name=\"$inputName\" class=\"dropzone-input file-input-preview\" data-preview=\"preview_$col\"$multiAttr accept=\"." . str_replace(',', ',.', $ext) . "\">\n";
                    $viewContent .= "                            </div>\n";
                    break;
                default: 
                    // text, number, date, etc.
                    if ($isRupiah) {
                        $viewContent .= "                            <input type=\"text\" name=\"$col\" class=\"form-control$rupiahClass\" placeholder=\"Rp 0\" $required>\n";
                    } else {
                        $viewContent .= "                            <input type=\"$inputType\" name=\"$col\" class=\"form-control\" $required>\n";
                    }
                    break;
            }
        }
        $viewContent .= "                        </div>\n";
    }
    
    $viewContent .= "                        <button type=\"submit\" class=\"btn btn-primary\">Save</button>\n";
    $viewContent .= "                    </form>\n";
    $viewContent .= "                </div>\n";
    $viewContent .= "            </div>\n";
    $viewContent .= "        </div>\n";
    $viewContent .= "    </div>\n\n";

    // Edit Modal
    $viewContent .= "    <!-- Edit Modal -->\n";
    $viewContent .= "    <div class=\"modal fade\" id=\"editModal\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">\n";
    $viewContent .= "        <div class=\"modal-dialog\">\n";
    $viewContent .= "            <div class=\"modal-content\">\n";
    $viewContent .= "                <div class=\"modal-header\">\n";
    $viewContent .= "                    <h4 class=\"modal-title\">Edit $moduleTitle</h4>\n";
    $viewContent .= "                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span aria-hidden=\"true\">&times;</span></button>\n";
    $viewContent .= "                </div>\n";
    $viewContent .= "                <div class=\"modal-body\">\n";
    $viewContent .= "                    <form action=\"core/$fileName/update.php\" method=\"POST\"$formEnctype>\n";
    $viewContent .= "                        <input type=\"hidden\" name=\"$pk\" id=\"edit_$pk\">\n";
    
    foreach ($validCols as $col) {
        if ($col === $pk) continue;
        
        $inputType = $columns[$col]['input_type'] ?? 'text';
        $rules = $columns[$col]['validations'] ?? [];
        $required = in_array('required', $rules) ? 'required' : '';
        $label = ucfirst(str_replace('_', ' ', $col));

        $viewContent .= "                        <div class=\"form-group\">\n";
        $viewContent .= "                            <label>$label</label>\n";
        
        if (isset($relations[$col])) {
             $viewContent .= "                            <select name=\"$col\" id=\"edit_$col\" class=\"form-control\" $required>\n";
             $viewContent .= "                                <option value=\"\">-- Select --</option>\n";
             $viewContent .= "                                <?php mysqli_data_seek(\${$col}_options, 0); while(\$opt = \${$col}_options->fetch_assoc()): ?>\n";
             $viewContent .= "                                    <option value=\"<?= \$opt['id'] ?>\"><?= \$opt['label'] ?></option>\n";
             $viewContent .= "                                <?php endwhile; ?>\n";
             $viewContent .= "                            </select>\n";
        } else {
            // Check if rupiah validation is enabled
            $isRupiah = in_array('rupiah', $rules);
            $rupiahClass = $isRupiah ? ' rupiah-input' : '';
            
             switch ($inputType) {
                case 'textarea':
                    $viewContent .= "                            <textarea name=\"$col\" id=\"edit_$col\" class=\"form-control\" $required></textarea>\n";
                    break;
                case 'select':
                    $viewContent .= "                            <select name=\"$col\" id=\"edit_$col\" class=\"form-control\" $required>\n";
                    $viewContent .= "                                <option value=\"\">-- Select --</option>\n";
                    $viewContent .= "                                <option value=\"Option 1\">Option 1</option>\n";
                    $viewContent .= "                                <option value=\"Option 2\">Option 2</option>\n";
                    $viewContent .= "                            </select>\n";
                    break;
                case 'radio':
                   $viewContent .= "                            <div>\n";
                    $viewContent .= "                                <label><input type=\"radio\" name=\"$col\" id=\"edit_{$col}_1\" value=\"1\"> Active</label>\n";
                    $viewContent .= "                                <label><input type=\"radio\" name=\"$col\" id=\"edit_{$col}_0\" value=\"0\"> Inactive</label>\n";
                    $viewContent .= "                            </div>\n";
                    break;
                case 'file':
                    $ext = $fileColumns[$col]['extensions'] ?? 'jpg,jpeg,png,gif,pdf';
                    $maxSize = $fileColumns[$col]['max_size'] ?? 2;
                    $isMultiple = $fileColumns[$col]['multiple'] ?? false;
                    $multiAttr = $isMultiple ? ' multiple' : '';
                    $inputName = $isMultiple ? "{$col}[]" : $col;
                    $multiText = $isMultiple ? 's' : '';
                    $viewContent .= "                            <input type=\"hidden\" name=\"{$col}_existing\" id=\"edit_{$col}_existing\">\n";
                    $viewContent .= "                            <div class=\"current-file\" id=\"current_{$col}\"></div>\n";
                    $viewContent .= "                            <div class=\"file-preview\" id=\"edit_preview_$col\"></div>\n";
                    $viewContent .= "                            <input type=\"file\" name=\"$inputName\" id=\"edit_$col\" class=\"form-control file-input-preview\" data-preview=\"edit_preview_$col\"$multiAttr accept=\"." . str_replace(',', ',.', $ext) . "\">\n";
                    $viewContent .= "                            <small class=\"text-muted\">Leave empty to keep current. Allowed: $ext (Max: {$maxSize}MB per file$multiText)</small>\n";
                    break;
                case 'file_dropzone':
                    $ext = $fileColumns[$col]['extensions'] ?? 'jpg,jpeg,png,gif,pdf';
                    $maxSize = $fileColumns[$col]['max_size'] ?? 2;
                    $isMultiple = $fileColumns[$col]['multiple'] ?? false;
                    $multiAttr = $isMultiple ? ' multiple' : '';
                    $inputName = $isMultiple ? "{$col}[]" : $col;
                    $multiText = $isMultiple ? ' (Multiple allowed)' : '';
                    $viewContent .= "                            <input type=\"hidden\" name=\"{$col}_existing\" id=\"edit_{$col}_existing\">\n";
                    $viewContent .= "                            <div class=\"current-file\" id=\"current_{$col}\"></div>\n";
                    $viewContent .= "                            <div class=\"file-preview\" id=\"edit_preview_$col\"></div>\n";
                    $viewContent .= "                            <div class=\"dropzone-wrapper\">\n";
                    $viewContent .= "                                <div class=\"dropzone-desc\">\n";
                    $viewContent .= "                                    <i class=\"fa fa-cloud-upload fa-2x\"></i>\n";
                    $viewContent .= "                                    <p>Drag & drop file here or click to browse$multiText</p>\n";
                    $viewContent .= "                                    <small>Leave empty to keep current. Allowed: $ext (Max: {$maxSize}MB)</small>\n";
                    $viewContent .= "                                </div>\n";
                    $viewContent .= "                                <input type=\"file\" name=\"$inputName\" id=\"edit_$col\" class=\"dropzone-input file-input-preview\" data-preview=\"edit_preview_$col\"$multiAttr accept=\"." . str_replace(',', ',.', $ext) . "\">\n";
                    $viewContent .= "                            </div>\n";
                    break;
                default:
                    if ($isRupiah) {
                        $viewContent .= "                            <input type=\"text\" name=\"$col\" id=\"edit_$col\" class=\"form-control$rupiahClass\" placeholder=\"Rp 0\" $required>\n";
                    } else {
                        $viewContent .= "                            <input type=\"$inputType\" name=\"$col\" id=\"edit_$col\" class=\"form-control\" $required>\n";
                    }
                    break;
            }
        }
        $viewContent .= "                        </div>\n";
    }

    $viewContent .= "                        <button type=\"submit\" class=\"btn btn-primary\">Update</button>\n";
    $viewContent .= "                    </form>\n";
    $viewContent .= "                </div>\n";
    $viewContent .= "            </div>\n";
    $viewContent .= "        </div>\n";
    $viewContent .= "    </div>\n\n";

    // Scripts
    $viewContent .= "    <script src=\"assets/js/jquery-3.1.1.min.js\"></script>\n";
    $viewContent .= "    <script src=\"assets/js/popper.min.js\"></script>\n";
    $viewContent .= "    <script src=\"assets/js/bootstrap.js\"></script>\n";
    $viewContent .= "    <script src=\"assets/js/plugins/metisMenu/jquery.metisMenu.js\"></script>\n";
    $viewContent .= "    <script src=\"assets/js/plugins/slimscroll/jquery.slimscroll.min.js\"></script>\n";
    $viewContent .= "    <script src=\"assets/js/inspinia.js\"></script>\n";
    $viewContent .= "    <script src=\"assets/js/plugins/pace/pace.min.js\"></script>\n";
    $viewContent .= "    <script src=\"assets/js/plugins/toastr/toastr.min.js\"></script>\n";
    $viewContent .= "    <script src=\"https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.js\"></script>\n";
    $viewContent .= "    <script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script>\n";
    
    $viewContent .= "    <script>\n";
    $viewContent .= "        $(document).ready(function(){\n";
    $viewContent .= "            $('#dataTables').DataTable();\n";
    $viewContent .= "        });\n\n";
    
    // JS Edit Function
    $viewContent .= "        function editData($pk, " . implode(", ", $validCols) . ") {\n";
    $viewContent .= "            $('#edit_$pk').val($pk);\n";
    foreach ($validCols as $col) {
        if ($col === $pk) continue;
        $inputType = $columns[$col]['input_type'] ?? 'text';
        
        if ($inputType === 'radio') {
             $viewContent .= "            if ($col == 1) $('#edit_{$col}_1').prop('checked', true); else $('#edit_{$col}_0').prop('checked', true);\n";
        } else if ($inputType === 'file' || $inputType === 'file_dropzone') {
             $isMultiple = $fileColumns[$col]['multiple'] ?? false;
             // For file inputs, store existing filename and show preview
             $viewContent .= "            $('#edit_{$col}_existing').val($col);\n";
             $viewContent .= "            $('#edit_preview_{$col}').empty();\n";
             
             if ($isMultiple) {
                 // Multiple files - parse JSON
                 $viewContent .= "            if ($col) {\n";
                 $viewContent .= "                try {\n";
                 $viewContent .= "                    var files = JSON.parse($col);\n";
                 $viewContent .= "                    if (Array.isArray(files) && files.length > 0) {\n";
                 $viewContent .= "                        var html = '<div style=\"display:flex;gap:5px;flex-wrap:wrap;\">';\n";
                 $viewContent .= "                        files.forEach(function(file) {\n";
                 $viewContent .= "                            var ext = file.split('.').pop().toLowerCase();\n";
                 $viewContent .= "                            var isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);\n";
                 $viewContent .= "                            if (isImage) {\n";
                 $viewContent .= "                                html += '<img src=\"uploads/' + file + '\" style=\"max-width:60px;max-height:60px;margin:2px;border-radius:4px;border:1px solid #ddd;\">';\n";
                 $viewContent .= "                            } else {\n";
                 $viewContent .= "                                html += '<span style=\"padding:10px;background:#f5f5f5;border-radius:4px;margin:2px;\"><i class=\"fa fa-file\"></i></span>';\n";
                 $viewContent .= "                            }\n";
                 $viewContent .= "                        });\n";
                 $viewContent .= "                        html += '</div><small class=\"text-muted\">Current: ' + files.length + ' file(s)</small>';\n";
                 $viewContent .= "                        $('#current_{$col}').html(html);\n";
                 $viewContent .= "                    } else {\n";
                 $viewContent .= "                        $('#current_{$col}').html('<small class=\"text-muted\">No files uploaded</small>');\n";
                 $viewContent .= "                    }\n";
                 $viewContent .= "                } catch(e) {\n";
                 $viewContent .= "                    $('#current_{$col}').html('<small class=\"text-muted\">No files uploaded</small>');\n";
                 $viewContent .= "                }\n";
                 $viewContent .= "            } else {\n";
                 $viewContent .= "                $('#current_{$col}').html('<small class=\"text-muted\">No files uploaded</small>');\n";
                 $viewContent .= "            }\n";
             } else {
                 // Single file
                 $viewContent .= "            if ($col) {\n";
                 $viewContent .= "                var ext = $col.split('.').pop().toLowerCase();\n";
                 $viewContent .= "                var isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);\n";
                 $viewContent .= "                if (isImage) {\n";
                 $viewContent .= "                    $('#current_{$col}').html('<img src=\"uploads/' + $col + '\" style=\"max-width:100px;max-height:100px;margin-bottom:10px;\"><br><small>Current: ' + $col + '</small>');\n";
                 $viewContent .= "                } else {\n";
                 $viewContent .= "                    $('#current_{$col}').html('<i class=\"fa fa-file\"></i> <small>Current: ' + $col + '</small>');\n";
                 $viewContent .= "                }\n";
                 $viewContent .= "            } else {\n";
                 $viewContent .= "                $('#current_{$col}').html('<small class=\"text-muted\">No file uploaded</small>');\n";
                 $viewContent .= "            }\n";
             }
        } else {
             $viewContent .= "            $('#edit_$col').val($col);\n";
        }
    }
    $viewContent .= "            $('#editModal').modal('show');\n";
    $viewContent .= "        }\n\n";
    
    // JS Delete Function
    $viewContent .= "        function confirmDelete(id) {\n";
    $viewContent .= "            Swal.fire({\n";
    $viewContent .= "                title: 'Are you sure?',\n";
    $viewContent .= "                text: \"You won't be able to revert this!\",\n";
    $viewContent .= "                icon: 'warning',\n";
    $viewContent .= "                showCancelButton: true,\n";
    $viewContent .= "                confirmButtonColor: '#d33',\n";
    $viewContent .= "                confirmButtonText: 'Yes, delete it!'\n";
    $viewContent .= "            }).then((result) => {\n";
    $viewContent .= "                if (result.isConfirmed) {\n";
    $viewContent .= "                    window.location.href = 'core/$fileName/delete.php?id=' + id;\n";
    $viewContent .= "                }\n";
    $viewContent .= "            })\n";
    $viewContent .= "        }\n\n";
    
    // Rupiah Auto-Format JavaScript
    $viewContent .= "        // Rupiah Format Helper\n";
    $viewContent .= "        function formatRupiah(angka) {\n";
    $viewContent .= "            var number_string = angka.replace(/[^,\\d]/g, '').toString();\n";
    $viewContent .= "            var split = number_string.split(',');\n";
    $viewContent .= "            var sisa = split[0].length % 3;\n";
    $viewContent .= "            var rupiah = split[0].substr(0, sisa);\n";
    $viewContent .= "            var ribuan = split[0].substr(sisa).match(/\\d{3}/gi);\n";
    $viewContent .= "            if (ribuan) {\n";
    $viewContent .= "                var separator = sisa ? '.' : '';\n";
    $viewContent .= "                rupiah += separator + ribuan.join('.');\n";
    $viewContent .= "            }\n";
    $viewContent .= "            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;\n";
    $viewContent .= "            return rupiah ? 'Rp ' + rupiah : '';\n";
    $viewContent .= "        }\n\n";
    $viewContent .= "        // Auto-format Rupiah inputs on keyup\n";
    $viewContent .= "        $(document).on('keyup', '.rupiah-input', function(e) {\n";
    $viewContent .= "            $(this).val(formatRupiah($(this).val()));\n";
    $viewContent .= "        });\n\n";
    $viewContent .= "        // Format existing values on page load (for edit modal)\n";
    $viewContent .= "        $(document).ready(function() {\n";
    $viewContent .= "            $('.rupiah-input').each(function() {\n";
    $viewContent .= "                if ($(this).val()) {\n";
    $viewContent .= "                    $(this).val(formatRupiah($(this).val()));\n";
    $viewContent .= "                }\n";
    $viewContent .= "            });\n";
    $viewContent .= "        });\n";
    
    // Add dropzone JavaScript if file uploads are used
    if ($hasFileUpload) {
        $viewContent .= "\n        // File Preview functionality\n";
        $viewContent .= "        function showFilePreview(input) {\n";
        $viewContent .= "            var previewId = $(input).data('preview');\n";
        $viewContent .= "            if (!previewId) return;\n";
        $viewContent .= "            var previewContainer = $('#' + previewId);\n";
        $viewContent .= "            previewContainer.empty();\n";
        $viewContent .= "            \n";
        $viewContent .= "            if (input.files && input.files.length > 0) {\n";
        $viewContent .= "                for (var i = 0; i < input.files.length; i++) {\n";
        $viewContent .= "                    var file = input.files[i];\n";
        $viewContent .= "                    var isImage = file.type.startsWith('image/');\n";
        $viewContent .= "                    var item = $('<div class=\"file-preview-item\"></div>');\n";
        $viewContent .= "                    \n";
        $viewContent .= "                    if (isImage) {\n";
        $viewContent .= "                        var reader = new FileReader();\n";
        $viewContent .= "                        reader.onload = (function(theItem) {\n";
        $viewContent .= "                            return function(e) {\n";
        $viewContent .= "                                theItem.prepend('<img src=\"' + e.target.result + '\">');\n";
        $viewContent .= "                            };\n";
        $viewContent .= "                        })(item);\n";
        $viewContent .= "                        reader.readAsDataURL(file);\n";
        $viewContent .= "                    } else {\n";
        $viewContent .= "                        item.append('<div class=\"file-icon\"><i class=\"fa fa-file\"></i></div>');\n";
        $viewContent .= "                    }\n";
        $viewContent .= "                    item.append('<div class=\"file-name\">' + file.name + '</div>');\n";
        $viewContent .= "                    previewContainer.append(item);\n";
        $viewContent .= "                }\n";
        $viewContent .= "            }\n";
        $viewContent .= "        }\n\n";
        
        $viewContent .= "        // Dropzone functionality\n";
        $viewContent .= "        $(document).ready(function() {\n";
        $viewContent .= "            // File input preview\n";
        $viewContent .= "            $(document).on('change', '.file-input-preview', function() {\n";
        $viewContent .= "                showFilePreview(this);\n";
        $viewContent .= "            });\n\n";
        $viewContent .= "            // Drag and drop visual feedback\n";
        $viewContent .= "            $('.dropzone-wrapper').on('dragover', function(e) {\n";
        $viewContent .= "                e.preventDefault();\n";
        $viewContent .= "                $(this).addClass('dragover');\n";
        $viewContent .= "            });\n";
        $viewContent .= "            $('.dropzone-wrapper').on('dragleave', function(e) {\n";
        $viewContent .= "                e.preventDefault();\n";
        $viewContent .= "                $(this).removeClass('dragover');\n";
        $viewContent .= "            });\n";
        $viewContent .= "            $('.dropzone-wrapper').on('drop', function(e) {\n";
        $viewContent .= "                $(this).removeClass('dragover');\n";
        $viewContent .= "            });\n";
        $viewContent .= "            // Show file count on dropzone\n";
        $viewContent .= "            $('.dropzone-input').on('change', function() {\n";
        $viewContent .= "                var count = this.files.length;\n";
        $viewContent .= "                if (count > 0) {\n";
        $viewContent .= "                    $(this).siblings('.dropzone-desc').find('p').text(count + ' file(s) selected');\n";
        $viewContent .= "                }\n";
        $viewContent .= "            });\n";
        $viewContent .= "        });\n";
    }
    
    $viewContent .= "    </script>\n";
    $viewContent .= "</body>\n</html>";

    file_put_contents("../../$fileName.php", $viewContent);

    // --- 4. Sidebar Integration ---
    if ($addToSidebar) {
        $sidebarFile = "../../template/sidebar.php";
        $sidebarContent = file_get_contents($sidebarFile);
        
        // Prevent Duplicate
        if (strpos($sidebarContent, "$fileName.php") === false) {
             $newLink = "            <li>\n                <a href=\"$fileName.php\"><i class=\"fa fa-table\"></i> <span class=\"nav-label\">$moduleTitle</span></a>\n            </li>\n";
             // Insert before Generator link
             $sidebarContent = str_replace('<li class="active">
                <a href="#"><i class="fa fa-cogs"></i>', $newLink . '<li class="active">
                <a href="#"><i class="fa fa-cogs"></i>', $sidebarContent);
             file_put_contents($sidebarFile, $sidebarContent);
        }
    }

    // --- 5. Save to CRUD History ---
    $columnsJson = json_encode($columns);
    $columnsJsonEscaped = $conn->real_escape_string($columnsJson);
    
    // Check if this table already has a history entry (update instead of insert)
    $checkHistory = $conn->query("SELECT id FROM crud_history WHERE table_name = '$tableName'");
    if ($checkHistory && $checkHistory->num_rows > 0) {
        // Update existing entry
        $conn->query("UPDATE crud_history SET module_title = '$moduleTitle', file_name = '$fileName', columns_config = '$columnsJsonEscaped', created_at = NOW() WHERE table_name = '$tableName'");
    } else {
        // Insert new entry
        $conn->query("INSERT INTO crud_history (table_name, module_title, file_name, columns_config) VALUES ('$tableName', '$moduleTitle', '$fileName', '$columnsJsonEscaped')");
    }

    $_SESSION['notification'] = "CRUD for $moduleTitle generated successfully!";
    $_SESSION['notification_type'] = "success";
    header("Location: ../../$fileName.php");
    exit();
}
?>
