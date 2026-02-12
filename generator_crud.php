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

// Fetch all tables for initial dropdown and for Relation Reference
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
    <title>CRUD Generator | Native Starterkit</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/plugins/select2/select2.min.css" rel="stylesheet">
    <style>
        .relation-config { display: none; }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include 'template/sidebar.php'; ?>
        
        <div id="page-wrapper" class="gray-bg">
            <?php include 'template/header.php'; ?>
            
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>CRUD Generator</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item">Generators</li>
                        <li class="breadcrumb-item active"><strong>CRUD Generator</strong></li>
                    </ol>
                </div>
            </div>

            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5>Configuration</h5>
                            </div>
                            <div class="ibox-content">
                                <form action="core/generator/generate_crud.php" method="POST" id="crudForm">
                                    
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Select Table</label>
                                        <div class="col-lg-4">
                                            <select name="table_name" id="table_select" class="form-control select2" required onchange="loadColumns()">
                                                <option value="">-- Choose Table --</option>
                                                <?php foreach ($tables as $tb): ?>
                                                    <option value="<?= $tb ?>"><?= $tb ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <label class="col-lg-2 col-form-label">Module Title</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="module_title" class="form-control" placeholder="e.g. Employee Management" required>
                                        </div>
                                    </div>
                                    
                                    <div class="hr-line-dashed"></div>
                                    
                                    <h3>Column Configuration</h3>
                                    <p class="text-muted small">Configure which columns to show and if they are related to other tables.</p>

                                        <table class="table table-bordered table-striped" id="configTable">
                                            <thead>
                                                <tr>
                                                    <th width="5%"><input type="checkbox" id="checkAll" checked></th>
                                                    <th width="15%">Column</th>
                                                    <th width="15%">Input Type</th>
                                                    <th width="25%">Validation</th>
                                                    <th width="40%">Relationship (Join)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="columnsBody">
                                                <tr>
                                                    <td colspan="5" class="text-center">Select a table to load columns...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    
                                    <div class="hr-line-dashed"></div>
                                    
                                    

                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fa fa-magic"></i> Generate CRUD
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CRUD History Section -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5><i class="fa fa-history"></i> CRUD History</h5>
                            </div>
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="historyTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="20%">Module Title</th>
                                                <th width="20%">Table Name</th>
                                                <th width="15%">File Name</th>
                                                <th width="20%">Created</th>
                                                <th width="20%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                // Check if crud_history table exists
                                                $tableExists = $conn->query("SHOW TABLES LIKE 'crud_history'");
                                                if ($tableExists && $tableExists->num_rows > 0) {
                                                    $historyResult = $conn->query("SELECT * FROM crud_history ORDER BY created_at DESC");
                                                    $no = 1;
                                                    if ($historyResult && $historyResult->num_rows > 0) {
                                                        while ($row = $historyResult->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><strong><?= htmlspecialchars($row['module_title']) ?></strong></td>
                                                    <td><code><?= htmlspecialchars($row['table_name']) ?></code></td>
                                                    <td>
                                                        <a href="<?= $row['file_name'] ?>.php" target="_blank">
                                                            <?= $row['file_name'] ?>.php
                                                        </a>
                                                    </td>
                                                    <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                                    <td>
                                                        <a href="<?= $row['file_name'] ?>.php" class="btn btn-primary btn-xs">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                        <button class="btn btn-danger btn-xs" 
                                                                onclick="confirmDeleteCrud(<?= $row['id'] ?>, '<?= htmlspecialchars($row['module_title']) ?>', '<?= htmlspecialchars($row['table_name']) ?>')">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php 
                                                        endwhile;
                                                    } else {
                                            ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No CRUD history found. Generate your first CRUD above!</td>
                                                </tr>
                                            <?php 
                                                    }
                                                } else {
                                            ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">CRUD history table not found. Please run the database migrations.</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="height: 50px;"></div>
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
    <script src="assets/js/plugins/select2/select2.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Delete CRUD confirmation with option to drop table
        function confirmDeleteCrud(historyId, moduleTitle, tableName) {
            Swal.fire({
                title: 'Delete CRUD: ' + moduleTitle + '?',
                html: `
                    <p>This will delete:</p>
                    <ul style="text-align:left; margin-left:20px;">
                        <li>View file (${moduleTitle.toLowerCase().replace(/\s+/g, '_')}.php)</li>
                        <li>Core folder (core/${moduleTitle.toLowerCase().replace(/\s+/g, '_')}/)</li>
                        <li>Sidebar menu entry</li>
                    </ul>
                    <hr>
                    <label style="cursor:pointer;">
                        <input type="checkbox" id="dropTableCheck"> 
                        <strong style="color:#d33;">Also DROP the database table "${tableName}"</strong>
                    </label>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    var dropTable = document.getElementById('dropTableCheck').checked ? '1' : '0';
                    
                    // Create form and submit
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'core/generator/delete_crud.php';
                    
                    var inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'history_id';
                    inputId.value = historyId;
                    form.appendChild(inputId);
                    
                    var inputDrop = document.createElement('input');
                    inputDrop.type = 'hidden';
                    inputDrop.name = 'drop_table';
                    inputDrop.value = dropTable;
                    form.appendChild(inputDrop);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>

    <script>
        $(document).ready(function(){
            $(".select2").select2();
            
            $('#checkAll').change(function(){
                $('.col-check').prop('checked', $(this).prop('checked'));
            });
        });

        // Pre-load tables for reference dropdown (reuse existing list)
        const allTables = <?= json_encode($tables) ?>;

        function loadColumns() {
            var table = $('#table_select').val();
            if (!table) {
                $('#columnsBody').html('<tr><td colspan="4" class="text-center">Select a table to load columns...</td></tr>');
                return;
            }

            $.ajax({
                url: 'core/generator/get_table_columns.php',
                type: 'GET',
                data: { table: table },
                dataType: 'json',
                success: function(data) {
                    var html = '';
                    $.each(data, function(i, col) {
                        var isPri = (col.Key === 'PRI');
                        
                        html += '<tr>';
                        
                        // Checkbox
                        html += '<td class="text-center">';
                        html += '<input type="checkbox" name="cols['+col.Field+'][include]" class="col-check" value="1" checked>';
                        html += '<input type="hidden" name="cols['+col.Field+'][type]" value="'+col.Type+'">';
                        html += '<input type="hidden" name="cols['+col.Field+'][key]" value="'+col.Key+'">';
                        html += '</td>';
                        
                        // Column & Type
                        html += '<td><strong>'+col.Field+'</strong>' + (isPri ? ' <span class="badge badge-warning">PK</span>' : '') + '<br><small>'+col.Type+'</small></td>';
                        
                        // Input Type
                        html += '<td>';
                        html += '<select name="cols['+col.Field+'][input_type]" class="form-control form-control-sm TypeSelect" onchange="checkInputType(this, \''+col.Field+'\')">';
                        html += '<option value="text">Text</option>';
                        html += '<option value="number">Number</option>';
                        html += '<option value="email">Email</option>';
                        html += '<option value="password">Password</option>';
                        html += '<option value="textarea">Textarea</option>';
                        html += '<option value="date">Date</option>';
                        html += '<option value="datetime-local">DateTime</option>';
                        html += '<option value="select">Select / Dropdown</option>';
                        html += '<option value="radio">Radio Button</option>';
                        html += '<option value="file">File Upload</option>';
                        html += '<option value="file_dropzone">File Upload (Dropzone)</option>';
                        // html += '<option value="checkbox">Checkbox</option>'; // Handling checkbox for single boolean vs multi-select is complex. Skip for now or simplify.
                        html += '</select>';
                        html += '<div class="file-config" id="file_config_'+col.Field+'" style="display:none; margin-top:5px;">';
                        html += '<div class="row">';
                        html += '<div class="col-md-6">';
                        html += '<small>Allowed Extensions:</small>';
                        html += '<input type="text" name="cols['+col.Field+'][file_extensions]" class="form-control form-control-sm" placeholder="jpg,png,pdf" value="jpg,jpeg,png,gif,pdf">';
                        html += '</div>';
                        html += '<div class="col-md-6">';
                        html += '<small>Max Size (MB):</small>';
                        html += '<input type="number" name="cols['+col.Field+'][file_max_size]" class="form-control form-control-sm" placeholder="2" value="2">';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="checkbox" style="margin-top:5px;">';
                        html += '<label><input type="checkbox" name="cols['+col.Field+'][file_multiple]" value="1"> Allow Multiple Files</label>';
                        html += '</div>';
                        html += '</div>';
                        html += '</td>';

                        // Validation
                        html += '<td>';
                        html += '<select name="cols['+col.Field+'][validations][]" class="form-control form-control-sm select2" multiple>';
                        html += '<option value="required">Required</option>';
                        html += '<option value="numeric">Is Numeric</option>';
                        html += '<option value="email">Is Email</option>';
                        html += '<option value="rupiah">Rupiah Format</option>';
                        // html += '<option value="unique">Is Unique</option>'; // Needs DB check logic, maybe later
                        html += '</select>';
                        html += '</td>';
                        
                        // Relationship
                        html += '<td>';
                        html += '<div class="checkbox"><label><input type="checkbox" class="rel-check" name="cols['+col.Field+'][is_relation]" value="1" onchange="toggleRelation(this, \''+col.Field+'\')"> Is Relation?</label></div>';
                        
                        html += '<div class="relation-config" id="rel_config_'+col.Field+'">';
                        html += '<div class="row">';
                        html += '<div class="col-md-6"><small>Ref Table</small><select name="cols['+col.Field+'][ref_table]" class="form-control form-control-sm" onchange="loadRefColumns(this, \''+col.Field+'\')"><option value="">-- Select --</option>';
                        allTables.forEach(function(t) { html += '<option value="'+t+'">'+t+'</option>'; });
                        html += '</select></div>';
                        html += '<div class="col-md-6"><small>Display</small><select name="cols['+col.Field+'][ref_col]" id="ref_col_'+col.Field+'" class="form-control form-control-sm"><option value="">-- Select First --</option></select></div>';
                        html += '</div></div>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    $('#columnsBody').html(html);
                    // Re-init select2 for new elements
                    $(".select2").select2({ width: '100%' });
                },
                error: function() {
                    alert('Error fetching columns.');
                }
            });
        }

        function toggleRelation(checkbox, field) {
            if (checkbox.checked) {
                $('#rel_config_'+field).slideDown();
            } else {
                $('#rel_config_'+field).slideUp();
            }
        }

        function loadRefColumns(select, field) {
            var refTable = $(select).val();
            var targetSelect = $('#ref_col_'+field);
            
            if (!refTable) {
                targetSelect.html('<option value="">-- Select Ref Table First --</option>');
                return;
            }

            $.ajax({
                url: 'core/generator/get_table_columns.php',
                type: 'GET',
                data: { table: refTable },
                dataType: 'json',
                success: function(data) {
                    var html = '<option value="">-- Choose Column --</option>';
                    $.each(data, function(i, col) {
                        html += '<option value="'+col.Field+'">'+col.Field+'</option>';
                    });
                    targetSelect.html(html);
                }
            });
        }

        function checkInputType(select, field) {
            var val = $(select).val();
            if (val === 'file' || val === 'file_dropzone') {
                $('#file_config_'+field).slideDown();
            } else {
                $('#file_config_'+field).slideUp();
            }
        }
    </script>
</body>
</html>
