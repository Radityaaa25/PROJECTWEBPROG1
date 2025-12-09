<?php 
  session_start();
  require_once '../includes/functions.php';

  // cek apa admin udah login
  is_logged_in();

  $pesan = '';

  // tambah data (Create)
  if (isset($_POST['tambah_kategori'])) {
  $nama_kategori = clean_input($_POST['nama_kategori']);
    if (!empty($nama_kategori)) {
      $sql ="INSERT INTO tbl_kategori (nama_kategori) VALUES ('$nama_kategori')";
      query($sql);
      $pesan = "Kategori **$nama_kategori** berhasil ditambahkan!";
    }
  }
  
  // hapus data (Delete)
  if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id_kategori = (int)$_GET['id'];
    
    $sql_cek = "SELECT nama_kategori FROM tbl_kategori WHERE id_kategori =$id_kategori";
    $result_cek = query($sql_cek);
    $kategori = fetch_assoc($result_cek);
    
    if ($kategori) {
      $nama_kategori = $kategori['nama_kategori'];
      $sql = "DELETE FROM tbl_kategori WHERE id_kategori = $id_kategori";
      query($sql);
      $pesan = "Kategori **$nama_kategori** berhasil dihapus!";
    } else {
      $pesan = "Kategori tidak ditemukan.";
    }

    // redirect untuk mencegah re-submit form saat refresh
    header('Location: crud_kategori.php?pesan=' .urlencode($pesan));
    exit();
  }

  // ambil data untuk edit (Read untuk Update)
  $kategori_edit = null;
  if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_kategori = (int)$_GET['id'];
    $sql = "SELECT id_kategori, nama_kategori FROM tbl_kategori WHERE id_kategori = $id_kategori";
    $result = query($sql);
    $kategori_edit = fetch_assoc($result);
    if (!$kategori_edit) {
      header('Location: crud_kategori.php');
      exit();
    }
  }

  // proses update data (Update)
  if(isset($_POST['update_kategori'])) {
    $id_kategori = (int)$_POST['id_kategori'];

    $nama_kategori = clean_input($_POST['nama_kategori']);

    $sql = "UPDATE tbl_kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = $id_kategori";
    query($sql);
    $pesan = "Kategori **$nama_kategori** berhasil diupdate!"; 

    header('Location: crud_kategori.php?pesan=' .urlencode($pesan));
    exit();
  }

  //ambil pesan dari URL
  if (isset($_GET['pesan'])) {
    $pesan = urldecode($_GET['pesan']);
  }

  //ambil semua data kategori untuk ditampilkan di tabel
  $sql_kategori = "SELECT * FROM tbl_kategori ORDER BY id_kategori DESC";
  $list_kategori = fetch_all(query($sql_kategori));

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CRUD Kategori - Admin</title>
  <style>
        /* CSS Sederhana untuk demo */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;}
        .nav-links a { margin-right: 15px; text-decoration: none; color: #007bff; }
        .form-crud { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #eee; }
        .form-crud input[type="text"] { padding: 8px; width: 60%; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .form-crud button { padding: 8px 15px; background-color: #ff5722; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .form-crud button.update { background-color: #28a745; }
        .form-crud a { text-decoration: none; color: #dc3545; margin-left: 10px; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .aksi a { margin-right: 8px; text-decoration: none; color: #007bff; }
        .aksi a.hapus { color: #dc3545; }
    </style>
</head>
<body>
  
  <div class="container">
    <h1>Kelola Kategori Menu</h1>
    <div class="nav-links">
      <a href="dashboard.php">&larr; Kembali ke Dashboard</a>
      <a href="crud_menu.php">Kelola Menu &rarr;</a>
    </div>
    <hr style="margin: 15px 0;">

    <?php if ($pesan): ?>
      <div class="alert-success">
        <?php echo $pesan?>
      </div>
    <?php endif; ?>

    <div class="form-crud">
      <h3>
        <?php echo $kategori_edit ? 'Ubah Kategori' : 'Tambah Kategori Baru'; ?>
      </h3>
      <form action="crud_kategori.php" method="POST">
        <?php if ($kategori_edit): ?>
          <input type="hidden" name="id_kategori" value="<?php echo $kategori_edit['id_kategori']; ?>">
          <input type="text" name="nama_kategori" value="<?php echo htmlspecialchars($kategori_edit['nama_kategori']); ?>" required>
          <button type="submit" name="update_kategori" class="update">Update</button>
          <a href="crud_kategori.php">Batal</a>
        <?php else: ?>
          <input type="text" name="nama_kategori" placeholder="Nama Kategori, cth: Makanan Utama" required>
          <button type="submit" name="tambah_kategori">Tambah</button>
          <?php endif; ?>
      </form>
    </div>
    
    <h2>Daftar Kategori</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Kategori</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($list_kategori)): ?>
          <?php foreach ($list_kategori as $kategori): ?>
            <tr>
              <td><?php echo $kategori['id_kategori']; ?></td>
              <td><?php echo htmlspecialchars($kategori['nama_kategori']); ?></td>
              <td class="aksi">
                <a href="crud_kategori.php?action=edit&id=<?php echo $kategori['id_kategori']; ?>">Edit</a>
                <a href="crud_kategori.php?action=hapus&id=<?php echo $kategori['id_kategori']; ?>" onclick="return confirm('Yakin ingin manghapus kategori <?php echo htmlspecialchars($kategori['nama_kategori']); ?>?')" class="hapus">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" style="text-align: center;">Belum ada data kategori.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </div>

</body>
</html>