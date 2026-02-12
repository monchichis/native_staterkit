<?php
session_start();
include('connection/connection.php');
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    $notificationType = $_SESSION['notification_type'];
    unset($_SESSION['notification']);
    unset($_SESSION['notification_type']);
    echo "<script>setTimeout(function() { toastr.options = { closeButton: true, progressBar: true, timeOut: 4000 }; toastr.$notificationType('$notification'); }, 1000);</script>";
}
$id_jenis_options = $conn->query("SELECT id, `nama_jenis` as label FROM `jenis_produk`");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk | Native Starterkit</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="assets/css/animate.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs4/dt-1.13.8/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</head>
<body>
    <div id="wrapper">
        <?php include 'template/sidebar.php'; ?>
        <div id="page-wrapper" class="gray-bg">
            <?php include 'template/header.php'; ?>
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>Produk</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active"><strong>Produk</strong></li>
                    </ol>
                </div>
            </div>
            <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5>List of Produk</h5>
                                <div class="ibox-tools">
                                    <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#addModal">
                                        <i class="fa fa-plus"></i> Add New
                                    </button>
                                </div>
                            </div>
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th>Nama produk</th>
                                                <th>Id jenis</th>
                                                <th>Stok</th>
                                                <th>Harga</th>
                                                <th width="15%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            $data = $conn->query("SELECT t1.*, `jenis_produk`.`nama_jenis` AS `id_jenis_display` FROM `produk` t1  LEFT JOIN `jenis_produk` ON t1.`id_jenis` = `jenis_produk`.`id`  ORDER BY t1.`id` DESC");
                                            while ($row = $data->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= $row['nama_produk'] ?></td>
                                                <td><?= $row['id_jenis_display'] ?></td>
                                                <td><?= $row['stok'] ?></td>
                                                <td><?= $row['harga'] ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-xs" onclick="editData('<?= $row['id'] ?>', '<?= $row['id'] ?>', '<?= $row['nama_produk'] ?>', '<?= $row['id_jenis'] ?>', '<?= $row['stok'] ?>', '<?= $row['harga'] ?>')"><i class="fa fa-edit"></i> Edit</button>
                                                    <button class="btn btn-danger btn-xs" onclick="confirmDelete('<?= $row['id'] ?>')"><i class="fa fa-trash"></i> Delete</button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
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

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Produk</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="core/produk/create.php" method="POST">
                        <div class="form-group">
                            <label>Nama produk</label>
                            <input type="text" name="nama_produk" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Id jenis</label>
                            <select name="id_jenis" class="form-control" required>
                                <option value="">-- Select --</option>
                                <?php mysqli_data_seek($id_jenis_options, 0); while($opt = $id_jenis_options->fetch_assoc()): ?>
                                    <option value="<?= $opt['id'] ?>"><?= $opt['label'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Harga</label>
                            <input type="text" name="harga" class="form-control rupiah-input" placeholder="Rp 0" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Produk</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="core/produk/update.php" method="POST">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Nama produk</label>
                            <input type="text" name="nama_produk" id="edit_nama_produk" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Id jenis</label>
                            <select name="id_jenis" id="edit_id_jenis" class="form-control" required>
                                <option value="">-- Select --</option>
                                <?php mysqli_data_seek($id_jenis_options, 0); while($opt = $id_jenis_options->fetch_assoc()): ?>
                                    <option value="<?= $opt['id'] ?>"><?= $opt['label'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" id="edit_stok" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Harga</label>
                            <input type="text" name="harga" id="edit_harga" class="form-control rupiah-input" placeholder="Rp 0" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
            $('#dataTables').DataTable();
        });

        function editData(id, id, nama_produk, id_jenis, stok, harga) {
            $('#edit_id').val(id);
            $('#edit_nama_produk').val(nama_produk);
            $('#edit_id_jenis').val(id_jenis);
            $('#edit_stok').val(stok);
            $('#edit_harga').val(harga);
            $('#editModal').modal('show');
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'core/produk/delete.php?id=' + id;
                }
            })
        }

        // Rupiah Format Helper
        function formatRupiah(angka) {
            var number_string = angka.replace(/[^,\d]/g, '').toString();
            var split = number_string.split(',');
            var sisa = split[0].length % 3;
            var rupiah = split[0].substr(0, sisa);
            var ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                var separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah ? 'Rp ' + rupiah : '';
        }

        // Auto-format Rupiah inputs on keyup
        $(document).on('keyup', '.rupiah-input', function(e) {
            $(this).val(formatRupiah($(this).val()));
        });

        // Format existing values on page load (for edit modal)
        $(document).ready(function() {
            $('.rupiah-input').each(function() {
                if ($(this).val()) {
                    $(this).val(formatRupiah($(this).val()));
                }
            });
        });
    </script>
</body>
</html>