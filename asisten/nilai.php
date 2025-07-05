<?php
// Aktifkan pelaporan error secara eksplisit untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan session sudah dimulai, dan user adalah asisten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_laporan = intval($_POST['id_laporan']);
    $nilai = intval($_POST['nilai']);
    $feedback = $_POST['feedback'] ?? null; // Tambahkan kolom feedback jika ada di form

    // Pastikan nilai antara 0-100
    if ($nilai < 0 || $nilai > 100) {
        header("Location: laporan.php?status=gagal&message=Nilai harus antara 0 dan 100.");
        exit();
    }

    // Mengubah status laporan menjadi 'Dinilai'
    $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, feedback = ?, status = 'Dinilai' WHERE id = ?"); //
    $stmt->bind_param("isi", $nilai, $feedback, $id_laporan);

    if ($stmt->execute()) {
        header("Location: laporan.php?status=sukses");
    } else {
        header("Location: laporan.php?status=gagal&message=Error database: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: laporan.php");
    exit();
}