# Proyek UAS: Sistem Pemesanan Restoran (QR Code)

Repository ini berisi kode sumber untuk proyek Ujian Akhir Semester (UAS) mata kuliah Pemrograman Web. Proyek ini adalah sistem pemesanan menu digital berbasis web yang diakses pelanggan melalui QR code di meja.

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
- [ ] Sidebar Keranjang (Cart) yang interaktif (Tambah/Kurang/Hapus item).
- [ ] Proses Checkout (Otomatis terisi nomor meja, input nama pemesan).
- [ ] Opsi Pembayaran:
    - [ ] Bayar di Kasir (Default).
    - [ ] Bayar Online (Integrasi Sandbox Payment Gateway, misal: Midtrans).

### Sisi Admin (Backend)
- [ ] Halaman login yang aman untuk Admin.
- [ ] CRUD (Create, Read, Update, Delete) untuk **Kategori Menu**.
- [ ] CRUD (Create, Read, Update, Delete) untuk **Item Menu** (termasuk foto, harga, deskripsi).
- [ ] CRUD (Create, Read, Update, Delete) untuk **Varian Menu** (menghubungkan varian ke item menu).

### Sisi Dapur (Backend)
- [ ] Halaman Tampilan Pesanan (`dapur.php`).
- [ ] Tampilan pesanan baru secara real-time (menggunakan AJAX/Fetch untuk auto-refresh).
- [ ] Fitur untuk mengubah **Status Pesanan** (Diterima, Dibuat, Selesai, Diantar).

## 3. Teknologi yang Digunakan

Sesuai syarat mata kuliah, proyek ini DIBANGUN TANPA FRAMEWORK.
* **Frontend:** HTML, CSS, JavaScript (Vanilla/Native JS).
* **Backend:** PHP (Vanilla/Native PHP).
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
| **tbl_pesanan_detail** | `id_detail` (PK), `id_pesanan` (FK), `id_menu` (FK), `jumlah`, `harga_saat_pesan`, `subtotal`, `catatan_varian` | Item-item di dalam satu pesanan. |

*(Catadtan: `catatan_varian` di `tbl_pesanan_detail` akan diisi oleh JS, contoh: "Suhu: Dingin, Level: 3, Topping: Keju")*

## 5. Struktur Folder (Repo)

Untuk menjaga kerapian, kita akan gunakan struktur folder berikut:
/
├── admin/                  # (Backend) Halaman khusus Admin (CRUD Menu)
│   ├── index.php           # (Login Admin)
│   ├── dashboard.php
│   ├── crud_menu.php
│   ├── crud_kategori.php
│   └── logout.php
│
├── dapur/                  # (Backend) Halaman khusus Dapur (Lihat Pesanan)
│   ├── index.php           # (Tampilan Pesanan)
│   └── api_get_pesanan.php # (File JSON untuk di-fetch oleh JS)
│
├── includes/               # (Backend) File-file pendukung
│   ├── db_connect.php      # (Koneksi database)
│   └── functions.php       # (Fungsi-fungsi PHP re-usable)
│
├── css/                    # (Frontend) Semua file CSS
│   └── style.css
│
├── js/                     # (Frontend) Semua file JavaScript
│   ├── main.js             # (Logika keranjang, pop-up, checkout)
│   └── dapur.js            # (Logika AJAX auto-refresh halaman dapur)
│
├── img/                    # (Lain-lain) Tempat upload foto-foto menu
│
├── index.php               # (Bersama) Halaman utama / Menu (dilihat pelanggan)
├── proses_pesanan.php      # (Backend) File untuk menerima data pesanan dari JS
└── README.md               # (Dokumentasi ini)

## 6. Pembagian Tugas & Alur Kerja

Kita dibagi menjadi 2 tim (Frontend & Backend). Ini adalah tanggung jawab dan titik temu (kontrak) kita:

### 🔵 Tim Frontend (3 Orang)

Tugas utama: Fokus pada **Tampilan (View)** dan **Interaksi (Client-side JS)**.
* Membuat `style.css` agar semua halaman (Menu, Admin, Dapur) terlihat rapi dan responsif.
* Membuat `main.js` (di `index.php`):
    * Logika Pop-up Varian.
    * Logika Keranjang (tambah/kurang/hapus).
    * Mengirim data keranjang (Checkout) ke `proses_pesanan.php`.
* Membuat `dapur.js` (di `dapur/index.php`):
    * Logika `fetch` / AJAX untuk memanggil `api_get_pesanan.php` setiap 10 detik.
    * Merender data pesanan baru ke HTML tanpa refresh halaman.
* Mendesain/Menata file-file PHP yang dibuat Tim Backend (`index.php`, `admin/*.php`, `dapur/*.php`).

### 🔴 Tim Backend (3 Orang)

Tugas utama: Fokus pada **Logika Server (PHP)** dan **Manajemen Data (MySQL)**.
* Merancang, membuat, dan mengelola **Database MySQL**.
* Membuat `includes/db_connect.php` dan `functions.php`.
* Membuat **seluruh** fungsionalitas di folder `/admin/` (Login, CRUD Kategori, Menu, Varian).
* Membuat **seluruh** fungsionalitas di folder `/dapur/` (Logika `index.php` untuk menampilkan data dan `api_get_pesanan.php` untuk mengirim data JSON).
* Menyiapkan `index.php` dengan logika PHP untuk menampilkan data menu dari DB (agar Tim Frontend bisa menatanya).
* Membuat `proses_pesanan.php` yang siap menerima data JSON dari `main.js` (Frontend) dan menyimpannya ke `tbl_pesanan`.

### 🤝 Titik Temu / "Kontrak" Penting

1.  **Proses Checkout (`main.js` -> `proses_pesanan.php`)**
    * **Frontend (JS)** akan mengirim data pesanan via `fetch()` (metode POST) dalam format **JSON**.
    * **Backend (PHP)** harus siap menerima *raw data JSON* (bukan `$_POST`) menggunakan `json_decode(file_get_contents('php://input'))`.
    * Contoh JSON yang dikirim:
        ```json
        {
          "no_meja": 5,
          "nama_pemesan": "Raditya",
          "metode_bayar": "kasir",
          "items": [
            { "id_menu": 1, "jumlah": 2, "catatan": "Panas, Level 1" },
            { "id_menu": 3, "jumlah": 1, "catatan": "Topping: Telur" }
          ]
        }
        ```

2.  **Refresh Dapur (`dapur.js` -> `dapur/api_get_pesanan.php`)**
    * **Backend (PHP)** membuat `api_get_pesanan.php` yang jika diakses akan mengembalikan data pesanan (misal: yang statusnya 'Diterima') dalam format **JSON**.
    * **Frontend (JS)** akan memanggil file API tersebut setiap 10 detik untuk mendapat data JSON terbaru dan menampilkannya di Halaman Dapur.

Mari kita mulai dari Fase 0 (Setup) dan Fase 1 (Admin) terlebih dahulu.

Semangat!
