<!--
*
*  INSPINIA - Responsive Admin Theme
*  version 2.9.2
*
-->
<?php 
    session_start();
    include('connection/connection.php');
    // Check apakah terdapat pesan notifikasi
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    $notificationType = $_SESSION['notification_type'];

    // Hapus variabel sesi setelah mengambil nilainya
    unset($_SESSION['notification']);
    unset($_SESSION['notification_type']);

    // Tampilkan notifikasi menggunakan JavaScript (Toastr)
    echo "<script>
            setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 4000
                };
                toastr.$notificationType('$notification'); // Perbaikan disini
            }, 1300);
          </script>";
}


?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>INSPINIA | Dashboard</title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">

    <!-- Toastr style -->
    <link href="assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">

    <!-- Gritter -->
    <link href="assets/js/plugins/gritter/jquery.gritter.css" rel="stylesheet">

    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.css" rel="stylesheet">
    <!-- Sweetalert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</head>
<?php include 'connection/connection.php';?>
<body>
    <div id="wrapper">
        <!-- sidebar --> 
            <?php include 'template/sidebar.php'; ?>
        <!-- end sidebar --> 

        
        <div id="page-wrapper" class="gray-bg dashboard-1">
            <!-- header -->
             <?php include 'template/header.php'; ?>
            <!-- end header --> 

            <!-- body content -->
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>Roles</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.html">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a>Tables</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <strong>Roles</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-2">
                    
                        
                </div>
                
            </div>
            <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal5">
								Tambah Role
						</button>
                        
                        <div class="ibox-tools">
                            
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-wrench"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-user">
                                <li><a href="#" class="dropdown-item">Config option 1</a>
                                </li>
                                <li><a href="#" class="dropdown-item">Config option 2</a>
                                </li>
                            </ul>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                            
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
							<table class="table table-striped table-bordered table-hover" id="mydatatables">
                                <thead>
                                    <th>No</th>
                                    <th>Role</th>
                                    
                                    <th>Action</th>
                                    
                                </thead>
                                <tbody>
                                    <?php $role = mysqli_query($conn, "SELECT * FROM user_role ");?>
                                    <?php $no = 1; foreach($role as $r):?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= $r['role']?></td>
                                        
                                        <td>
                                            
                                            <?php if($r['id'] == 1):?>
                                                <div class="alert alert-danger text-center">
                                                    Restricted. <a class="alert-link" href="#">Alert</a>.
                                                </div>
                                            <?php else :?>
                                                <a href="role_permissions.php?role_id=<?= $r['id'] ?>" class="btn btn-success btn-sm"><i class="fa fa-shield"></i> Permissions</a>
                                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#EditmyModal5" data-id="<?= $r['id'] ?>" data-role="<?= $r['role'] ?>">Edit</button>
                                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $r['id']; ?>)">Delete</button>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                    <?php endforeach;?>
                                </tbody>
                            </table>

						</div>
                        
                    </div>
                </div>
            </div>
            </div>
        </div>
            <!-- end body content -->
            <!-- footer -->
                <?php include 'template/footer.php'; ?>
            <!-- end footer -->
        </div> 
        
      <!-- Modal -->
    <div class="modal inmodal fade" id="myModal5" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Tambah Role</h4>
                    <small class="font-bold"></small>
                </div>
                <div class="modal-body">
                <form method="post" action="core/role/add_role.php" enctype="multipart/form-data">
                        
                        <div class="form-group row"><label class="col-lg-2 col-form-label">Nama Role</label>

                            <div class="col-lg-10"><input type="text" placeholder="nama" class="form-control" name="role"> 
                            </div>
                        </div>
                </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal inmodal fade" id="EditmyModal5" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Edit Role</h4>
                    <small class="font-bold">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</small>
                </div>
                <div class="modal-body">
                    <form method="post" action="core/role/edit_role.php" enctype="multipart/form-data">
                        <p>Sign in today for more experience.</p>
                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label">Nama Role</label>
                            <div class="col-lg-10">
                                <input type="hidden" name="id" id="editRoleId" value="">
                                <input type="text" placeholder="nama" class="form-control" name="role" id="namaRole"> 
                            </div>
                        </div>
                </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                        
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                    </form>
            </div>
        </div>
    </div>
  
        
    </div>

    <!-- Mainly scripts -->
    <script src="assets/js/jquery-3.1.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Flot -->
    <script src="assets/js/plugins/flot/jquery.flot.js"></script>
    <script src="assets/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
    <script src="assets/js/plugins/flot/jquery.flot.spline.js"></script>
    <script src="assets/js/plugins/flot/jquery.flot.resize.js"></script>
    <script src="assets/js/plugins/flot/jquery.flot.pie.js"></script>

    <!-- Peity -->
    <script src="assets/js/plugins/peity/jquery.peity.min.js"></script>
    <script src="assets/js/demo/peity-demo.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="assets/js/inspinia.js"></script>
    <script src="assets/js/plugins/pace/pace.min.js"></script>

    <!-- jQuery UI -->
    <script src="assets/js/plugins/jquery-ui/jquery-ui.min.js"></script>

    <!-- GITTER -->
    <script src="assets/js/plugins/gritter/jquery.gritter.min.js"></script>

    <!-- Sparkline -->
    <script src="assets/js/plugins/sparkline/jquery.sparkline.min.js"></script>

    <!-- Sparkline demo data  -->
    <script src="assets/js/demo/sparkline-demo.js"></script>

    <!-- ChartJS-->
    <script src="assets/js/plugins/chartJs/Chart.min.js"></script>

    <!-- Toastr -->
    <script src="assets/js/plugins/toastr/toastr.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.js"></script>
    <!-- sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function(){
            $('#mydatatables').DataTable({
                // Konfigurasi DataTables
            });

            // Tangkap ID saat tombol "Edit" diklik
            $('#mydatatables').on('click', 'button[data-target="#EditmyModal5"]', function() {
                var id = $(this).data('id');
                var role = $(this).data('role');
                $('#editRoleId').val(id);
                $('#namaRole').val(role);
            });
        });
    </script>
    <script>
        function confirmDelete(roleId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user clicks "Yes," proceed with AJAX request to delete
                    $.ajax({
                        url: 'core/role/delete_role.php',
                        type: 'POST',
                        data: { id: roleId },
                        success: function(response) {
                            // Handle success
                            // Swal.fire('Deleted!', 'Role has been deleted.', 'success');
                            // Optionally, you can reload the page or update the table here
                            location.reload(); // Reload the page
                            // OR
                            // $('#mydatatables').DataTable().ajax.reload(); // Update DataTable
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            Swal.fire('Error!', 'Failed to delete role.', 'error');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }
    </script>

    
</body>
</html>
