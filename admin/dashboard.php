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
    <link rel="stylesheet" href="../css/style.css?v=3">
</head>
<body>

    <div class="container-admin">
        <div class="welcome">
            <h1>Dashboard Admin</h1>
            <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
        </div>

        <div class="admin-menu-grid">
            <a href="crud_kategori.php" class="admin-menu-card admin-menu-card-blue">Kelola Kategori</a>
            <a href="crud_menu.php" class="admin-menu-card admin-menu-card-blue">Kelola Menu & Varian</a>
            <a href="export_laporan.php" class="admin-menu-card admin-menu-card-blue" target="_blank">Download Laporan (CSV)</a>
            <a href="../dapur/index.php" class="admin-menu-card admin-menu-card-yellow" target="_blank">Lihat Tampilan Dapur</a>
            <a href="logout.php" class="admin-menu-card admin-menu-card-red">Logout</a>
        </div>
    </div>

</body>
</html>