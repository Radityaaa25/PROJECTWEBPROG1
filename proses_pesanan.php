<?php
// proses_pesanan.php
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Cek apakah keranjang ada isinya
if (empty($_SESSION['cart'])) {
    die("Keranjang kosong.");
}

$no_meja = (int)$_SESSION['no_meja'];
$nama_pemesan = clean_input($_POST['nama_pemesan']);
$metode_bayar = clean_input($_POST['metode_bayar']);
$items = $_SESSION['cart'];

// Hitung total harga final
$total_harga = 0;
foreach ($items as $item) {
    $total_harga += $item['subtotal'];
}

$koneksi = $GLOBALS['koneksi'];
mysqli_begin_transaction($koneksi);

try {
    // 1. Simpan Header Pesanan
    $timestamp = date('Y-m-d H:i:s');
    $status_bayar = 'Belum';
    
    $sql_header = "INSERT INTO tbl_pesanan (no_meja, nama_pemesan, total_harga, status_pesanan, status_bayar, metode_bayar, timestamp) 
                   VALUES ($no_meja, '$nama_pemesan', $total_harga, 'Diterima', '$status_bayar', '$metode_bayar', '$timestamp')";
    
    query($sql_header);
    $id_pesanan = mysqli_insert_id($koneksi);

    // 2. Simpan Detail Pesanan
    foreach ($items as $item) {
        $id_menu = (int)$item['id_menu'];
        $jumlah = (int)$item['jumlah'];
        $harga_saat_pesan = (int)$item['harga_satuan']; // Harga satuan + varian
        $subtotal = (int)$item['subtotal'];
        $catatan_varian = clean_input($item['varian']);
        $catatan_kustom = clean_input($item['catatan']);

        $sql_detail = "INSERT INTO tbl_pesanan_detail (id_pesanan, id_menu, jumlah, harga_saat_pesan, subtotal, catatan_varian, catatan_kustom)
                       VALUES ($id_pesanan, $id_menu, $jumlah, $harga_saat_pesan, $subtotal, '$catatan_varian', '$catatan_kustom')";
        
        query($sql_detail);
    }

    mysqli_commit($koneksi);
    
    // Kosongkan keranjang
    unset($_SESSION['cart']);

    // Tampilkan pesan sukses sederhana
    echo "<!DOCTYPE html><html><head><title>Sukses</title><meta http-equiv='refresh' content='3;url=index.php?meja=$no_meja'></head>
    <body style='text-align:center; padding-top: 50px; font-family: sans-serif;'>
        <h1 style='color: green;'>Pesanan Berhasil!</h1>
        <p>Mohon tunggu, pesanan Anda sedang diproses dapur.</p>
        <p>Total: " . format_rupiah($total_harga) . "</p>
        <p>Anda akan diarahkan kembali ke menu dalam 3 detik...</p>
        <a href='index.php?meja=$no_meja'>Kembali Sekarang</a>
    </body></html>";

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo "Gagal memproses pesanan: " . $e->getMessage();
}
?>