<?php
  session_start();
  require_once '../includes/functions.php';

  is_logged_in();
  
  $pesan = '';

  // wajib ada id_menu di URL
  if (!isset($_GET['id_menu']) || empty($_GET['id_menu'])) {
    header('Location: crud_menu.php');
    exit();
  }

  $id_menu = (int)$_GET['id_menu'];

  // ambil info menu utama
  $sql_menu = "SELECT id_menu, nama_menu FROM tbl_menu WHERE id_menu = $id_menu";
  $menu = fetch_assoc(query($sql_menu));

  if (!$menu) {
    header('Location: crud_menu.php');
    exit();
  }

  // tambah grup varian (Create)
  if (isset($_POST['tambah_grup'])) {
    $nama_grup = clean_input($_POST['nama_grup']);
    $tipe_pilihan = clean_input($_POST['tipe_pilihan']);

    $sql = "INSERT INTO tbl_varian_grup (id_menu, nama_grup, tipe_pilihan)
            VALUES ($id_menu, '$nama_grup', '$tipe_pilihan')";
    query($sql);
    $pesan = "Grup varian **$nama_grup** berhasil ditambahkan!";
  }

  // hapus grup varian (Delete)
  if (isset($_GET['action']) && $_GET['action'] == 'hapus_grup' && isset($_GET['id_grup'])) {
    $id_grup = (int)$_GET['id_grup'];
    $sql = "DELETE FROM tbl_varian_grup WHERE id_grup_varian = $id_grup";
    query($sql);
    $pesan = "Grup varian berhasil dihapus! (termasuk opsi opsinya).";
    header('Location: crud_varian.php?id_menu=' . $id_menu . '&pesan=' . urldecode($pesan));  
    exit(); 
  }

  // tambah opsi varian
  if (isset($_POST['tambah_opsi'])) {
    $id_grup = (int)$_POST['id_grup_varian'];
    $nama_opsi = clean_input($_POST['nama_opsi']);
    $tambahan_harga = (int)$_POST['tambahan_harga'];

    $sql = "INSERT INTO tbl_varian_opsi (id_grup_varian, nama_opsi, tambahan_harga) 
            VALUES ($id_grup, '$nama_opsi', $tambahan_harga)";
    query($sql);
    $pesan = "Opsi **$nama_opsi** berhasil ditambahkan!";
  }

  // hapus opsi varian (Delete)
  if (isset($_GET['action']) && $_GET['action'] == 'hapus_opsi' && isset($_GET['id_opsi'])) {
    $id_opsi = (int)$_GET['id_opsi'];
    $sql = "DELETE FROM tbl_varian_opsi WHERE id_opsi_varian = $id_opsi";
    query($sql);
    $pesan = "Opsi varian berhasil dihapus.";
    header('Location: crud_varian.php?id_menu=' . $id_menu . '&pesan=' . urldecode($pesan));
    exit();
  }

  // ambil pesan dari url
  if (isset($_GET['pesan'])) {
    $pesan = urldecode($_GET['pesan']);
  }

  // ambil semua grup varian untuk menu ini
  $sql_grup = "SELECT * FROM tbl_varian_grup WHERE id_menu = $id_menu ORDER BY id_grup_varian ASC";
  $list_grup = fetch_all(query($sql_grup));

  // ambil semua opsi varian, dikelompokan berdasarkan grupnya  
  $list_opsi = [];
  if (!empty($list_grup)) {
    foreach ($list_grup as $grup) {
      $sql_opsi = "SELECT * FROM tbl_varian_opsi WHERE id_grup_varian = " . $grup['id_grup_varian'] . " ORDER BY tambahan_harga ASC";
      $list_opsi[$grup['id_grup_varian']] = fetch_all(query($sql_opsi));
    }
  }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Varian Menu: <?php echo htmlspecialchars($menu['nama_menu']); ?></title>
    <link rel="stylesheet" href="../css/style.css?v=3">
</head>
<body>

    <div class="container-admin">
        <h1>Kelola Varian untuk: <?php echo htmlspecialchars($menu['nama_menu']); ?></h1>
        <div class="nav-links">
            <a href="crud_menu.php">&larr; Kembali ke Daftar Menu</a>
        </div>
        <hr style="margin: 15px 0;">

        <?php if ($pesan): ?>
            <div class="alert-success"><?php echo $pesan; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h3>Tambah Grup Varian Baru</h3>
            <form action="crud_varian.php?id_menu=<?php echo $id_menu; ?>" method="POST">
                <div>
                    <label for="nama_grup">Nama Grup (Cth: Suhu, Level Pedas)</label>
                    <input type="text" name="nama_grup" id="nama_grup" required>
                </div>
                <div>
                    <label for="tipe_pilihan">Tipe Pilihan</label>
                    <select name="tipe_pilihan" id="tipe_pilihan" required>
                        <option value="radio">Radio (Pilih Satu)</option>
                        <option value="checkbox">Checkbox (Pilih Banyak)</option>
                    </select>
                </div>
                <button type="submit" name="tambah_grup" class="btn btn-primary">Tambah Grup</button>
            </form>
        </div>
        
        <h2>Daftar Grup Varian</h2>
        <?php if (empty($list_grup)): ?>
            <p>Belum ada Grup Varian untuk menu ini.</p>
        <?php else: ?>
            <?php foreach ($list_grup as $grup): ?>
                <div class="grup-box">
                    <div class="grup-header">
                        <h3>
                            <?php echo htmlspecialchars($grup['nama_grup']); ?> 
                            <span style="font-size: 0.8em; font-weight: normal; color: #666;">(<?php echo ucfirst($grup['tipe_pilihan']); ?>)</span>
                        </h3>
                        <a 
                            href="crud_varian.php?id_menu=<?php echo $id_menu; ?>&action=hapus_grup&id_grup=<?php echo $grup['id_grup_varian']; ?>" 
                            onclick="return confirm('Yakin hapus grup <?php echo htmlspecialchars($grup['nama_grup']); ?>?')" 
                            class="btn btn-danger" style="padding: 5px 10px;"
                        >
                            Hapus Grup
                        </a>
                    </div>

                    <div class="form-card" style="background: #e9ecef;">
                        <h4>Tambah Opsi untuk <?php echo htmlspecialchars($grup['nama_grup']); ?></h4>
                        <form action="crud_varian.php?id_menu=<?php echo $id_menu; ?>" method="POST">
                            <input type="hidden" name="id_grup_varian" value="<?php echo $grup['id_grup_varian']; ?>">
                            <div class="opsi-form-inline">
                                <div>
                                    <label for="nama_opsi">Nama Opsi</label>
                                    <input type="text" name="nama_opsi" placeholder="Contoh: Dingin" required>
                                </div>
                                <div>
                                    <label for="tambahan_harga">Tambahan Harga (Rp)</label>
                                    <input type="number" name="tambahan_harga" placeholder="Contoh: 2000" value="0" required>
                                </div>
                                <button type="submit" name="tambah_opsi" class="btn btn-success" style="white-space: nowrap;">Tambah Opsi</button>
                            </div>
                        </form>
                    </div>

                    <h4>Daftar Opsi</h4>
                    <ul class="opsi-list">
                        <?php if (isset($list_opsi[$grup['id_grup_varian']]) && !empty($list_opsi[$grup['id_grup_varian']])): ?>
                            <?php foreach ($list_opsi[$grup['id_grup_varian']] as $opsi): ?>
                                <li>
                                    <span>
                                        <?php echo htmlspecialchars($opsi['nama_opsi']); ?> 
                                        (<span style="color:#ff5722;"><?php echo format_rupiah($opsi['tambahan_harga']); ?></span>)
                                    </span>
                                    <span class="aksi">
                                        <a 
                                            href="crud_varian.php?id_menu=<?php echo $id_menu; ?>&action=hapus_opsi&id_opsi=<?php echo $opsi['id_opsi_varian']; ?>" 
                                            onclick="return confirm('Yakin hapus opsi <?php echo htmlspecialchars($opsi['nama_opsi']); ?>?')"
                                            class="hapus"
                                        >
                                            Hapus
                                        </a>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Belum ada opsi di grup ini.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>