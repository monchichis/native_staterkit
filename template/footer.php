<?php 
    include 'connection/connection.php';
    
?>
<?php
    $list_aplikasi = mysqli_query($conn, "SELECT * FROM tbl_aplikasi ORDER BY id DESC LIMIT 1");
    $num_rows = mysqli_num_rows($list_aplikasi);
?>
<?php foreach($list_aplikasi as $hasil):?>
<div class="footer">
    <div>
        <strong>Copyright : </strong> <span class="badge badge-info"><?= $hasil['nama_developer']?></span>
    </div>
</div>
<?php endforeach;?>

<!-- SweetAlert2 and Global JS for logout confirmation -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/global.js"></script>

