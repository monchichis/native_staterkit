<?php
session_start();
include('connection/connection.php');

// Security Check
if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    header("Location: index.php");
    exit();
}

$tableName = $_GET['table'] ?? '';
if (empty($tableName)) {
    header("Location: generator_table.php");
    exit();
}

// Fetch Columns via DESCRIBE or SHOW FULL COLUMNS
$columns = [];
$result = $conn->query("SHOW FULL COLUMNS FROM `$tableName`");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
} else {
    die("Table not found or error: " . $conn->error);
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
    <title>Structure: <?= $tableName ?> | Native Starterkit</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</head>
<body>
    <div id="wrapper">
        <?php include 'template/sidebar.php'; ?>
        
        <div id="page-wrapper" class="gray-bg">
            <?php include 'template/header.php'; ?>
            
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>Structure: <?= $tableName ?></h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item">Generators</li>
                        <li class="breadcrumb-item"><a href="generator_table.php">Table Manager</a></li>
                        <li class="breadcrumb-item active"><strong>Structure</strong></li>
                    </ol>
                </div>
            </div>

            <div class="wrapper wrapper-content animated fadeInRight">
                
                <!-- Primary Key Management & Existing Columns -->
                <div class="row">
                    <div class="col-lg-12">
                         <?php
                            // Check if PK exists
                            $pkExists = false;
                            $pkColumns = [];
                            foreach ($columns as $col) {
                                if ($col['Key'] === 'PRI') {
                                    $pkExists = true;
                                    $pkColumns[] = $col['Field'];
                                }
                            }
                        ?>
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5>Table Structure</h5>
                                <div class="ibox-tools">
                                    <?php if ($pkExists): ?>
                                        <span class="badge badge-primary">PK: <?= implode(', ', $pkColumns) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">No Primary Key</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="ibox-content">
                                <form action="core/generator/manage_pk.php" method="POST" id="structureForm">
                                    <input type="hidden" name="table_name" value="<?= $tableName ?>">
                                    <input type="hidden" name="action" id="formAction" value="set">
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="submitSetPK()">
                                            <i class="fa fa-key"></i> Set Selected as Primary Key
                                        </button>
                                        <?php if ($pkExists): ?>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="submitDropPK()">
                                                <i class="fa fa-trash"></i> Drop Primary Key
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th width="40" class="text-center"><i class="fa fa-check-square-o"></i></th>
                                                    <th>Field</th>
                                                    <th>Type</th>
                                                    <th>Null</th>
                                                    <th>Key</th>
                                                    <th>Default</th>
                                                    <th class="text-center">AI</th>
                                                    <th>Extra</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($columns as $col): ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="checkbox" name="pk_columns[]" value="<?= $col['Field'] ?>" <?= $col['Key'] === 'PRI' ? 'checked' : '' ?>>
                                                    </td>
                                                    <td><?= $col['Field'] ?></td>
                                                    <td><?= $col['Type'] ?></td>
                                                    <td><?= $col['Null'] ?></td>
                                                    <td><?= $col['Key'] ?></td>
                                                    <td><?= $col['Default'] ?></td>
                                                    <td class="text-center">
                                                        <?php 
                                                            $isAI = strpos($col['Extra'], 'auto_increment') !== false;
                                                        ?>
                                                        <div class="switch">
                                                            <div class="onoffswitch">
                                                                <input type="checkbox" class="onoffswitch-checkbox" id="ai_<?= $col['Field'] ?>" <?= $isAI ? 'checked' : '' ?> onchange="toggleAI('<?= $tableName ?>', '<?= $col['Field'] ?>', this.checked)">
                                                                <label class="onoffswitch-label" for="ai_<?= $col['Field'] ?>">
                                                                    <span class="onoffswitch-inner"></span>
                                                                    <span class="onoffswitch-switch"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= $col['Extra'] ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-warning btn-xs" onclick="openModifyModal('<?= $col['Field'] ?>', '<?= $col['Type'] ?>')">
                                                            <i class="fa fa-edit"></i> Change
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-xs" onclick="confirmDropColumn('<?= $tableName ?>', '<?= $col['Field'] ?>')">
                                                            <i class="fa fa-trash"></i> Drop
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Columns Section -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox collapsed">
                            <div class="ibox-title">
                                <h5>Add New Columns</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </div>
                            </div>
                            <div class="ibox-content">
                                <form action="core/generator/add_column.php" method="POST">
                                    <input type="hidden" name="table_name" value="<?= $tableName ?>">
                                    
                                    <table class="table table-bordered" id="addColumnsTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Length/Values</th>
                                                <th>Default</th>
                                                <th class="text-center" width="50">Null</th>
                                                <th class="text-center" width="50">AI</th>
                                                <th width="50">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="addColumnsBody">
                                            <!-- Rows added via JS -->
                                        </tbody>
                                    </table>
                                    
                                    <button type="button" class="btn btn-info btn-sm" onclick="addColumnRow()">
                                        <i class="fa fa-plus"></i> Add Column
                                    </button>
                                    
                                    <div class="hr-line-dashed"></div>
                                    
                                    <button type="submit" class="btn btn-primary btn-block">All New Columns</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'template/footer.php'; ?>
        </div>
    </div>

    <!-- Modify Modal -->
    <div class="modal fade" id="modifyModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Modify Column</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="core/generator/modify_column.php" method="POST">
                        <input type="hidden" name="table_name" value="<?= $tableName ?>">
                        <input type="hidden" name="old_name" id="mod_old_name">
                        
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="mod_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Type Definition</label>
                            <input type="text" name="definition" id="mod_definition" class="form-control" required>
                            <small class="text-muted">Currently mostly manual. e.g. <code>VARCHAR(255) NOT NULL</code></small>
                            <!-- Future improvement: Break this down into Select Type, Length input, etc. -->
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- PK Modal -->
    <div class="modal fade" id="pkModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Set Primary Key</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="core/generator/manage_pk.php" method="POST">
                        <input type="hidden" name="table_name" value="<?= $tableName ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label>Select Columns</label>
                            <select name="pk_columns[]" class="form-control" multiple required style="height: 150px;">
                                <?php foreach ($columns as $col): ?>
                                    <option value="<?= $col['Field'] ?>"><?= $col['Field'] ?> (<?= $col['Type'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple columns for Composite PK.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Primary Key</button>
                    </form>
                </div>
            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let rowCount = 0;
        
        function addColumnRow() {
            rowCount++;
            const tbody = document.getElementById('addColumnsBody');
            const tr = document.createElement('tr');
            
            tr.innerHTML = `
                <td><input type="text" name="columns[${rowCount}][name]" class="form-control form-control-sm" required placeholder="Column Name"></td>
                <td>
                    <select name="columns[${rowCount}][type]" class="form-control form-control-sm">
                        <option value="INT">INT</option>
                        <option value="VARCHAR">VARCHAR</option>
                        <option value="TEXT">TEXT</option>
                        <option value="DATE">DATE</option>
                        <option value="DATETIME">DATETIME</option>
                        <option value="BOOLEAN">BOOLEAN</option>
                        <option value="DECIMAL">DECIMAL</option>
                        <option value="FLOAT">FLOAT</option>
                    </select>
                </td>
                <td><input type="text" name="columns[${rowCount}][length]" class="form-control form-control-sm" placeholder="Len/Val"></td>
                <td><input type="text" name="columns[${rowCount}][default_value]" class="form-control form-control-sm" placeholder="Default"></td>
                <td class="text-center"><input type="checkbox" name="columns[${rowCount}][is_null]" value="1"></td>
                <td class="text-center"><input type="checkbox" name="columns[${rowCount}][is_ai]" value="1"></td>
                <td><button type="button" class="btn btn-danger btn-xs" onclick="this.closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
            `;
            
            tbody.appendChild(tr);
        }

        // Add one empty row by default
        addColumnRow();

        function confirmDropColumn(table, column) {
            Swal.fire({
                title: 'Drop Column?',
                text: "Drop column '" + column + "' from table '" + table + "'?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, drop it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'core/generator/drop_column.php?table=' + table + '&column=' + column;
                }
            })
        }

        function openModifyModal(name, type) {
            $('#mod_old_name').val(name);
            $('#mod_name').val(name);
            $('#mod_definition').val(type); 
            $('#modifyModal').modal('show');
        }

        function submitSetPK() {
            // Check if any checked
            if ($('input[name="pk_columns[]"]:checked').length === 0) {
                toastr.error('Please select at least one column to set as Primary Key.');
                return;
            }
            $('#formAction').val('set');
            $('#structureForm').submit();
        }

        function toggleAI(table, column, isChecked) {
            const enable = isChecked ? 1 : 0;
            // Optional: Confirm if enabling?
            window.location.href = 'core/generator/toggle_ai.php?table=' + table + '&column=' + column + '&enable=' + enable;
        }

        function submitDropPK() {
            if (confirm('Are you sure you want to drop the Primary Key?')) {
                $('#formAction').val('drop');
                $('#structureForm').submit();
            }
        }
    </script>
</body>
</html>
