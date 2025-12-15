<?php
// dapur/index.php
require_once '../includes/functions.php';

// --- PROSES UPDATE STATUS (JIKA ADA REQUEST) ---
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['status'])) {
    $id_pesanan = (int)$_GET['id'];
    $status_baru = clean_input($_GET['status']);
    
    if (in_array($status_baru, ['Diterima', 'Dibuat', 'Selesai'])) {
        $sql_update = "UPDATE tbl_pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = $id_pesanan";
        query($sql_update);
    }
    // Redirect kembali ke halaman ini agar URL bersih
    header("Location: index.php");
    exit();
}

// --- AMBIL DATA PESANAN ---
$sql_pesanan = "
    SELECT * FROM tbl_pesanan 
    WHERE status_pesanan IN ('Diterima', 'Dibuat') 
    ORDER BY timestamp ASC
";
$list_pesanan = fetch_all(query($sql_pesanan));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="10"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Dapur (Auto-refresh)</title>
    <link rel="stylesheet" href="../css/style.css?v=3">
</head>
<body class="kitchen-body">
    <div class="kitchen-header">
        <h1>Kitchen Display System</h1>
        <p>Mode: PHP Native (Auto-refresh 10 detik)</p>
        <a href="../admin/dashboard.php" style="color: #aaf;">&larr; Kembali ke Admin</a>
    </div>

    <div class="order-grid">
        <?php if (empty($list_pesanan)): ?>
            <p style="width: 100%; text-align: center; color: #aaa;">Tidak ada pesanan aktif.</p>
        <?php else: ?>
            <?php foreach ($list_pesanan as $order): ?>
                <div class="order-card">
                    <h3>Meja <?php echo $order['no_meja']; ?> (<?php echo htmlspecialchars($order['nama_pemesan']); ?>)</h3>
                    <p style="font-size: 0.8em; color: #ccc;">Masuk: <?php echo $order['timestamp']; ?></p>
                    <p>Status: <strong><?php echo $order['status_pesanan']; ?></strong></p>
                    <hr>
                    
                    <ul>
                        <?php 
                        $id_p = $order['id_pesanan'];
                        $sql_detail = "SELECT pd.*, m.nama_menu FROM tbl_pesanan_detail pd JOIN tbl_menu m ON pd.id_menu = m.id_menu WHERE pd.id_pesanan = $id_p";
                        $details = fetch_all(query($sql_detail));
                        
                        foreach ($details as $d):
                        ?>
                        <li>
                            <b><?php echo htmlspecialchars($d['nama_menu']); ?> x<?php echo $d['jumlah']; ?></b><br>
                            <?php if(!empty($d['catatan_varian'])) echo "<small class='note'>Varian: " . htmlspecialchars($d['catatan_varian']) . "</small><br>"; ?>
                            <?php if(!empty($d['catatan_kustom'])) echo "<small class='note custom-note'>Note: " . htmlspecialchars($d['catatan_kustom']) . "</small>"; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="order-actions">
                        <?php if ($order['status_pesanan'] == 'Diterima'): ?>
                            <a href="index.php?action=update&id=<?php echo $order['id_pesanan']; ?>&status=Dibuat" class="btn btn-proses">Mulai Masak</a>
                        <?php elseif ($order['status_pesanan'] == 'Dibuat'): ?>
                            <a href="index.php?action=update&id=<?php echo $order['id_pesanan']; ?>&status=Selesai" class="btn btn-selesai" onclick="return confirm('Pesanan Selesai?')">Selesai</a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>