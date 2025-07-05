<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$id_praktikum_redirect = isset($_POST['id_praktikum']) ? intval($_POST['id_praktikum']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_laporan'])) {
    $id_modul     = intval($_POST['id_modul']);
    $id_mahasiswa = $_SESSION['user_id'];

    if ($id_praktikum_redirect === 0) {
        header("Location: my_courses.php?upload_status=gagal&message=ID Praktikum tidak ditemukan pada saat upload.");
        exit();
    }

    if ($_FILES['file_laporan']['error'] === 0) {
        $target_dir = "../uploads/laporan/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0775, true);
        }

        if (is_writable($target_dir)) {
            $ext = pathinfo($_FILES["file_laporan"]["name"], PATHINFO_EXTENSION);
            $allowed_ext = ['pdf', 'docx', 'zip', 'rar'];
            if (!in_array(strtolower($ext), $allowed_ext)) {
                header("Location: courses_detail.php?id=$id_praktikum_redirect&upload_status=gagal&message=Format file tidak diizinkan.");
                exit();
            }

            $max_file_size = 10 * 1024 * 1024;
            if ($_FILES['file_laporan']['size'] > $max_file_size) {
                header("Location: courses_detail.php?id=$id_praktikum_redirect&upload_status=gagal&message=Ukuran file terlalu besar (maks 10MB).");
                exit();
            }

            $filename = "laporan_" . $id_modul . "_" . $id_mahasiswa . "_" . time() . "." . $ext;
            $destination = $target_dir . $filename;

            if (move_uploaded_file($_FILES["file_laporan"]["tmp_name"], $destination)) {
                $stmt_check = $conn->prepare("SELECT id FROM laporan WHERE modul_id = ? AND mahasiswa_id = ?"); //
                $stmt_check->bind_param("ii", $id_modul, $id_mahasiswa);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    $stmt_update = $conn->prepare("UPDATE laporan SET file_laporan = ?, tanggal_kumpul = CURRENT_TIMESTAMP(), status = 'Terkumpul', nilai = NULL, feedback = NULL WHERE modul_id = ? AND mahasiswa_id = ?"); //
                    $stmt_update->bind_param("sii", $filename, $id_modul, $id_mahasiswa);
                    $stmt_update->execute();
                    $stmt_update->close();
                } else {
                    $stmt_insert = $conn->prepare("INSERT INTO laporan (modul_id, mahasiswa_id, file_laporan) VALUES (?, ?, ?)"); //
                    $stmt_insert->bind_param("iis", $id_modul, $id_mahasiswa, $filename);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                }
                $stmt_check->close();

                // Hapus bagian pembuatan notifikasi ke sesi di sini
                header("Location: courses_detail.php?id=$id_praktikum_redirect&upload_status=sukses");
                exit();
            }
        }
    }
}

if ($id_praktikum_redirect === 0) {
    header("Location: my_courses.php?upload_status=gagal&message=ID Praktikum tidak diketahui untuk redirect.");
} else {
    header("Location: courses_detail.php?id=$id_praktikum_redirect&upload_status=gagal");
}
exit();