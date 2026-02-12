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

// Fetch Tables
$tables = [];
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
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
    <title>Table Manager | Native Starterkit</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Sweetalert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.css" rel="stylesheet">
</head>
<body>
    <div id="wrapper">
        <?php include 'template/sidebar.php'; ?>
        
        <div id="page-wrapper" class="gray-bg">
            <?php include 'template/header.php'; ?>
            
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>Table Manager</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item">Generators</li>
                        <li class="breadcrumb-item active"><strong>Table Manager</strong></li>
                    </ol>
                </div>
            </div>

            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5>List of Tables</h5>
                                <div class="ibox-tools">
                                    <a href="generator_table_create.php" class="btn btn-primary btn-xs">
                                        <i class="fa fa-plus"></i> Create New Table
                                    </a>
                                </div>
                            </div>
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="tableList">
                                        <thead>
                                            <tr>
                                                <th width="50">No</th>
                                                <th>Table Name</th>
                                                <th width="200">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tables as $index => $table): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= $table ?></td>
                                                <td>
                                                    <a href="generator_table_structure.php?table=<?= $table ?>" class="btn btn-info btn-xs">
                                                        <i class="fa fa-list"></i> Structure
                                                    </a>
                                                    <button class="btn btn-danger btn-xs" onclick="confirmDrop('<?= $table ?>')">
                                                        <i class="fa fa-trash"></i> Drop
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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
    <script src="https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function(){
            $('#tableList').DataTable();
        });

        function confirmDrop(tableName) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to DROP table '" + tableName + "'. This cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, drop it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'core/generator/drop_table.php?table=' + tableName;
                }
            })
        }
    </script>
</body>
</html>
