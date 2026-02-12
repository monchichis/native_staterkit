<?php
session_start();
include('../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST["old_password"];
    $new_password = $_POST["new_password"];
    $confirm_new_password = $_POST["confirm_new_password"];

    // Validasi jika password baru sama dengan password lama
    if ($old_password == $new_password) {
        $_SESSION['notification'] = 'Ubah Password Gagal !! <br> Password baru tidak boleh sama dengan password lama</div>';
        $_SESSION['notification_type'] = "error";
        header("Location: ../profile.php");
        exit();
    }

    // Validasi jika konfirmasi password baru tidak sesuai
    if ($new_password != $confirm_new_password) {
        $_SESSION['notification'] = 'Ubah Password Gagal !! <br> Konfirmasi password baru tidak sesuai</div>';
        $_SESSION['notification_type'] = "error";
        header("Location: ../profile.php");
        exit();
    }

    // Query untuk mendapatkan data pengguna berdasarkan ID user
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM mst_user WHERE id_user = $user_id";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $user = mysqli_fetch_assoc($result);

        // Validasi password lama
        if (password_verify($old_password, $user['password'])) {
            // Hash password baru
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Perbarui data password
            $update_query = "UPDATE mst_user SET password = '$password_hash' WHERE id_user = $user_id";
            if (mysqli_query($conn, $update_query)) {
                $_SESSION['notification'] = "Password updated successfully.";
                $_SESSION['notification_type'] = "success";
                header("Location: ../dashboard.php");
                exit();
            } else {
                $_SESSION['notification'] = "Error updating password: " . mysqli_error($conn);
                $_SESSION['notification_type'] = "error";
                header("Location: ../profile.php");
                exit();
            }
        } else {
            // Password lama tidak sesuai
            $_SESSION['notification'] = 'Ubah Password Gagal !! <br> Password lama tidak sesuai</div>';
            $_SESSION['notification_type'] = "error";
            header("Location: ../profile.php");
            exit();
        }
    } else {
        // Terjadi kesalahan pada query
        $_SESSION['notification'] = "Error: " . mysqli_error($conn);
        $_SESSION['notification_type'] = "error";
        header("Location: ../profile.php");
        exit();
    }
}
?>
