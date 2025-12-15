# Proyek UAS: Sistem Pemesanan Restoran (QR Code)

Repository ini berisi kode sumber untuk proyek Ujian Akhir Semester (UAS) mata kuliah Web Programming 1. Proyek ini adalah sistem pemesanan menu digital berbasis web yang diakses pelanggan melalui QR code di meja.

## 1. Deskripsi & Alur Proyek

Tujuan utama proyek ini adalah membuat aplikasi web yang memungkinkan pelanggan memesan menu langsung dari meja mereka.

**Alur Pengguna (Pelanggan):**
1.  Pelanggan duduk di meja (misal: Meja 5).
2.  Pelanggan memindai (scan) QR code yang ada di meja.
3.  Browser HP membuka web, contoh: `https://resto-kita.com/index.php?meja=5`.
4.  Aplikasi web secara otomatis mendeteksi **Nomor Meja 5**.
5.  Pelanggan melihat daftar menu (per kategori), memilih menu, dan memilih varian (jika ada).
6.  Pesanan masuk ke keranjang (sidebar).
7.  Pelanggan mengisi nama dan memilih metode pembayaran (Bayar di Kasir / Online).
8.  Pelanggan klik "Pesan".

**Alur Internal (Restoran):**
1.  Pesanan baru (dari Meja 5) akan langsung muncul di **Halaman Dapur** (`dapur.php`).
2.  Halaman Dapur akan auto-refresh untuk menampilkan pesanan baru.
3.  Dapur/Kasir mengubah status pesanan (Diterima -> Dibuat -> Selesai).
4.  Admin juga memiliki halaman khusus (`admin/`) untuk menambah/mengubah/menghapus menu.

## 2. Ruang Lingkup & Fitur

### Sisi Pelanggan (Frontend)
- [ ] Tampilan daftar menu berdasarkan kategori (Makanan, Minuman, Kopi, Dll).
- [ ] Pop-up untuk memilih varian menu (Contoh: Panas/Dingin, Level Pedas, Topping).
- [ ] Fitur untuk menambah catatan kustom per item (Contoh: "Jangan pakai bawang").
- [ ] Sidebar Keranjang (Cart) yang interaktif (Tambah/Kurang/Hapus item).
- [ ] Proses Checkout (Otomatis terisi nomor meja, input nama pemesan).
- [ ] Opsi Pembayaran:
    - [ ] Bayar di Kasir (Default).
    - [ ] Bayar Online (Integrasi Sandbox Payment Gateway, misal: Midtrans or Qris).

### Sisi Admin (Backend)
- [ ] Halaman login yang aman untuk Admin.
- [ ] CRUD (Create, Read, Update, Delete) untuk **Kategori Menu**.
- [ ] CRUD (Create, Read, Update, Delete) untuk **Item Menu** (termasuk foto, harga, deskripsi).
- [ ] CRUD (Create, Read, Update, Delete) untuk **Varian Menu** (menghubungkan varian ke item menu).
- [ ] Fitur Export Laporan Transaksi ke CSV/Excel.

### Sisi Dapur (Backend)
- [ ] Halaman Tampilan Pesanan (`dapur.php`).
- [ ] Tampilan pesanan baru secara real-time (menggunakan AJAX/Fetch untuk auto-refresh).
- [ ] Fitur untuk mengubah **Status Pesanan** (Diterima, Dibuat, Selesai).

## 3. Teknologi yang Digunakan

Sesuai syarat mata kuliah, proyek ini DIBANGUN TANPA FRAMEWORK.
* **Frontend:** & **Backend:** FULL PHP (Vanilla/Native PHP) dan CSS.
* **Database:** MySQL.

## 4. Rancangan Database (Awal)

Ini adalah rancangan awal tabel yang kita butuhkan.

| Nama Tabel | Kolom | Keterangan |
| :--- | :--- | :--- |
| **tbl_admin** | `id_admin` (PK), `username`, `password` | Untuk login admin & dapur. |
| **tbl_kategori** | `id_kategori` (PK), `nama_kategori` | Contoh: Makanan, Minuman, Kopi. |
| **tbl_menu** | `id_menu` (PK), `id_kategori` (FK), `nama_menu`, `deskripsi`, `harga_dasar`, `foto` | Data item menu utama. |
| **tbl_varian_grup** | `id_grup_varian` (PK), `id_menu` (FK), `nama_grup`, `tipe_pilihan` | Contoh: (Menu: Kopi Susu, Nama: Suhu, Tipe: 'radio'). |
| **tbl_varian_opsi** | `id_opsi_varian` (PK), `id_grup_varian` (FK), `nama_opsi`, `tambahan_harga` | Contoh: (Grup: Suhu, Opsi: 'Dingin', Harga: +1000). |
| **tbl_pesanan** | `id_pesanan` (PK), `no_meja`, `nama_pemesan`, `total_harga`, `status_pesanan`, `status_bayar`, `metode_bayar`, `timestamp` | "Header" dari setiap pesanan. |
| **tbl_pesanan_detail** | `id_detail` (PK), `id_pesanan` (FK), `id_menu` (FK), `jumlah`, `harga_saat_pesan`, `subtotal`, `catatan_varian`, **`catatan_kustom` (BARU)** | Item-item di dalam satu pesanan. |

*(Catatan: `catatan_varian` = "Suhu: Dingin, Level: 3". `catatan_kustom` = "Jangan pakai bawang".)*

## 5. Struktur Folder (Repo)

Untuk menjaga kerapian, kita akan gunakan struktur folder berikut:
/
├── admin/                  # (Backend) Halaman khusus Admin (CRUD Menu)
│   ├── index.php           # (Login Admin)
│   ├── dashboard.php
│   ├── crud_menu.php
│   ├── crud_kategori.php
│   ├── crud_varian.php
│   ├── export_laporan.php
│   └── logout.php
│
├── dapur/                  # (Backend) Halaman khusus Dapur (Lihat Pesanan)
│   └── index.php           # (Tampilan Pesanan)
│
├── includes/               # (Backend) File-file pendukung
│   ├── db_connect.php      # (Koneksi database)
│   └── functions.php       # (Fungsi-fungsi PHP re-usable)
│
├── css/                    # (Frontend) Semua file CSS
│   └── style.css
│
├── img/                    # (Lain-lain) Tempat upload foto-foto menu
│
├── index.php               # (Bersama) Halaman utama / Menu (dilihat pelanggan)
├── proses_pesanan.php      # (Backend) File untuk menerima data pesanan dari JS
└── README.md               # (Dokumentasi ini)

## 6. Pembagian Tugas & Alur Kerja

Kita dibagi menjadi 2 tim (Frontend & Backend). Ini adalah tanggung jawab dan titik temu (kontrak) kita:

### 🔵 Tim Frontend (3 Orang)

Tugas utama: Fokus pada **Tampilan (View)**.
* Membuat `style.css` agar semua halaman (Menu, Admin, Dapur) terlihat rapi dan responsif.
    * Logika Keranjang (tambah/kurang/hapus).
    * Menyediakan input untuk `catatan_kustom`.
    * Mengirim data keranjang (Checkout) ke `proses_pesanan.php`.
* Mendesain/Menata file-file PHP yang dibuat Tim Backend (`index.php`, `admin/*.php`, `dapur/*.php`).
* **(BARU)** Menambahkan tombol/link di `admin/dashboard.php` untuk memicu download laporan (link ke `export_laporan.php`).

### 🔴 Tim Backend (3 Orang)

Tugas utama: Fokus pada **Logika Server (PHP)** dan **Manajemen Data (MySQL)**.
* Merancang, membuat, dan mengelola **Database MySQL** (termasuk `catatan_kustom`).
* Membuat `includes/db_connect.php` dan `functions.php`.
* Membuat **seluruh** fungsionalitas di folder `/admin/` (Login, CRUD Kategori, Menu, Varian).
* **(BARU)** Membuat file `admin/export_laporan.php` untuk meng-query data transaksi dan meng-generate file .csv.
* Membuat **seluruh** fungsionalitas di folder `/dapur/` Logika `index.php` untuk menampilkan data.
* Menyiapkan `index.php` dengan logika PHP untuk menampilkan data menu dari DB (agar Tim Frontend bisa menatanya).
* Membuat `proses_pesanan.php` yang siap menerima data JSON dari `main.js` (Frontend) dan menyimpannya ke `tbl_pesanan` dan `tbl_pesanan_detail`.

## 7. Panduan Alur Kerja Git (Git Workflow)

Untuk menjaga agar *repository* utama (`main`) tetap bersih dan menghindari "konflik" kode, semua pekerjaan (fitur baru, perbaikan *bug*) **HARUS** dibuat di **branch** terpisah.

Berikut adalah alur kerja yang wajib diikuti oleh semua anggota tim.

### Langkah 0: Persiapan Awal (Clone)

**Hanya dilakukan SEKALI saat pertama kali bergabung ke proyek.**

1.  Buka halaman GitHub repository proyek ini.
2.  Klik tombol hijau **Code** dan salin URL-nya.
3.  Buka Terminal / Command Prompt di folder tempat Anda ingin menyimpan proyek.
4.  Jalankan perintah:
    ```bash
    git clone URL_REPOSITORY_ANDA
    cd NAMA_FOLDER_PROYEK
    ```
### Langkah 1: Selalu Mulai dari `main` yang Terbaru

Sebelum mulai *coding* fitur baru, pastikan kode di komputer Anda adalah yang paling baru dari GitHub.

```bash
# 1. Pindah dulu ke branch 'main' (jika Anda sedang di branch lain)
git checkout main

# 2. Cek apakah ada update di server (GitHub)
# Ini cara aman untuk 'mengintip' tanpa langsung menggabung
git fetch origin

# 3. Tarik (pull) semua perubahan terbaru dari 'main' ke komputer Anda
git pull origin main

**JANGAN CODING DI BRANCH MAIN LANGSUNG, BUAT BRANCH BARU!! **

# 1. Buat branch baru. (Contoh: Tim Backend membuat fitur login)
git branch backend-login

# 2. Pindah ke branch baru tersebut
git checkout backend-login

** CARA PUSH KE GITHUB!! **

# 1. Tambahkan semua file yang Anda ubah
git add .

# 2. Simpan dengan pesan yang jelas
git commit -m "Selesai menambahkan logika untuk admin login"

# 'origin' adalah nama server (GitHub)
# 'backend-login' adalah nama branch Anda
git push origin backend-login

Semangat!