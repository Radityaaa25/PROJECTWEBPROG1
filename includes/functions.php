<?php 
// includes/functions.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php'; 

function is_logged_in() {
    if (!isset($_SESSION['id_admin'])) {
        header('Location: ../admin/index.php');
        exit();
    }
}

function clean_input($data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($koneksi, $data);
    return $data;
}

function query($sql) {
    global $koneksi;
    $result = mysqli_query($koneksi, $sql);
    if(!$result) {
        die("Query Gagal: " . mysqli_error($koneksi));
    }
    return $result;
}

function fetch_assoc($result) {
    return mysqli_fetch_assoc($result);
}

function fetch_all($result) {
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function format_rupiah($angka){
    return "Rp " . number_format($angka,0,',','.');
}
?>