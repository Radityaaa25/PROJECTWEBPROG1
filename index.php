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
    <link rel="stylesheet" href="css/style.css?v=4">
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <div class="nav-bar">
        <h3>Meja <?php echo $no_meja; ?></h3>
        <div>
            <a href="index.php?page=menu&meja=<?php echo $no_meja; ?>" class="btn btn-nav">Menu</a>
            <a href="index.php?page=cart&meja=<?php echo $no_meja; ?>" class="btn btn-nav">
                <i class='bx  bx-cart'></i><span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
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
        <div class="detail-container">
            <img src="img/<?php echo $menu['foto'] ? htmlspecialchars($menu['foto']) : 'placeholder.jpg'; ?>" alt="Foto <?php echo $menu['nama_menu']; ?>" style="width:100%; max-height: 300px; object-fit: cover;">
            <h2><?php echo htmlspecialchars($menu['nama_menu']); ?></h2>
            <p><?php echo htmlspecialchars($menu['deskripsi']); ?></p>
            <h3><?php echo format_rupiah($menu['harga_dasar']); ?></h3>
            
            <form action="index.php" method="POST">
                <input type="hidden" name="id_menu" value="<?php echo $menu['id_menu']; ?>">
                <input type="hidden" name="nama_menu" value="<?php echo htmlspecialchars($menu['nama_menu']); ?>">
                <input type="hidden" name="harga_dasar" value="<?php echo $menu['harga_dasar']; ?>">

                <?php foreach ($grup_varian as $grup): ?>
                    <div class="variant-group">
                        <h4><?php echo htmlspecialchars($grup['nama_grup']); ?></h4>
                        <?php 
                            $opsi = fetch_all(query("SELECT * FROM tbl_varian_opsi WHERE id_grup_varian = " . $grup['id_grup_varian'])); 
                        ?>
                        <?php foreach ($opsi as $op): ?>
                            <label>
                                <?php if ($grup['tipe_pilihan'] == 'radio'): ?>
                                    <input type="radio" name="varian[<?php echo htmlspecialchars($grup['nama_grup']); ?>]" value="<?php echo htmlspecialchars($op['nama_opsi']).'|'.$op['tambahan_harga']; ?>" required>
                                <?php else: ?>
                                    <input type="checkbox" name="varian[<?php echo htmlspecialchars($grup['nama_grup']); ?>][]" value="<?php echo htmlspecialchars($op['nama_opsi']).'|'.$op['tambahan_harga']; ?>">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($op['nama_opsi']); ?> 
                                <?php if($op['tambahan_harga'] > 0) echo " <span style='color:#ff5722;'> (+" . format_rupiah($op['tambahan_harga']) . ")</span>"; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <div class="note-group" style="margin-top: 15px;">
                    <label>Catatan Tambahan:</label>
                    <textarea name="catatan_kustom" placeholder="Contoh: Jangan pakai bawang"></textarea>
                </div>

                <div style="margin-top: 15px;">
                    <label>Jumlah:</label>
                    <div class="quantity-control">
                        <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                        <input type="number" name="jumlah" id="item_quantity" value="1" min="1" required class="quantity-input">
                        <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
                    </div>
                </div>

                <br>
                <button type="submit" name="add_to_cart" class="btn btn-primary" style="width:100%;">Tambahkan ke Keranjang</button>
            </form>
        </div>
        
        <script>
            function updateQuantity(change) {
                const input = document.getElementById('item_quantity');
                let currentValue = parseInt(input.value);
                let newValue = currentValue + change;
                
                if (newValue < 1 || isNaN(newValue)) {
                    newValue = 1; 
                }
                
                input.value = newValue;
            }
        </script>

    <?php elseif ($page == 'cart'): ?>
        <div class="cart-container">
            <h2>Keranjang Pesanan</h2>
            <?php if (empty($_SESSION['cart'])): ?>
                <p>Keranjang masih kosong.</p>
                <a href="index.php?meja=<?php echo $no_meja; ?>" class="btn btn-primary">Pesan Menu</a>
            <?php else: ?>
                <table class="cart-table">
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
                            <td data-label="Menu">
                                <strong><?php echo htmlspecialchars($item['nama_menu']); ?></strong>
                            </td>
                            <td data-label="Detail">
                                <small>
                                    <?php if($item['varian']) echo "Varian: " . htmlspecialchars($item['varian']) . "<br>"; ?>
                                    <?php if($item['catatan']) echo "<em>Catatan: " . htmlspecialchars($item['catatan']) . "</em>"; ?>
                                </small>
                            </td>
                            <td data-label="Jumlah"><?php echo $item['jumlah']; ?></td>
                            <td data-label="Subtotal"><?php echo format_rupiah($item['subtotal']); ?></td>
                            <td data-label="Aksi">
                                <a href="index.php?action=hapus&index=<?php echo $index; ?>&meja=<?php echo $no_meja; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="total-summary">
                    Total: <?php echo format_rupiah($grand_total); ?>
                </div>

                <div class="checkout-box">
                    <h3>Konfirmasi Pemesanan</h3>
                    <form action="proses_pesanan.php" method="POST">
                        <label for="nama_pemesan">Nama Pemesan:</label>
                        <input type="text" id="nama_pemesan" name="nama_pemesan" required>
                        
                        <label>Metode Pembayaran:</label>
                        <div style="margin-bottom: 10px;">
                            <label class="radio-label"><input type="radio" name="metode_bayar" value="kasir" checked> Bayar di Kasir</label>
                            <label class="radio-label"><input type="radio" name="metode_bayar" value="online"> Bayar Online</label>
                        </div>
                        
                        <button type="submit" class="btn btn-success" style="width: 100%;">PROSES PESANAN</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

    <?php else: // Menu Page ?>
        
        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'sukses'): ?>
            <div class="alert-success container-frontend">
                Menu berhasil ditambahkan ke keranjang!
            </div>
        <?php endif; ?>

        <div class="container-frontend">
            <div class="menu-nav">
                <a href="index.php?meja=<?php echo $no_meja; ?>" class="btn">Semua</a>
                <?php 
                    $kategoris = fetch_all(query("SELECT * FROM tbl_kategori"));
                    foreach($kategoris as $kat): 
                ?>
                <a href="index.php?kategori=<?php echo $kat['id_kategori']; ?>&meja=<?php echo $no_meja; ?>" class="btn"><?php echo htmlspecialchars($kat['nama_kategori']); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="menu-grid">
                <?php 
                    $where = "";
                    if (isset($_GET['kategori'])) {
                        $id_kat = (int)$_GET['kategori'];
                        $where = "WHERE id_kategori = $id_kat";
                    }
                    $menu_list = fetch_all(query("SELECT * FROM tbl_menu $where ORDER BY id_menu DESC"));
                ?>
                
                <?php foreach ($menu_list as $m): ?>
                <div class="menu-card">
                    <img src="img/<?php echo $m['foto'] ? htmlspecialchars($m['foto']) : 'placeholder.jpg'; ?>" alt="Foto <?php echo htmlspecialchars($m['nama_menu']); ?>">
                    <div class="menu-card-body">
                        <h4><?php echo htmlspecialchars($m['nama_menu']); ?></h4>
                        <p><?php echo format_rupiah($m['harga_dasar']); ?></p>
                        <a href="index.php?page=detail&id=<?php echo $m['id_menu']; ?>&meja=<?php echo $no_meja; ?>" class="btn btn-primary">Pesan</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>