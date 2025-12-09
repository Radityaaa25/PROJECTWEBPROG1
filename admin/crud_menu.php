<?php 

  session_start();
  require_once '../includes/functions.php';

  is_logged_in();

  $pesan = '';
  $menu_edit = null;

  //ambil semua data untuk ditampilkan di form dropdow
  $sql_kategori = "SELECT id_kategori, nama_kategori FROM tbl_kategori ORDER BY nama_kategori ASC";
  $list_kategori = fetch_all(query($sql_kategori));

  function upload_foto($file) {
    $target_dir = "../img/";
    $nama_file_asli = basename($file["name"]);
    $ekstensi_file = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));
    $nama_file_unik = uniqid('menu_') . "." . $ekstensi_file;
    $target_file = $target_dir . $nama_file_unik;

    if ($file["size"] > 5000000) {
      return ['error' => "Ukuran file terlalu besar ( Maksimal 5MB)."];
    }

    if($ekstensi_file != "jpg" && $ekstensi_file != "png" && $ekstensi_file != "jpeg") {
      return ['error' => "Hanya file JPG, JPEG, & PNG yang diizinkan."];
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
      return ['filename' => $nama_file_unik];
    } else {
      return ['error' => "terjadi kesalahan saat meng-upload file."];
    }
  }

  // proses tambah data (Create)
  if (isset($_POST['tambah_menu'])) {
    $nama_menu = clean_input($_POST['nama_menu']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $harga_dasar = (int)$_POST['harga_dasar'];
    $id_kategori = (int)$_POST['id_kategori'];

    $foto_path = '';

    if (!empty($_FILES['foto']['name'])) {
      $upload_result = upload_foto($_FILES['foto']);
      if (isset($upload_result['error'])){
        $pesan = "Gagal Tambah Menu: " . $upload_result['error'];
      } else {
        $foto_path = $upload_result['filename'];
        $sql = "INSERT INTO tbl_menu (id_kategori, nama_menu, deskripsi, harga_dasar, foto) VALUES ($id_kategori, '$nama_menu', '$deskripsi', $harga_dasar, '$foto_path')";
        query($sql);
        $pesan = "Menu **$nama_menu** berhasil ditambahkan!";
      } 
    } else {
        $pesan = "Gagal Tambah Menu: Foto wajib diupload.";
      }
      header('Location: crud_menu.php?pesan=' .urlencode($pesan));
      exit();
  }

  //hapus data (Delete)
  if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id_menu = (int)$_GET['id'];

    //ambil nama file foto lama
    $sql_cek = "SELECT foto FROM tbl_menu WHERE id_menu = $id_menu";
    $result_cek =query($sql_cek);
    $menu = fetch_assoc($result_cek);

    if ($menu) {
      // hapus file foto dari server
      $file_to_delete = "../img/" . $menu['foto'];
      if (file_exists($file_to_delete) && $menu['foto'] != '') {
        unlink($file_to_delete);
      }

      //hapus data dari database
      $sql = "DELETE FROM tbl_menu WHERE id_menu = $id_menu";
      query($sql);
      $pesan = "Menu berhasil dihapus!";
    } else {
      $pesan = "Menu tidak ditemukan.";
    }
    header('Location: crud_menu.php?pesan=' .urlencode($pesan));
    exit();
  }

  // ambil data edit 
  if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_menu = (int)$_GET['id'];
    $sql = "SELECT * FROM tbl_menu WHERE id_menu = $id_menu";
    $result = query($sql);
    $menu_edit = fetch_assoc($result);
    if (!$menu_edit) {
      header('Location: crud_menu.php');
      exit();
    }
  }

  // update data
  if (isset($_POST['update_menu'])) {
    $id_menu = (int)$_POST['id_menu'];
    $nama_menu = clean_input($_POST['nama_menu']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $harga_dasar = (int)$_POST['harga_dasar'];
    $id_kategori = (int)$_POST['id_kategori'];
    $foto_lama = clean_input($_POST['foto_lama']);
    $update_foto_sql = '';

    $is_update_ok = true;

    // cek apa ada foto yang baru diupload
    if (!empty($_FILES['foto']['name'])) {
      $upload_result = upload_foto($_FILES['foto']);
      if (isset($upload_result['error'])) {
        $pesan = "Gagagl Update Menu: " . $upload_result['error'];
        $is_update_ok = false;
      } else {
        // hapus foto lama diserver
        $file_to_delete = "../img/" . $foto_lama;
        if (file_exists($file_to_delete) && $foto_lama != '') {
          unlink($file_to_delete);
        }
        //tambah update foto ke query
        $foto_path = $upload_result['filename'];
        $update_foto_sql = ", foto = '$foto_path'";
      }
    }

    // lakukan update database jika tidak ada error
    if ($is_update_ok) {
      $sql = "UPDATE tbl_menu SET
      id_kategori = $id_kategori,
      nama_menu = '$nama_menu',
      deskripsi = '$deskripsi',
      harga_dasar = $harga_dasar
      $update_foto_sql
      WHERE id_menu = $id_menu";
      query($sql);
      $pesan = "Menu **$nama_menu** berhasil diupdate!";
    }
    header('Location: crud_menu.php?pesan=' .urlencode($pesan));
    exit();
  }

  // ambil pesan dari url
  if (isset($_GET['pesan'])) {
    $pesan = urldecode($_GET['pesan']);
  }

  //ambil semua data menu untuk ditampilkan
  $sql_menu = "SELECt
  m.id_menu, m.nama_menu, m.harga_dasar, m.foto, k.nama_kategori
  FROM tbl_menu m
  JOIN tbl_kategori k ON m.id_kategori = k.id_kategori
  ORDER BY m.id_menu DESC
  ";
  $list_menu = fetch_all(query($sql_menu));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CRUD Menu - Admin</title>
  <style>
        /* CSS Sederhana untuk demo */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;}
        .nav-links a { margin-right: 15px; text-decoration: none; color: #007bff; }
        .form-menu { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #eee; }
        .form-menu div { margin-bottom: 10px; }
        .form-menu label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-menu input[type="text"], 
        .form-menu input[type="number"], 
        .form-menu textarea, 
        .form-menu select,
        .form-menu input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .form-menu button { padding: 8px 15px; background-color: #ff5722; color: white; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        .form-menu button.update { background-color: #28a745; }
        .form-menu a { text-decoration: none; color: #dc3545; margin-left: 10px; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .aksi a { margin-right: 5px; text-decoration: none; color: #007bff; }
        .aksi a.hapus { color: #dc3545; }
        .foto-preview { max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>
  <div class="container">
    <h1>Kelola Item Menu</h1>
    <div class="nav-links">
      <a href="dashboard.php">&larr; Kembali ke Dashboard</a>
      <a href="crud_kategori.php">Kelola Kategori</a>
    </div>
    <hr style="margin: 15px 0;">

    <?php if ($pesan): ?>
      <div class="<?php echo strpos($pesan, 'Gagal') !== false ? 'alert-error' : 'alert-success'; ?>">
        <?php echo $pesan; ?>
      </div>
    <?php endif; ?>

    <div class="form-menu">
      <h3>
        <?php echo $menu_edit ? 'Ubah Menu: ' . htmlspecialchars($menu_edit['nama_menu']) : 'Tambah Menu Baru'; ?>
      </h3>
      <form action="crud_menu.php" method="POST" enctype="multipart/form-data">
        <?php if ($menu_edit): ?>
          <input type="hidden" name="id_menu" value="<?php echo $menu_edit['id_menu']; ?>">
          <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($menu_edit['foto']); ?>">
        <?php endif; ?>

        <div>
          <label for="nama_menu">Nama Menu</label>
          <input type="text" name="nama_menu" id="nama_menu" value="<?php echo $menu_edit ? htmlspecialchars($menu_edit['nama_menu']) : ''; ?> ">
        </div>

        <div>
          <label for="id_kategori">Kategori</label>
          <select name="id_kategori" id="id_kategori">
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($list_kategori as $kategori): ?>
              <?php $selected = $menu_edit && $menu_edit['id_kategori'] == $kategori['id_kategori'] ? 'selected' : ''; ?>
              <option value="<?php echo $kategori['id_kategori']; ?>" <?php echo $selected; ?>>
                <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div>
          <label for="deskripsi">Deskripsi</label>
          <textarea name="deskripsi" id="deskripsi" rows="3"><?php echo $menu_edit ? htmlspecialchars($menu_edit[$deskripsi]) : '' ?></textarea>
        </div>

        <div>
          <label for="harga_dasar">Harga Dasar (Rp)</label>
          <input type="number" name="harga_dasar" id="harga_dasar" value="<?php echo $menu_edit ? $menu_edit['harga_dasar'] : ''; ?>" required>
        </div>

        <div>
          <label for="foto">Foto Menu (.jpg/.png) - Maks 5mb</label>
          <input type="file" name="foto" id="foto" <?php echo $menu_edit ? '' : 'required'; ?>>
          <?php if ($menu_edit && $menu_edit['foto']): ?>
            <p style="margin-top: 5px;">Foto saat ini: <img src="../img/<?php echo htmlspecialchars($menu_edit['foto']); ?>" alt="Foto Menu" class="foto-preview"></p>
            <p style="font-size: 0.8rem; color: #666;">Kosongkan jika tidak ingin mengganti foto.</p>
          <?php endif; ?>
        </div>

        <button type="submit" name="<?php echo $menu_edit ? 'update_menu' : 'tambah_menu'; ?>" class="<?php echo $menu_edit ? 'update' : ''; ?>">
          <?php echo $menu_edit ? 'Update Menu' : 'Tambah Menu'; ?>
        </button>
          <?php if ($menu_edit): ?>
            <a href="crud_menu.php">Batal</a>
          <?php endif; ?>
      </form>
    </div>

    <h2>Daftar Menu (Total: <?php echo count($list_menu); ?>)</h2>
    <table>
      <thead>
        <tr>
          <th>Foto</th>
          <th>Nama Menu</th>
          <th>Kategori</th>
          <th>Harga</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($list_menu)): ?>
          <?php foreach ($list_menu as $menu): ?>
            <tr>
              <td>
                <?php if ($menu['foto']): ?>
                  <img src="../img/<?php echo htmlspecialchars($menu['foto']); ?>" alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>" class="foto-preview">
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($menu['nama_menu']); ?></td>
              <td><?php echo htmlspecialchars($menu['nama_kategori']); ?></td>
              <td>Rp <?php echo number_format($menu['harga_dasar'], 0, ',', ','); ?></td>
              <td class="aksi">
                <a href="crud_menu.php?action=edit&id=<?php echo $menu['id_menu']; ?>">Edit</a>
                <a href="crud_varian.php?id_menu=<?php echo $menu['id_menu']; ?>">Varian</a>
                <a href="crud_menu.php?action=hapus&id=<?php echo $menu['id_menu']; ?>" onclick="return confirm('Yakin ingin menghapus menu ini? Semua varian terkait juga akan terhapus!')" class="hapus">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" style="text-align: center;">Belum ada data menu.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>