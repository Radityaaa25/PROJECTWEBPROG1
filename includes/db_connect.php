<?php 
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'resto_uas');

  $koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

  if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
  }

  $koneksi->set_charset("utf8mb4");

  // echo("Koneksi berhasil!");
?>