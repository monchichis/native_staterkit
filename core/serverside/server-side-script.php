<?php

include '../../connection/connection.php';

// Atur draw, start, dan length
$draw = $_POST['draw'];
$start = $_POST['start'];
$length = $_POST['length'];

// Sorting
$columns = array('id_user', 'nama'); // Sesuaikan dengan nama kolom di tabel
$column_index = $_POST['order'][0]['column'];
$column_name = $columns[$column_index];
$column_sort_order = $_POST['order'][0]['dir'];

// Pencarian
$search = $_POST['search']['value'];

// Query utama
$query = "SELECT * FROM mst_user WHERE nama LIKE '%$search%' ORDER BY $column_name $column_sort_order LIMIT $start, $length";
$result = mysqli_query($conn, $query);

$data = array();
$no = $start + 1; // Nomor urut dimulai dari start

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = array(
        "DT_RowId" => $row['id_user'], // ID unik untuk setiap baris (penting untuk DataTables)
        "No" => $no++,
        "Nama" => $row['nama'],
        "Email" => $row['email'],
        "Level" => $row['level'],
        "Image" => "<img src='core/".$row['image']."' width='80' height='80'>"
        // Sesuaikan dengan kolom lainnya
    );
}

// Query untuk records total dan records filtered
$total_records = mysqli_query($conn, "SELECT COUNT(*) AS total FROM mst_user");
$total_records = mysqli_fetch_assoc($total_records)['total'];

$output = array(
    "draw" => intval($draw),
    "recordsTotal" => $total_records,
    "recordsFiltered" => mysqli_num_rows($result),
    "data" => $data
);

echo json_encode($output);
?>
