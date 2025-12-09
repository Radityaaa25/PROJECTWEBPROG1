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
    <style>
        /* CSS Sederhana untuk demo */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 900px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;}
        h2, h3 { margin-top: 20px; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .nav-links a { margin-right: 15px; text-decoration: none; color: #007bff; }
        .form-group { margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-right: 10px; }
        .form-group button { padding: 8px 15px; background-color: #ff5722; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .form-group.opsi button { background-color: #007bff; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .grup-box { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 5px; background: #fff; }
        .opsi-list { list-style: none; padding: 0; margin-top: 10px; }
        .opsi-list li { padding: 8px 0; border-bottom: 1px dotted #eee; display: flex; justify-content: space-between; }
        .opsi-list li:last-child { border-bottom: none; }
        .aksi-opsi a { color: #dc3545; text-decoration: none; margin-left: 10px; font-size: 0.9em; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Kelola Varian untuk: <?php echo htmlspecialchars($menu['nama_menu']); ?></h1>
        <div class="nav-links">
            <a href="crud_menu.php">&larr; Kembali ke Daftar Menu</a>
        </div>
        <hr style="margin: 15px 0;">

        <?php if ($pesan): ?>
            <div class="alert-success"><?php echo $pesan; ?></div>
        <?php endif; ?>

        <div class="form-group">
            <h3>Tambah Grup Varian Baru</h3>
            <form action="crud_varian.php?id_menu=<?php echo $id_menu; ?>" method="POST">
                <div style="margin-bottom: 10px;">
                    <label for="nama_grup">Nama Grup (Cth: Suhu, Level Pedas)</label>
                    <input type="text" name="nama_grup" id="nama_grup" required>
                </div>
                <div style="margin-bottom: 10px;">
                    <label for="tipe_pilihan">Tipe Pilihan</label>
                    <select name="tipe_pilihan" id="tipe_pilihan" required>
                        <option value="radio">Radio (Pilih Satu)</option>
                        <option value="checkbox">Checkbox (Pilih Banyak)</option>
                    </select>
                </div>
                <button type="submit" name="tambah_grup">Tambah Grup</button>
            </form>
        </div>
        
        <h2>Daftar Grup Varian</h2>
        <?php if (empty($list_grup)): ?>
            <p>Belum ada Grup Varian untuk menu ini.</p>
        <?php else: ?>
            <?php foreach ($list_grup as $grup): ?>
                <div class="grup-box">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0;">
                            <?php echo htmlspecialchars($grup['nama_grup']); ?> 
                            <span style="font-size: 0.8em; font-weight: normal; color: #666;">(<?php echo ucfirst($grup['tipe_pilihan']); ?>)</span>
                        </h3>
                        <a 
                            href="crud_varian.php?id_menu=<?php echo $id_menu; ?>&action=hapus_grup&id_grup=<?php echo $grup['id_grup_varian']; ?>" 
                            onclick="return confirm('Yakin hapus grup <?php echo htmlspecialchars($grup['nama_grup']); ?>?')" 
                            style="color: #dc3545;"
                        >
                            Hapus Grup
                        </a>
                    </div>

                    <div class="form-group opsi">
                        <h4>Tambah Opsi untuk <?php echo htmlspecialchars($grup['nama_grup']); ?></h4>
                        <form action="crud_varian.php?id_menu=<?php echo $id_menu; ?>" method="POST">
                            <input type="hidden" name="id_grup_varian" value="<?php echo $grup['id_grup_varian']; ?>">
                            <label for="nama_opsi">Nama Opsi (Cth: Dingin, Extra Pedas)</label>
                            <input type="text" name="nama_opsi" placeholder="Nama Opsi" required style="width: 30%;">
                            
                            <label for="tambahan_harga">Tambahan Harga (Cth: 2000)</label>
                            <input type="number" name="tambahan_harga" placeholder="Harga Tambahan" value="0" required style="width: 30%;">
                            
                            <button type="submit" name="tambah_opsi">Tambah Opsi</button>
                        </form>
                    </div>

                    <h4>Daftar Opsi</h4>
                    <ul class="opsi-list">
                        <?php if (isset($list_opsi[$grup['id_grup_varian']]) && !empty($list_opsi[$grup['id_grup_varian']])): ?>
                            <?php foreach ($list_opsi[$grup['id_grup_varian']] as $opsi): ?>
                                <li>
                                    <span>
                                        <?php echo htmlspecialchars($opsi['nama_opsi']); ?> 
                                        (Rp <?php echo number_format($opsi['tambahan_harga'], 0, ',', '.'); ?>)
                                    </span>
                                    <span class="aksi-opsi">
                                        <a 
                                            href="crud_varian.php?id_menu=<?php echo $id_menu; ?>&action=hapus_opsi&id_opsi=<?php echo $opsi['id_opsi_varian']; ?>" 
                                            onclick="return confirm('Yakin hapus opsi <?php echo htmlspecialchars($opsi['nama_opsi']); ?>?')"
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