# Native PHP Starter Kit with RBAC & CRUD Generator

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)
![License](https://img.shields.io/badge/license-MIT-green)

Native PHP Starter Kit adalah sebuah sistem dasar (boilerplate) yang dirancang untuk mempercepat pengembangan aplikasi web menggunakan PHP Native. Sistem ini sudah dilengkapi dengan fitur-fitur modern seperti **Role Based Access Control (RBAC)**, **CRUD Generator**, dan **System Monitoring Dashboard**.

---

## 🚀 Fitur Utama

### 1. **Role Based Access Control (RBAC)**
Kelola hak akses pengguna dengan sangat detail:
- **Role Management**: Membuat dan mengelola level pengguna (SuperAdmin, Admin, User, dll).
- **Permission Management**: Mengatur izin akses setiap modul secara spesifik.
- **Menu Access**: Sembunyikan atau tampilkan menu berdasarkan level akses pengguna.

### 2. **Auto CRUD Generator**
Buat fitur CRUD (Create, Read, Update, Delete) dalam hitungan detik tanpa menulis kode dari nol:
- **Field Configuration**: Mendukung berbagai tipe input (Text, Number, Date, File Upload, Image).
- **Auto Source Code Generation**: Sistem akan secara otomatis membuat file Controller dan View yang siap pakai.
- **Support Upload**: Dilengkapi fitur upload file/gambar dengan preview.

### 3. **Database & Table Generator**
- **Table Creator**: Membuat tabel database langsung dari dashboard GUI.
- **Structure Manager**: Ubah struktur tabel (tambah/hapus kolom) tanpa melalui phpMyAdmin.
- **History Migration**: Melacak perubahan yang dilakukan pada tabel.

### 4. **Modern Dashboard & System Monitoring**
- **System Resources**: Monitoring penggunaan RAM, CPU, dan Disk secara real-time.
- **Neofetch Style Info**: Menampilkan informasi server dengan gaya terminal linux yang keren.
- **Network Tools**: Cek status koneksi internet dan informasi SSID.

### 5. **User Management & Profile**
- Pengaturan profil pengguna dengan upload foto.
- Keamanan session yang terintegrasi.
- Notifikasi interaktif menggunakan Toastr dan SweetAlert2.

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
| --- | --- |
| **Backend** | PHP Native |
| **Database** | MySQL / MariaDB |
| **Frontend Framework** | Bootstrap 4 (Inspinia Theme) |
| **Interactive UI** | jQuery, DataTables, SweetAlert2, Toastr |
| **Charts** | Chart.js, C3.js |

---

## 📦 Instalasi

Ikuti langkah-langkah berikut untuk menjalankan project di lokal:

1. **Clone Repository**
   ```bash
   git clone https://github.com/username/native_staterkit.git
   ```

2. **Pindahkan ke Server Lokal**
   Pindahkan folder ke directory `www` (Laragon) atau `htdocs` (XAMPP).

3. **Konfigurasi Database Terotomatisasi**
   - Buka browser dan akses `http://localhost/native_staterkit/index.php`.
   - Sistem akan mendeteksi jika database belum ada.
   - Masukkan nama database yang diinginkan pada form yang disediakan.
   - Sistem akan otomatis membuat database dan table-table yang diperlukan.

4. **Login Default**
   - **Username**: `superadmin`
   - **Password**: `admin123` *(Suaikan di database jika perlu)*

---

## 📂 Struktur Folder

```text
native_staterkit/
├── assets/             # CSS, JS, Images, dan Plugins
├── connection/         # File konfigurasi koneksi database
├── core/               # Inti sistem (Logic, Generators, API)
├── helper/             # Fungsi bantuan (Utility)
├── template/           # Header, Footer, Sidebar, dll
├── uploads/            # Direktori penyimpanan file upload
└── *.php               # File modul-modul utama (Dashboard, CRUD, dll)
```

---

## 📸 Tampilan UI

*Sistem ini menggunakan tema premium **Inspinia** yang sangat responsif dan memiliki fitur dark mode serta dashboard neon yang elegan.*

---

## 📄 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

---

**Dibuat dengan ❤️ untuk memudahkan Developer PHP.**
