<?php
// HAPUS baris-baris debugging berikut:
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// echo "DEBUG: config.php dimuat.<br>";

// Ini adalah contoh isi config.php, sesuaikan dengan koneksi database Anda
$servername = "localhost"; // Sesuaikan jika berbeda
$username = "root";        // Sesuaikan dengan username database Anda
$password = "";            // Sesuaikan dengan password database Anda
$dbname = "pengumpulantugas"; // Sesuaikan dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Biarkan die() untuk error koneksi fatal
    die("Koneksi database GAGAL: " . $conn->connect_error);
}
// HAPUS baris-baris debugging berikut:
// echo "DEBUG: Koneksi database BERHASIL.<br>";

// Opsional: Pastikan session dimulai hanya jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // HAPUS baris debugging berikut:
    // echo "DEBUG: Session dimulai di config.php.<br>";
}
// HAPUS baris debugging berikut:
// echo "DEBUG: Akhir config.php.<br>";
?>