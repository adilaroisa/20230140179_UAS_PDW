<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_praktikum = intval($_GET['id']);
    $id_mahasiswa = $_SESSION['user_id'];

    // Cek apakah sudah pernah mendaftar
    $stmt_check = $conn->prepare("SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?"); //
    $stmt_check->bind_param("ii", $id_mahasiswa, $id_praktikum);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        // Belum terdaftar → insert baru
        $stmt_insert = $conn->prepare("INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)"); //
        $stmt_insert->bind_param("ii", $id_mahasiswa, $id_praktikum);
        // Hapus bagian pembuatan notifikasi ke sesi di sini (karena notifikasi dihitung ulang di dashboard)
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    $stmt_check->close();
    $conn->close();

    header("Location: my_courses.php?status=sukses"); // Tetap redirect ke my_courses
    exit();
} else {
    header("Location: courses.php");
    exit();
}