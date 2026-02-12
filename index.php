<?php
session_start();
ini_set('display_errors', 0);

// Nonaktifkan error reporting
error_reporting(0);
include('check_session.php');
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
    function checkInternetConnection($url = 'https://www.google.com') {
        $timeout = 5; // seconds

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return ($httpCode >= 200 && $httpCode < 300);
    }
?>

<?php

include('connection/connection.php'); 
include('core/count_table.php');
include('core/software_info.php');
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>INSPINIA</title>

    <?php include 'assets/php_mode/css.php';?>

</head>

<body class="gray-bg">
    <style>
        /* Responsive Login Page Styles */
        .loginColumns {
            padding: 15px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .loginColumns .row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-logo-section {
            text-align: center;
            padding: 20px;
        }
        
        .login-logo-section h2 {
            color: #1ab394;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        
        .login-logo-section .img-fluid {
            max-width: 280px;
            width: 100%;
            height: auto;
            margin: 0 auto;
            display: block;
        }
        
        .login-form-section {
            padding: 20px;
        }
        
        .login-form-section .ibox-content {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .login-form-section .form-control {
            height: 45px;
            font-size: 16px;
        }
        
        .login-form-section .btn {
            height: 45px;
            font-size: 16px;
        }
        
        /* Tablet styles */
        @media (max-width: 992px) {
            .loginColumns .row {
                min-height: auto;
                padding: 20px 0;
            }
            
            .login-logo-section h2 {
                font-size: 1.5rem;
            }
            
            .login-logo-section .img-fluid {
                max-width: 200px;
            }
        }
        
        /* Mobile styles */
        @media (max-width: 768px) {
            .loginColumns {
                padding: 10px;
            }
            
            .loginColumns .row {
                flex-direction: column;
                min-height: auto;
            }
            
            .login-logo-section {
                padding: 15px 10px;
                order: 1;
            }
            
            .login-logo-section h2 {
                font-size: 1.3rem;
                margin-bottom: 15px;
            }
            
            .login-logo-section .img-fluid {
                max-width: 150px;
            }
            
            .login-form-section {
                padding: 10px;
                order: 2;
                width: 100%;
            }
            
            .login-form-section .ibox-content {
                padding: 20px;
            }
            
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        
        /* Small mobile styles */
        @media (max-width: 480px) {
            .login-logo-section h2 {
                font-size: 1.1rem;
            }
            
            .login-logo-section .img-fluid {
                max-width: 120px;
            }
            
            .login-form-section .ibox-content {
                padding: 15px;
            }
            
            .login-form-section .form-control {
                height: 40px;
                font-size: 14px;
            }
            
            .login-form-section .btn {
                height: 40px;
                font-size: 14px;
            }
        }
    </style>

    <div class="loginColumns animated fadeInDown">
        <div class="row">
            
            <?php
                $num_rows = 0;
                $list_aplikasi = false;

                // Check if connection is valid before querying
                if (isset($conn) && $conn !== false) {
                     // Wrap query in try-catch or silence warnings if table doesn't exist
                     try {
                        $list_aplikasi = @mysqli_query($conn, "SELECT * FROM tbl_aplikasi ORDER BY id DESC LIMIT 1");
                        if ($list_aplikasi) {
                            $num_rows = mysqli_num_rows($list_aplikasi);
                        }
                     } catch (Exception $e) {
                         // Table might not exist yet
                     }
                }
            ?>
            
            <?php if ($num_rows > 0 && isset($conn) && $conn !== false): ?>
                <?php foreach($list_aplikasi as $identitas):?>
                
                <div class="col-md-6 login-logo-section">
                    <h2 class="font-bold">Welcome to <?= $identitas['nama_aplikasi']?></h2>

                    <p>
                        <img class="img-fluid" src="core/logo_aplikasi/<?= $identitas['logo']?>" alt="">
                    </p>
                </div>
                
                <?php endforeach ?>
            <?php endif; ?>
            
            <?php if ($num_rows > 0 && isset($conn) && $conn !== false) : ?>
                <div class="col-md-6 login-form-section">
                    <div class="ibox-content">
                        <form class="m-t" method="post" action="core/login.php" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Application Username">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Application Password">
                            </div>
                           
                                <!-- // Contoh penggunaan -->
                                <?php if (checkInternetConnection()) :?>
                                    <button type="submit" class="btn btn-primary block full-width m-b">Login</button>
                                <?php else : ?>
                                    <a href="" class="btn btn-warning block full-width m-b"><i class="fa fa-close"> Disconnected</a></i>
                                <?php endif; ?>

                           
                        </form>
                    </div>
                </div>
            <?php else : ?>
            <?php  
                $file_path = 'connection/connection.php';
                // Logic upgrade: Check if file exists AND connection is valid
                if (file_exists($file_path) && isset($conn) && $conn !== false) :
                    // Only run this if connection is good
                    $jumlahTabel = 0;
                    try {
                        $jumlahTabel = countTables($conn, $database);
                    } catch (Exception $e) { $jumlahTabel = 0; }
                    
                    if ($jumlahTabel > 0) :
                ?>
                        <div class="col-md-6">
                            <div class="ibox-content">
                                <h2 class="font-bold">Welcome to Setup</h2>
                                <small>Machine Info</small>
                                <ul class="todo-list m-t ui-sortable">
                                    <li>

                                        <span class="m-l-xs">Webserver.</span>
                                        <small class="label label-primary"> <?= $infoWebServer ?></small>
                                    </li>
                                    <li>
                                       
                                        <span class="m-l-xs">PHP Version.</span>
                                        <small class="label label-info"> <?= $phpVersion ?></small>
                                    </li>
                                    <li>
                                        
                                        <span class="m-l-xs">Your IP Public.</span>
                                        <small class="label label-warning"> <?= $publicIP ?></small>
                                    </li>
                                    <li>
                                        
                                        <span class="m-l-xs">Database.</span>
                                        <small class="label label-danger"> <?= $conn->server_info; ?></small>
                                    </li>
                                    <li>
                                        
                                        <span class="m-l-xs">Default Username & Password.</span>
                                        <small class="label label-danger"> superadmin@app.com => admin</small>
                                    </li>
                                    <li>
                                        
                                        <span class="m-l-xs">Connection.</span>
                                         <?php if (checkInternetConnection()) :?>
                                            <small class="label label-success"> <i class="fa fa-check"></i></small>
                                        <?php else : ?>
                                        <small class="label label-danger"><i class="fa fa-close"></i> </small>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ibox-content">
                                <form class="m-t" method="post" action="core/setup_aplikasi.php" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Nama Aplikasi</label>
                                        <input type="text" name="nama_aplikasi" class="form-control" placeholder="Application Name" required="">
                                    </div>
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea rows="" cols="" name="alamat" class="form-control"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Telp</label>
                                        <input type="text" name="telp" class="form-control" placeholder="" required="">
                                    </div>
                                    <div class="form-group">
                                        <label>Nama Developer</label>
                                        <input type="text" name="nama_developer" class="form-control" placeholder="" required="">
                                    </div>
                                    <div class="form-group">
                                        <label>Logo</label>
                                        <input type="file" name="logo" class="form-control" placeholder="" required="">
                                    </div>
                                     <?php if (checkInternetConnection()) :?>
                                        <button type="submit" class="btn btn-primary block full-width m-b">Setup</button>
                                    <?php else : ?>
                                        <a href="" class="btn btn-warning block full-width m-b"><i class="fa fa-close"> Disconnected</a></i>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="ibox ">
                                <div class="ibox-title">
                                    <h5>Instalation Instructions</h5>
                                </div>

                                <div class="ibox-content">

                                    <div class="activity-stream">
                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-flash bg-primary"></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Make sure you have a internet Connection. <i class="fa fa-check bg-primary"></i>
                                            </div>
                                        </div>

                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-database bg-primary"></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Create a new database. <i class="fa fa-check bg-primary"></i>
                                            </div>
                                        </div>
                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-upload bg-primary"></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Choose & Upload core.sql file there are on folder native_staterkit/database. <i class="fa fa-check bg-primary"></i>
                                            </div>
                                        </div>
                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-cog"></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Setup Your Application. 
                                            </div>
                                        </div>
                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-send "></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Finish. you can login now.
                                            </div>
                                        </div>
                                        


                                    </div>

                                </div>
                            </div>
                        </div>
                <?php else : ?>
                        <div class="col-md-6">
                            <div class="ibox-content">
                                <?php if (checkInternetConnection()) :?>
                                <div style="position: relative; width: 100%; height: 0; padding-top: 56.2225%;
                                padding-bottom: 0;  margin-top: 1.6em; margin-bottom: 0.9em; overflow: hidden;
                                border-radius: 8px; will-change: transform;">
                                    <iframe loading="lazy" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: none; padding: 0;margin: 0;"
                                        src="https:&#x2F;&#x2F;www.canva.com&#x2F;design&#x2F;DAF7WUfE_0Q&#x2F;view?embed" allowfullscreen="allowfullscreen" allow="fullscreen">
                                    </iframe>
                                </div>
                                <?php else : ?>
                                    <div class="alert alert-danger">
                                        <i class="fa fa-close"></i> No Internet Connection
                                    </div>
                                <?php endif; ?>
                            
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ibox-content">
                                <form class="m-t" method="post" action="core/import_table.php" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <?php
                                        $list_database = mysqli_query($conn, "SHOW DATABASES");
                                        ?>
                                        <label>Choose Database</label>
                                        <select name="selected_database" class="form-control" requred>
                                            <option>Choose Database</option>
                                            <?php while ($row = mysqli_fetch_array($list_database)) :; ?>
                                                <option><?php echo $row[0]; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Upload SQL File</label>
                                        <input type="file" name="sql_file" class="form-control" placeholder="Upload SQL File" required="">
                                    </div>
                                    <?php if (checkInternetConnection()) :?>
                                        <button type="submit" class="btn btn-primary block full-width m-b"><i class="fa fa-upload"></i>Upload</button>
                                    <?php else : ?>
                                        <a href="" class="btn btn-warning block full-width m-b"><i class="fa fa-close"> Disconnected</a></i>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="ibox ">
                                <div class="ibox-title">
                                    <h5>Instalation Instructions</h5>
                                </div>

                                <div class="ibox-content">

                                    <div class="activity-stream">
                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-flash bg-primary"></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Make sure you have a internet Connection. <i class="fa fa-check bg-primary"></i>
                                            </div>
                                        </div>

                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-database bg-primary"></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Create a new database. <i class="fa fa-check bg-primary"></i>
                                            </div>
                                        </div>
                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-upload"></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Choose Lastest Database & Upload core.sql file there are on folder native_staterkit/database.
                                            </div>
                                        </div>
                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-cog"></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Setup Your Application. 
                                            </div>
                                        </div>
                                        <div class="stream">
                                            <div class="stream-badge">
                                                <i class="fa fa-send "></i>
                                            </div>
                                            <div class="stream-panel">
                                                <div class="stream-info">
                                                    
                                                </div>
                                                Finish. you can login now.
                                            </div>
                                        </div>
                                        


                                    </div>

                                </div>
                            </div>
                        </div>
                <?php endif; ?>
                <?php else : ?>
                    <div class="col-md-6">
                        <div class="ibox-content">
                            <?php if (checkInternetConnection()) :?>
                            <div style="position: relative; width: 100%; height: 0; padding-top: 56.2225%;
                            padding-bottom: 0;  margin-top: 1.6em; margin-bottom: 0.9em; overflow: hidden;
                            border-radius: 8px; will-change: transform;">
                                <iframe loading="lazy" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: none; padding: 0;margin: 0;"
                                    src="https:&#x2F;&#x2F;www.canva.com&#x2F;design&#x2F;DAF7WUfE_0Q&#x2F;view?embed" allowfullscreen="allowfullscreen" allow="fullscreen">
                                </iframe>
                            </div>
                            <?php else : ?>
                                <div class="alert alert-danger">
                                    <i class="fa fa-close"></i> No Internet Connection
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="ibox-title">
                            <h5>Create Database Name</h5>
                           
                        </div>
                        <div class="ibox-content">
                            <form class="m-t" method="post" action="core/create_connection.php" enctype="multipart/form-data">
                                <div class="form-group">
                                    <input type="text" name="database_name" class="form-control" placeholder="Database Name" required="">
                                </div>
                                <?php if (checkInternetConnection()) :?>
                                    <button type="submit" class="btn btn-primary block full-width m-b">Create</button>
                                <?php else : ?>
                                    <a href="" class="btn btn-warning block full-width m-b"><i class="fa fa-close"> Disconnected</a></i>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5>Instalation Instructions</h5>
                            </div>

                            <div class="ibox-content">

                                <div class="activity-stream">
                                    <div class="stream">
                                        <div class="stream-badge">
                                            <i class="fa fa-flash bg-primary"></i>
                                        </div>
                                        <div class="stream-panel">
                                            <div class="stream-info">
                                                
                                            </div>
                                            Make sure you have a internet Connection. <i class="fa fa-check bg-primary"></i></i>
                                        </div>
                                    </div>

                                    <div class="stream">
                                        <div class="stream-badge">
                                            <i class="fa fa-database"></i>
                                        </div>
                                        <div class="stream-panel">
                                            <div class="stream-info">
                                                
                                            </div>
                                            Create a new database.
                                        </div>
                                    </div>
                                    <div class="stream">
                                        <div class="stream-badge">
                                            <i class="fa fa-upload"></i>
                                        </div>
                                        <div class="stream-panel">
                                            <div class="stream-info">
                                                
                                            </div>
                                            Choose Lastest Database & Upload core.sql file there are on folder native_staterkit/core.
                                        </div>
                                    </div>
                                    <div class="stream">
                                        <div class="stream-badge">
                                            <i class="fa fa-cog"></i>
                                        </div>
                                        <div class="stream-panel">
                                            <div class="stream-info">
                                                
                                            </div>
                                            Setup Your Application. 
                                        </div>
                                    </div>
                                    <div class="stream">
                                        <div class="stream-badge">
                                            <i class="fa fa-send"></i>
                                        </div>
                                        <div class="stream-panel">
                                            <div class="stream-info">
                                                
                                            </div>
                                            Finish. you can login now.
                                        </div>
                                    </div>
                                    


                                </div>

                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                    
             <?php endif; ?>
            </div>
        
        <hr/>
        
    </div>

</body>
    <?php include 'assets/php_mode/js.php';?>
    
</html>
