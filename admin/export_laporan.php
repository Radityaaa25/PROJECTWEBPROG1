<?php
// admin/export_laporan.php
session_start();
require_once '../includes/functions.php';

// Cek login
is_logged_in();

// Set header agar browser mendownload file sebagai CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_transaksi_' . date('Y-m-d') . '.csv');

// Buat output stream
$output = fopen('php://output', 'w');

// Tulis Header Kolom (Judul)
fputcsv($output, array('ID Pesanan', 'Tanggal', 'No Meja', 'Pemesan', 'Status', 'Metode Bayar', 'Total Harga', 'Detail Menu'));

// Ambil data pesanan yang sudah selesai (atau semua pesanan)
$sql = "SELECT * FROM tbl_pesanan ORDER BY timestamp DESC";
$result = query($sql);

while ($row = fetch_assoc($result)) {
    // Ambil detail menu dalam satu string agar rapi
    $id_pesanan = $row['id_pesanan'];
    $sql_detail = "SELECT m.nama_menu, pd.jumlah 
                   FROM tbl_pesanan_detail pd 
                   JOIN tbl_menu m ON pd.id_menu = m.id_menu 
                   WHERE pd.id_pesanan = $id_pesanan";
    $result_detail = query($sql_detail);
    
    $detail_str = [];
    while($d = fetch_assoc($result_detail)){
        $detail_str[] = $d['nama_menu'] . " (" . $d['jumlah'] . ")";
    }
    $detail_text = implode(", ", $detail_str);

    // Tulis baris data ke CSV
    fputcsv($output, array(
        $row['id_pesanan'],
        $row['timestamp'],
        $row['no_meja'],
        $row['nama_pemesan'],
        $row['status_pesanan'],
        $row['metode_bayar'],
        $row['total_harga'],
        $detail_text
    ));
}

fclose($output);
exit();
?>