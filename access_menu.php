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
    <!-- select2 -->
    <link href="assets/css/plugins/select2/select2.min.css" rel="stylesheet">
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
                    <h2>Management Access Menu</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.html">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a>Tables</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <strong>Management Access Menu</strong>
                        </li>
                    </ol>
                </div>
                
                
            </div>
            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                
                                Management Access Menu
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
                                <form action="core/access_menu/add_access_menu.php" method="POST" enctype="multipart/form-data">
                                    
                                    <div class="form-group row"><label class="col-lg-2 col-form-label">Role</label>

                                        <div class="col-lg-6">
                                            <select class="form-control select2" name="role_id[]" id="role_id" multiple>
                                                <option value="">Pilih Role</option>
                                                <?php  
                                                    $list_role = mysqli_query($conn, "SELECT * FROM user_role WHERE role != 'SuperAdmin'");
                                                    foreach($list_role as $lr):?>
                                                        <option value="<?= $lr['id']?>"><?= $lr['role']?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-lg-4">
                                            
                                        </div>
                                    </div>
                                    <div class="form-group row"><label class="col-lg-2 col-form-label">Menu</label>

                                        <div class="col-lg-6">
                                            <select class="form-control select2" name="menu_id[]" id="menu_id" multiple>
                                                <option value="">Pilih Menu</option>
                                                <?php  
                                                    $list_menu = mysqli_query($conn, "SELECT * FROM user_menu ");
                                                    foreach($list_menu as $lm):?>
                                                        <option value="<?= $lm['id']?>"><?= $lm['menu']?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                        
                                        
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                            
                                        </div>
                                        
                                    </div>
                                </form>
                                
                            </div>
                        </div>
                    </div>
                    <!-- submenu default hide -->
                    <div class="col-lg-12">
                        <div class="ibox" id="submenuFormContainer" style="display: none;">
                            <div class="ibox-title">
                                Submenu
                                
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
                                <form action="core/menu/add_submenu.php" method="POST" enctype="multipart/form-data">
                                    <p>Sign in today for more expirience.</p>
                                    <div class="form-group row"><label class="col-lg-2 col-form-label">Pilih Menu</label>

                                        <div class="col-lg-6">
                                            <select class="form-control" name="menu_id">
                                                <option value="">Pilih Menu</option>
                                                <?php  
                                                    $list_menu = mysqli_query($conn, "SELECT * FROM user_menu ");
                                                    foreach($list_menu as $lm):?>
                                                        <option value="<?= $lm['id']?>"><?= $lm['menu']?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-lg-4">
                                            
                                        </div>
                                    </div>
                                    <div class="form-group row"><label class="col-lg-2 col-form-label">Nama Sub Menu</label>

                                        <div class="col-lg-6"><input type="text" placeholder="Nama Submenu" class="form-control" name="submenu"> 
                                        </div>
                                        
                                        
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                            
                                        </div>
                                        
                                    </div>
                                </form>
                                
                            </div>
                        </div>
                    </div>
                    <!-- end submenu default hide -->
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                               
                                <button class="btn btn-info btn-sm" id="">Management Access List</button>                               
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
                                            <th>Menu</th>
                                            <th>Submenu</th>
                                            <th>Action</th>
                                        </thead>
                                        <tbody>
                                            <?php  
                                                $list_access = mysqli_query($conn, "SELECT user_access_menu.id as id_access, user_role.role, user_menu.menu, user_sub_menu.title 
                                                                                    FROM `user_access_menu`
                                                                                    JOIN user_role ON user_role.id = user_access_menu.role_id
                                                                                    JOIN user_menu ON user_menu.id = user_access_menu.menu_id
                                                                                    LEFT JOIN user_sub_menu ON user_sub_menu.menu_id = user_access_menu.menu_id");
                                                $no = 1; 
                                                foreach($list_access as $la):
                                            ?>
                                                <tr>
                                                    <td><?= $no++?></td>
                                                    <td><?= $la['role']?></td>
                                                    <td><?= $la['menu']?></td>
                                                    <td>
                                                        <?php if($la['title'] == NULL):?>
                                                            <span class="badge badge-danger">No Submenu</span>
                                                            <?php else: ?>
                                                            <?= $la['title']?></td>
                                                        <?php endif; ?>
                                                    <td>
                                                        <a href="#" class="btn btn-danger btn-xs" onclick="confirmDeleteAccess(<?= $la['id_access']?>)">Revoke Access</a>
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
            <!-- end body content -->
            <!-- footer -->
                <?php include 'template/footer.php'; ?>
            <!-- end footer -->
        </div>
         <!-- modal submenu -->
        <div class="modal inmodal fade" id="ExampleModal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title">Ubah Pengguna</h4>
                        <small class="font-bold">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</small>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="core/menu/edit_submenu.php" enctype="multipart/form-data">
                            <p>Sign in today for more expirience.</p>
                                    <div class="form-group row"><label class="col-lg-2 col-form-label">Judul Submenu</label>
                                        <input type="hidden" name="id" value="" id="id_submenu">
                                        <input type="hidden" name="menu_id" value="" id="id_menu">               
                                        <div class="col-lg-10"><input type="text" placeholder="" class="form-control" name="title" id="title_submenu"> 
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
        <!-- end modal submenu -->                                                       
        
        <!-- modal menu -->
        <div class="modal inmodal fade" id="ExampleModalMenu" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title">Ubah Menu</h4>
                        <small class="font-bold">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</small>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="core/menu/edit_menu.php" enctype="multipart/form-data">
                            <p>Sign in today for more expirience.</p>
                                    <div class="form-group row"><label class="col-lg-2 col-form-label">Menu</label>
                                        
                                        <input type="hidden" name="id_menu" value="" id="edit_menu_id">               
                                        <div class="col-lg-10"><input type="text" placeholder="" class="form-control" name="menu" id="edit_menu_name"> 
                                        </div>
                                    </div>
                                    <div class="form-group row"><label class="col-lg-2 col-form-label">Icon</label>
                                        
                                                  
                                        <div class="col-lg-10"><input type="text" placeholder="" class="form-control" name="icon" id="edit_menu_icon"> 
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
        <!-- end modal menu -->
        
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
    <!-- select2 -->
    <script src="assets/js/plugins/select2/select2.full.min.js"></script>
    <script>
        function validateForm() {
            // Validasi untuk memastikan minimal satu role dan satu menu dipilih
            var selectedRoles = document.getElementsByName('role_id[]')[0].selectedOptions.length;
            var selectedMenus = document.getElementsByName('menu_id[]')[0].selectedOptions.length;

            if (selectedRoles === 0 || selectedMenus === 0) {
                alert("Silakan pilih minimal satu Role dan satu Menu.");
                return false;
            }

            return true;
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Silahkan Pilih",
                allowClear: true
            });
        });
    </script>
    <script>
        $(document).ready(function(){
            // Tangkap ID dan nilai Menu saat tombol "Edit Menu" diklik
            $('body').on('click', '.btn-info[data-target="#ExampleModalMenu"]', function() {
                var id_menu = $(this).data('id_menu');
                var menu_name = $(this).data('menu');
                var menu_icon = $(this).data('icon');

                // Isi nilai input di modal form dengan data yang sesuai
                $('#edit_menu_id').val(id_menu);
                $('#edit_menu_name').val(menu_name);
                $('#edit_menu_icon').val(menu_icon);
            });
        });
    </script>
    <script>
        $(document).ready(function(){
            $('#mydatatables').DataTable({
                // Konfigurasi DataTables
            });

            // Tangkap ID saat tombol "Edit" diklik
            $('#mydatatables').on('click', 'button[data-target="#EditmyModal5"]', function() {
                var id = $(this).data('id_user');
                var nama = $(this).data('nama');
                var email = $(this).data('email');
                $('#edituserid').val(id);
                $('#namaUser').val(nama);
                $('#emailUser').val(email);
            });
        });
    </script>
    <script>
        function confirmDelete(idSubmenu) {
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
                    // If user clicks "Yes," proceed with AJAX request to delete submenu
                    $.ajax({
                        url: 'core/menu/delete_submenu.php',
                        type: 'POST',
                        data: { id_submenu: idSubmenu },
                        success: function(response) {
                            // Handle success
                            location.reload(); // Reload the page
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            Swal.fire('Error!', 'Failed to delete submenu.', 'error');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }
        function confirmDeleteMenu(idmenu) {
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
                    // If user clicks "Yes," proceed with AJAX request to delete submenu
                    $.ajax({
                        url: 'core/menu/delete_menu.php',
                        type: 'POST',
                        data: { id_menu: idmenu },
                        success: function(response) {
                            // Handle success
                            location.reload(); // Reload the page
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            Swal.fire('Error!', 'Failed to delete submenu.', 'error');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }
        function confirmDeleteAccess(idaccess) {
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
                    // If user clicks "Yes," proceed with AJAX request to delete submenu
                    $.ajax({
                        url: 'core/access_menu/revoke_access.php',
                        type: 'POST',
                        data: { id_access: idaccess },
                        success: function(response) {
                            // Handle success
                            location.reload(); // Reload the page
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            Swal.fire('Error!', 'Failed to delete submenu.', 'error');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            // Tangkap elemen formulir dan tombol
            var submenuFormContainer = $('#submenuFormContainer');
            var toggleSubmenuFormButton = $('#toggleSubmenuForm');

            // Sembunyikan formulir secara default
            submenuFormContainer.hide();

            // Tambahkan event listener pada tombol
            toggleSubmenuFormButton.on('click', function () {
                // Toggle (sembunyikan/tampilkan) formulir saat tombol ditekan
                submenuFormContainer.toggle();
            });
        });
    </script>
    <script>
        $(document).ready(function(){
            $('#ExampleModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Tombol yang men-trigger modal
                var id_submenu = button.data('id_submenu'); // Mengambil data ID submenu
                var menu_id = button.data('menu-id'); // Mengambil data ID menu
                var title = button.data('title'); // Mengambil data judul submenu

                // Mengisi nilai ke dalam form modal
                $('#id_submenu').val(id_submenu);
                $('#id_menu').val(menu_id);
                $('#title_submenu').val(title);
            });
        });
    </script>

</body>
</html>
