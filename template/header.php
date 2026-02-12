<?php 
    include 'connection/connection.php';
  
?>

<div class="row border-bottom">
        <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
           
        </div>
        
            <ul class="nav navbar-top-links navbar-right">
            <?php 
                    $list_aplikasi = mysqli_query($conn, "SELECT * FROM tbl_aplikasi ORDER BY id DESC LIMIT 1");
                    $num_rows = mysqli_num_rows($list_aplikasi);
            ?>
            
            <?php foreach($list_aplikasi as $identitas):?>
                <li style="padding: 20px">
                    <span class="m-r-sm text-muted welcome-message">Welcome to <?= $identitas['nama_aplikasi']?></span>
                </li>
            <?php endforeach; ?>    


                <li>
                     
                    <a href="javascript:void(0)" onclick="confirmLogout()">
                        <i class="fa fa-sign-out"></i> Log out
                    </a>
                </li>
                
            </ul>

        </nav>
</div>