<?php
session_start();
include('connection/connection.php');

// Security Check
if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    $_SESSION['notification'] = "Unauthorized access.";
    $_SESSION['notification_type'] = "error";
    header("Location: index.php");
    exit();
}

// Check Notification
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    $notificationType = $_SESSION['notification_type'];
    unset($_SESSION['notification']);
    unset($_SESSION['notification_type']);
    echo "<script>
            setTimeout(function() {
                toastr.options = { closeButton: true, progressBar: true, timeOut: 4000 };
                toastr.$notificationType('$notification');
            }, 1000);
          </script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Generator | Native Starterkit</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div id="wrapper">
        <?php include 'template/sidebar.php'; ?>
        
        <div id="page-wrapper" class="gray-bg">
            <?php include 'template/header.php'; ?>
            
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>Create New Table</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item">Generators</li>
                        <li class="breadcrumb-item"><a href="generator_table.php">Table Manager</a></li>
                        <li class="breadcrumb-item active"><strong>Create Table</strong></li>
                    </ol>
                </div>
            </div>

            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5>Create New Table</h5>
                            </div>
                            <div class="ibox-content">
                                <form action="core/generator/create_table.php" method="POST">
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Table Name</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="table_name" class="form-control" placeholder="e.g. tbl_products" required>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    <h4>Columns</h4>
                                    
                                    <table class="table table-bordered" id="columnsTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Length/Values</th>
                                                <th>Default</th>
                                                <th class="text-center" width="50">PK</th>
                                                <th class="text-center" width="50">AI</th>
                                                <th class="text-center" width="50">Null</th>
                                                <th width="50">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="columnsBody">
                                            <!-- Rows added via JS -->
                                        </tbody>
                                    </table>
                                    
                                    <button type="button" class="btn btn-info btn-sm" onclick="addColumnRow()">
                                        <i class="fa fa-plus"></i> Add Column
                                    </button>
                                    
                                    <div class="hr-line-dashed"></div>
                                    
                                    <div class="form-group row">
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn btn-primary float-right">Create Table</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'template/footer.php'; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/jquery-3.1.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/js/inspinia.js"></script>
    <script src="assets/js/plugins/pace/pace.min.js"></script>
    <script src="assets/js/plugins/toastr/toastr.min.js"></script>

    <script>
        let rowCount = 0;

        function addColumnRow() {
            rowCount++;
            const tbody = document.getElementById('columnsBody');
            const tr = document.createElement('tr');
            
            tr.innerHTML = `
                <td><input type="text" name="columns[${rowCount}][name]" class="form-control form-control-sm" required></td>
                <td>
                    <select name="columns[${rowCount}][type]" class="form-control form-control-sm">
                        <option value="INT">INT</option>
                        <option value="VARCHAR">VARCHAR</option>
                        <option value="TEXT">TEXT</option>
                        <option value="DATE">DATE</option>
                        <option value="DATETIME">DATETIME</option>
                        <option value="BOOLEAN">BOOLEAN</option>
                        <option value="DECIMAL">DECIMAL</option>
                    </select>
                </td>
                <td><input type="text" name="columns[${rowCount}][length]" class="form-control form-control-sm" placeholder="e.g. 255"></td>
                <td><input type="text" name="columns[${rowCount}][default_value]" class="form-control form-control-sm"></td>
                <td class="text-center"><input type="checkbox" name="columns[${rowCount}][is_pk]" value="1"></td>
                <td class="text-center"><input type="checkbox" name="columns[${rowCount}][is_ai]" value="1"></td>
                <td class="text-center"><input type="checkbox" name="columns[${rowCount}][is_null]" value="1"></td>
                <td><button type="button" class="btn btn-danger btn-xs" onclick="this.closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
            `;
            
            tbody.appendChild(tr);
        }

        // Add initial row
        window.onload = function() {
            addColumnRow();
            // Pre-fill first row as ID standard
            const firstRowInputs = document.querySelectorAll('#columnsBody tr:first-child input');
            firstRowInputs[0].value = 'id'; // Name
            // AI and PK checked by default for first row usually, but let user decide or we can set it
            const checkboxes = document.querySelectorAll('#columnsBody tr:first-child input[type="checkbox"]');
            checkboxes[0].checked = true; // PK
            checkboxes[1].checked = true; // AI
        };
    </script>
</body>
</html>
