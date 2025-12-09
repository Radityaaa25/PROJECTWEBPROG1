<?php
// index.php
require_once 'includes/functions.php';

// Inisialisasi Keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ambil Nomor Meja
$no_meja = isset($_GET['meja']) ? (int)$_GET['meja'] : (isset($_SESSION['no_meja']) ? $_SESSION['no_meja'] : 1);
$_SESSION['no_meja'] = $no_meja;

// --- LOGIKA TAMBAH KE KERANJANG (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $id_menu = (int)$_POST['id_menu'];
    $jumlah = (int)$_POST['jumlah'];
    $catatan_kustom = clean_input($_POST['catatan_kustom']);
    $nama_menu = $_POST['nama_menu'];
    $harga_dasar = (int)$_POST['harga_dasar'];
    
    // Hitung total harga item + varian
    $harga_total_item = $harga_dasar;
    $varian_str = [];

    // Proses Varian yang dipilih
    if (isset($_POST['varian'])) {
        foreach ($_POST['varian'] as $grup_nama => $opsi_pilih) {
            if (is_array($opsi_pilih)) {
                // Checkbox (banyak pilihan)
                foreach ($opsi_pilih as $val) {
                    $parts = explode('|', $val); // Format value: "Nama Opsi|Harga"
                    $nama_opsi = $parts[0];
                    $harga_opsi = (int)$parts[1];
                    $harga_total_item += $harga_opsi;
                    $varian_str[] = "$grup_nama: $nama_opsi";
                }
            } else {
                // Radio (satu pilihan)
                $parts = explode('|', $opsi_pilih);
                $nama_opsi = $parts[0];
                $harga_opsi = (int)$parts[1];
                $harga_total_item += $harga_opsi;
                $varian_str[] = "$grup_nama: $nama_opsi";
            }
        }
    }

    // Simpan ke Session Cart
    $_SESSION['cart'][] = [
        'id_menu' => $id_menu,
        'nama_menu' => $nama_menu,
        'jumlah' => $jumlah,
        'harga_satuan' => $harga_total_item,
        'subtotal' => $harga_total_item * $jumlah,
        'varian' => implode(', ', $varian_str),
        'catatan' => $catatan_kustom
    ];

    header("Location: index.php?page=menu&meja=$no_meja&pesan=sukses");
    exit();
}

// --- LOGIKA HAPUS KERANJANG ---
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    array_splice($_SESSION['cart'], $index, 1);
    header("Location: index.php?page=cart&meja=$no_meja");
    exit();
}

// --- TENTUKAN HALAMAN YANG DITAMPILKAN ---
$page = isset($_GET['page']) ? $_GET['page'] : 'menu';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resto Kita - Meja <?php echo $no_meja; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Tambahan CSS sederhana agar layout rapi tanpa JS */
        .btn { display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 5px; cursor: pointer; border: none;}
        .btn-primary { background: #ff5722; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .nav-bar { background: #fff; padding: 15px; border-bottom: 1px solid #ddd; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;}
        .variant-group { margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .cart-count { background: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 0.8em; }
        input[type=number] { width: 50px; padding: 5px; }
        textarea { width: 100%; height: 60px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>

    <div class="nav-bar">
        <h3>Meja <?php echo $no_meja; ?></h3>
        <div>
            <a href="index.php?page=menu&meja=<?php echo $no_meja; ?>" class="btn">Menu</a>
            <a href="index.php?page=cart&meja=<?php echo $no_meja; ?>" class="btn">
                Keranjang <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
            </a>
        </div>
    </div>

    <?php if ($page == 'detail' && isset($_GET['id'])): ?>
        <?php 
            $id_menu = (int)$_GET['id'];
            $menu = fetch_assoc(query("SELECT * FROM tbl_menu WHERE id_menu = $id_menu"));
            
            // Ambil Varian
            $grup_varian = fetch_all(query("SELECT * FROM tbl_varian_grup WHERE id_menu = $id_menu"));
        ?>
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; background: white;">
            <img src="img/<?php echo $menu['foto'] ? $menu['foto'] : 'placeholder.jpg'; ?>" style="width:100%; max-height: 300px; object-fit: cover;">
            <h2><?php echo $menu['nama_menu']; ?></h2>
            <p><?php echo $menu['deskripsi']; ?></p>
            <h3 style="color: #ff5722;"><?php echo format_rupiah($menu['harga_dasar']); ?></h3>
            
            <form action="index.php" method="POST">
                <input type="hidden" name="id_menu" value="<?php echo $menu['id_menu']; ?>">
                <input type="hidden" name="nama_menu" value="<?php echo $menu['nama_menu']; ?>">
                <input type="hidden" name="harga_dasar" value="<?php echo $menu['harga_dasar']; ?>">

                <?php foreach ($grup_varian as $grup): ?>
                    <div class="variant-group">
                        <h4><?php echo $grup['nama_grup']; ?></h4>
                        <?php 
                            $opsi = fetch_all(query("SELECT * FROM tbl_varian_opsi WHERE id_grup_varian = " . $grup['id_grup_varian'])); 
                        ?>
                        <?php foreach ($opsi as $op): ?>
                            <label style="display:block; margin-bottom: 5px;">
                                <?php if ($grup['tipe_pilihan'] == 'radio'): ?>
                                    <input type="radio" name="varian[<?php echo $grup['nama_grup']; ?>]" value="<?php echo $op['nama_opsi'].'|'.$op['tambahan_harga']; ?>" required>
                                <?php else: ?>
                                    <input type="checkbox" name="varian[<?php echo $grup['nama_grup']; ?>][]" value="<?php echo $op['nama_opsi'].'|'.$op['tambahan_harga']; ?>">
                                <?php endif; ?>
                                <?php echo $op['nama_opsi']; ?> 
                                <?php if($op['tambahan_harga'] > 0) echo "(+" . format_rupiah($op['tambahan_harga']) . ")"; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <div style="margin-top: 15px;">
                    <label>Catatan Tambahan:</label><br>
                    <textarea name="catatan_kustom" placeholder="Contoh: Jangan pakai bawang"></textarea>
                </div>

                <div style="margin-top: 15px;">
                    <label>Jumlah:</label>
                    <input type="number" name="jumlah" value="1" min="1" required>
                </div>

                <br>
                <button type="submit" name="add_to_cart" class="btn btn-primary" style="width:100%;">Tambahkan ke Keranjang</button>
            </form>
        </div>

    <?php elseif ($page == 'cart'): ?>
        <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
            <h2>Keranjang Pesanan</h2>
            <?php if (empty($_SESSION['cart'])): ?>
                <p>Keranjang masih kosong.</p>
                <a href="index.php?meja=<?php echo $no_meja; ?>" class="btn btn-primary">Pesan Menu</a>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Detail</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach ($_SESSION['cart'] as $index => $item): 
                            $grand_total += $item['subtotal'];
                        ?>
                        <tr>
                            <td><?php echo $item['nama_menu']; ?></td>
                            <td>
                                <small>
                                    <?php echo $item['varian']; ?><br>
                                    <?php if($item['catatan']) echo "<em>Catatan: " . $item['catatan'] . "</em>"; ?>
                                </small>
                            </td>
                            <td><?php echo $item['jumlah']; ?></td>
                            <td><?php echo format_rupiah($item['subtotal']); ?></td>
                            <td>
                                <a href="index.php?action=hapus&index=<?php echo $index; ?>&meja=<?php echo $no_meja; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h3 style="text-align: right; margin-top: 20px;">Total: <?php echo format_rupiah($grand_total); ?></h3>

                <div style="background: #eee; padding: 20px; margin-top: 20px; border-radius: 8px;">
                    <h3>Konfirmasi Pemesanan</h3>
                    <form action="proses_pesanan.php" method="POST">
                        <label>Nama Pemesan:</label><br>
                        <input type="text" name="nama_pemesan" required style="width: 100%; padding: 10px; margin-bottom: 10px;"><br>
                        
                        <label>Metode Pembayaran:</label><br>
                        <label><input type="radio" name="metode_bayar" value="kasir" checked> Bayar di Kasir</label> <br>
                        <label><input type="radio" name="metode_bayar" value="online"> Bayar Online</label>
                        <br><br>
                        <button type="submit" class="btn btn-success" style="width: 100%;">PROSES PESANAN</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        
        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'sukses'): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; text-align: center; margin-bottom: 10px;">
                Menu berhasil ditambahkan ke keranjang!
            </div>
        <?php endif; ?>

        <div style="padding: 20px;">
            <div style="margin-bottom: 20px; overflow-x: auto; white-space: nowrap; padding-bottom: 10px;">
                <a href="index.php?meja=<?php echo $no_meja; ?>" class="btn" style="border: 1px solid #ccc;">Semua</a>
                <?php 
                    $kategoris = fetch_all(query("SELECT * FROM tbl_kategori"));
                    foreach($kategoris as $kat): 
                ?>
                <a href="index.php?kategori=<?php echo $kat['id_kategori']; ?>&meja=<?php echo $no_meja; ?>" class="btn" style="border: 1px solid #ccc;"><?php echo $kat['nama_kategori']; ?></a>
                <?php endforeach; ?>
            </div>

            <div class="menu-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <?php 
                    $where = "";
                    if (isset($_GET['kategori'])) {
                        $id_kat = (int)$_GET['kategori'];
                        $where = "WHERE id_kategori = $id_kat";
                    }
                    $menu_list = fetch_all(query("SELECT * FROM tbl_menu $where ORDER BY id_menu DESC"));
                ?>
                
                <?php foreach ($menu_list as $m): ?>
                <div style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; padding-bottom: 10px;">
                    <img src="img/<?php echo $m['foto'] ? $m['foto'] : 'placeholder.jpg'; ?>" style="width: 100%; height: 150px; object-fit: cover;">
                    <div style="padding: 10px;">
                        <h4 style="margin: 0 0 5px 0;"><?php echo $m['nama_menu']; ?></h4>
                        <p style="color: #666; font-size: 0.9em;"><?php echo format_rupiah($m['harga_dasar']); ?></p>
                        <a href="index.php?page=detail&id=<?php echo $m['id_menu']; ?>&meja=<?php echo $no_meja; ?>" class="btn btn-primary" style="display: block; text-align: center; margin-top: 10px;">Pesan</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>