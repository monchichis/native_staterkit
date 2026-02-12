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
            <?php 
            $id_user = $_SESSION['user_id'];
            $list_user = mysqli_query($conn, "SELECT * FROM mst_user WHERE id_user = '$id_user'");?>
            <?php foreach($list_user as $lu):?>
            <!-- body content -->
            <div class="row  border-bottom white-bg dashboard-header">
                <div class="wrapper wrapper-content animated fadeInRight">
                    <div class="row m-b-lg m-t-lg">
                        <div class="col-md-6">

                            <div class="profile-image">
                                <img src="core/<?= $lu['image']?>" class="rounded-circle circle-border m-b-md" alt="profile">
                            </div>
                            <div class="profile-info">
                                <div class="">
                                    <div>
                                        <h2 class="no-margins">
                                            <?= $lu['nama']?>
                                        </h2>
                                        <h4>Founder of Group</h4>
                                        <small>
                                            There are many variations of passages of Lorem Ipsum available, but the majority
                                            have suffered alteration in some form Ipsum available.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        


                    </div>
                    <div class="row">

                        <div class="col-lg-4">

                            <div class="ibox">
                                <div class="ibox-content">
                                        <h3>About <?= $lu['nama']?></h3>

                                    <p class="small">
                                        There are many variations of passages of Lorem Ipsum available, but the majority have
                                        suffered alteration in some form, by injected humour, or randomised words which don't.
                                        <br>
                                        <br>
                                        If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't
                                        anything embarrassing
                                    </p>

                                    <p class="small font-bold">
                                        <span><i class="fa fa-circle text-navy"></i> Online status</span>
                                    </p>

                                </div>
                            </div>

                            

                           

                        </div>
                        <div class="col-lg-4">
                             <div class="ibox">
                                <div class="ibox-content">
                                    <form method="POST" action="core/update_profile.php" enctype="multipart/form-data">
                                        <h3>Data Pribadi</h3>
                                        <div class="form-group">
                                            <label>Nama</label>
                                            <input type="hidden" name="id_user" value="<?= $lu['id_user']?>">
                                            <input type="text" class="form-control" placeholder="" name="nama" value="<?= $lu['nama']?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Foto</label>
                                            <input type="file" class="form-control" name="foto">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">Send</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                             <div class="ibox">
                                <div class="ibox-content">
                                    <form method="POST" action="core/update_password.php" enctype="multipart/form-data">
                                        <h3>Security</h3>
                                        <div class="form-group">
                                            <label>Password Lama</label>
                                            <input type="password" class="form-control" placeholder="" name="old_password">
                                        </div>
                                        <div class="form-group">
                                            <label>Password Baru</label>
                                            <input type="password" class="form-control" placeholder="" name="new_password">
                                        </div>
                                        <div class="form-group">
                                            <label>Confirm Password Baru</label>
                                            <input type="password" class="form-control" placeholder="" name="confirm_new_password">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">Send</button>  
                                    </form>
                                    

                                </div>
                            </div>
                        </div>

                       

                    </div>
                </div>   
            </div>
            <!-- end body content -->
            <?php endforeach;?>
            <!-- footer -->
                <?php include 'template/footer.php'; ?>
            <!-- end footer -->
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


    
</body>
</html>


