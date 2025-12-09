<?php 
// admin/dashboard.php
session_start();
require_once '../includes/functions.php';

// Cek apakah user sudah login
is_logged_in();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Resto Kita</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #333; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .menu-card { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px; text-decoration: none; font-weight: bold; transition: background 0.3s; }
        .menu-card:hover { background: #0056b3; }
        .menu-card.logout { background: #dc3545; }
        .menu-card.logout:hover { background: #a71d2a; }
        .menu-card.dapur { background: #ffc107; color: black; }
        .menu-card.dapur:hover { background: #e0a800; }
        .welcome { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    </style>
</head>
<body>

    <div class="container">
        <div class="welcome">
            <h1>Dashboard Admin</h1>
            <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
        </div>

        <div class="menu-grid">
            <a href="crud_kategori.php" class="menu-card">Kelola Kategori</a>
            <a href="crud_menu.php" class="menu-card">Kelola Menu & Varian</a>
            <a href="export_laporan.php" class="menu-card" target="_blank">Download Laporan (CSV)</a>
            <a href="../dapur/index.php" class="menu-card dapur" target="_blank">Lihat Tampilan Dapur</a>
            <a href="logout.php" class="menu-card logout">Logout</a>
        </div>
    </div>

</body>
</html>