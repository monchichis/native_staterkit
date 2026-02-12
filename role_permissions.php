<?php
session_start();
include('connection/connection.php');

// Security Check - SuperAdmin only
if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'SuperAdmin') {
    $_SESSION['notification'] = "Unauthorized access.";
    $_SESSION['notification_type'] = "error";
    header("Location: index.php");
    exit();
}

// Get role_id from URL
$roleId = isset($_GET['role_id']) ? intval($_GET['role_id']) : 0;

// Get role info
$roleName = '';
if ($roleId) {
    $stmt = $conn->prepare("SELECT role FROM user_role WHERE id = ?");
    $stmt->bind_param("i", $roleId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $roleName = $row['role'];
    }
    $stmt->close();
}

// Get all roles for dropdown
$roles = [];
$result = $conn->query("SELECT id, role FROM user_role WHERE role != 'SuperAdmin' ORDER BY role");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
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
    <title>Role Permissions | Native Starterkit</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/plugins/select2/select2.min.css" rel="stylesheet">
    <style>
        .permission-matrix th, .permission-matrix td {
            text-align: center;
            vertical-align: middle;
        }
        .permission-matrix th:first-child,
        .permission-matrix td:first-child {
            text-align: left;
        }
        .permission-check {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .module-row:hover {
            background-color: #f5f5f5;
        }
        .btn-check-all {
            font-size: 11px;
            padding: 2px 8px;
        }
        .add-module-form {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include 'template/sidebar.php'; ?>
        
        <div id="page-wrapper" class="gray-bg">
            <?php include 'template/header.php'; ?>
            
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>Role Permissions</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="list_role.php">Roles</a></li>
                        <li class="breadcrumb-item active"><strong>Permissions</strong></li>
                    </ol>
                </div>
            </div>

            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5><i class="fa fa-shield"></i> Permission Management</h5>
                            </div>
                            <div class="ibox-content">
                                
                                <!-- Role Selection -->
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label">Select Role</label>
                                    <div class="col-lg-4">
                                        <select id="role_select" class="form-control select2" onchange="loadPermissions()">
                                            <option value="">-- Choose Role --</option>
                                            <?php foreach ($roles as $r): ?>
                                                <option value="<?= $r['id'] ?>" <?= $roleId == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['role']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <span id="role_info" class="text-muted"></span>
                                    </div>
                                </div>

                                <div class="hr-line-dashed"></div>

                                <!-- Add Custom Module -->
                                <div class="add-module-form" id="addModuleForm" style="display: none;">
                                    <h5><i class="fa fa-plus"></i> Add Custom Module</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" id="new_module_name" class="form-control" placeholder="Module name (lowercase, no spaces)">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" id="new_module_display" class="form-control" placeholder="Display name">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-success" onclick="addCustomModule()">
                                                <i class="fa fa-plus"></i> Add Module
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Permission Matrix -->
                                <div id="permissionContainer" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered permission-matrix" id="permissionTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="40%">
                                                        Module
                                                        <button class="btn btn-outline-primary btn-check-all ml-2" onclick="toggleAddModule()">
                                                            <i class="fa fa-plus"></i> Add
                                                        </button>
                                                    </th>
                                                    <th width="15%">
                                                        View
                                                        <br><button class="btn btn-xs btn-outline-secondary btn-check-all" onclick="checkAllColumn('view')">All</button>
                                                    </th>
                                                    <th width="15%">
                                                        Create
                                                        <br><button class="btn btn-xs btn-outline-secondary btn-check-all" onclick="checkAllColumn('create')">All</button>
                                                    </th>
                                                    <th width="15%">
                                                        Update
                                                        <br><button class="btn btn-xs btn-outline-secondary btn-check-all" onclick="checkAllColumn('update')">All</button>
                                                    </th>
                                                    <th width="15%">
                                                        Delete
                                                        <br><button class="btn btn-xs btn-outline-secondary btn-check-all" onclick="checkAllColumn('delete')">All</button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="permissionBody">
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Select a role to load permissions...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="hr-line-dashed"></div>

                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right">
                                            <a href="list_role.php" class="btn btn-white">
                                                <i class="fa fa-arrow-left"></i> Back to Roles
                                            </a>
                                            <button type="button" class="btn btn-primary btn-lg" onclick="savePermissions()">
                                                <i class="fa fa-save"></i> Save Permissions
                                            </button>
                                        </div>
                                    </div>
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
    <script src="assets/js/plugins/select2/select2.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let currentModules = [];
        let currentPermissions = {};

        $(document).ready(function() {
            $(".select2").select2();
            
            // Auto-load if role_id is in URL
            <?php if ($roleId): ?>
            loadPermissions();
            <?php endif; ?>
        });

        function loadPermissions() {
            var roleId = $('#role_select').val();
            if (!roleId) {
                $('#permissionContainer').hide();
                $('#permissionBody').html('<tr><td colspan="5" class="text-center text-muted">Select a role to load permissions...</td></tr>');
                return;
            }

            // Update URL without reload
            history.pushState(null, '', 'role_permissions.php?role_id=' + roleId);

            // First load modules
            $.ajax({
                url: 'core/permission/get_modules.php',
                type: 'GET',
                dataType: 'json',
                success: function(moduleData) {
                    if (moduleData.error) {
                        toastr.error(moduleData.error);
                        return;
                    }
                    currentModules = moduleData.modules;

                    // Then load permissions for this role
                    $.ajax({
                        url: 'core/permission/get_permissions.php',
                        type: 'GET',
                        data: { role_id: roleId },
                        dataType: 'json',
                        success: function(permData) {
                            if (permData.error) {
                                toastr.error(permData.error);
                                return;
                            }
                            currentPermissions = permData.permissions;
                            renderPermissionMatrix();
                            $('#permissionContainer').show();
                        },
                        error: function() {
                            toastr.error('Failed to load permissions');
                        }
                    });
                },
                error: function() {
                    toastr.error('Failed to load modules');
                }
            });
        }

        function renderPermissionMatrix() {
            var html = '';

            if (currentModules.length === 0) {
                html = '<tr><td colspan="5" class="text-center text-muted">No modules found. Add modules via Menu Management first.</td></tr>';
            } else {
                currentModules.forEach(function(module) {
                    var perms = currentPermissions[module.name] || { view: false, create: false, update: false, delete: false };
                    
                    html += '<tr class="module-row" data-module="' + module.name + '">';
                    html += '<td><strong>' + module.display + '</strong><br><small class="text-muted">' + module.name + '</small></td>';
                    html += '<td><input type="checkbox" class="permission-check perm-view" ' + (perms.view ? 'checked' : '') + '></td>';
                    html += '<td><input type="checkbox" class="permission-check perm-create" ' + (perms.create ? 'checked' : '') + '></td>';
                    html += '<td><input type="checkbox" class="permission-check perm-update" ' + (perms.update ? 'checked' : '') + '></td>';
                    html += '<td><input type="checkbox" class="permission-check perm-delete" ' + (perms.delete ? 'checked' : '') + '></td>';
                    html += '</tr>';
                });
            }

            $('#permissionBody').html(html);
        }

        function checkAllColumn(action) {
            var checkboxes = $('.perm-' + action);
            var allChecked = checkboxes.filter(':checked').length === checkboxes.length;
            checkboxes.prop('checked', !allChecked);
        }

        function toggleAddModule() {
            $('#addModuleForm').slideToggle();
        }

        function addCustomModule() {
            var name = $('#new_module_name').val().trim().toLowerCase().replace(/\s+/g, '_');
            var display = $('#new_module_display').val().trim();

            if (!name || !display) {
                toastr.warning('Please enter both module name and display name');
                return;
            }

            // Check if already exists
            var exists = currentModules.some(m => m.name === name);
            if (exists) {
                toastr.warning('Module already exists');
                return;
            }

            // Add to list
            currentModules.push({ name: name, display: display });
            renderPermissionMatrix();

            // Clear inputs
            $('#new_module_name').val('');
            $('#new_module_display').val('');
            $('#addModuleForm').slideUp();

            toastr.success('Module added. Don\'t forget to save!');
        }

        function savePermissions() {
            var roleId = $('#role_select').val();
            if (!roleId) {
                toastr.warning('Please select a role first');
                return;
            }

            // Collect permissions from checkboxes
            var permissions = {};
            $('#permissionBody tr.module-row').each(function() {
                var moduleName = $(this).data('module');
                permissions[moduleName] = {
                    view: $(this).find('.perm-view').is(':checked') ? 1 : 0,
                    create: $(this).find('.perm-create').is(':checked') ? 1 : 0,
                    update: $(this).find('.perm-update').is(':checked') ? 1 : 0,
                    delete: $(this).find('.perm-delete').is(':checked') ? 1 : 0
                };
            });

            Swal.fire({
                title: 'Save Permissions?',
                text: 'This will update the permissions for this role.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'core/permission/save_permissions.php',
                        type: 'POST',
                        data: { role_id: roleId, permissions: permissions },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.error || 'Failed to save permissions');
                            }
                        },
                        error: function() {
                            toastr.error('Failed to save permissions');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
