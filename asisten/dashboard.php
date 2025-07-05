<?php
// Aktifkan pelaporan error secara eksplisit untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Definisi Variabel untuk Template
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// 2. Panggil Header (ini sudah memuat config.php dan cek autentikasi)
require_once 'templates/header.php'; 
require_once '../includes/config.php'; // Memastikan koneksi database tersedia

// --- Ambil Data Statistik Aktual dari Database ---
$total_modul_diajarkan = 0;
$total_laporan_masuk = 0;
$laporan_belum_dinilai = 0;

// Total Modul Diajarkan
$stmt_modul_count = $conn->prepare("SELECT COUNT(id) AS total_modul FROM modul"); //
$stmt_modul_count->execute();
$result_modul_count = $stmt_modul_count->get_result();
if ($result_modul_count && $result_modul_count->num_rows > 0) {
    $row_modul_count = $result_modul_count->fetch_assoc();
    $total_modul_diajarkan = $row_modul_count['total_modul'];
}
$stmt_modul_count->close();

// Total Laporan Masuk
$stmt_laporan_masuk = $conn->prepare("SELECT COUNT(id) AS total_laporan FROM laporan"); //
$stmt_laporan_masuk->execute();
$result_laporan_masuk = $stmt_laporan_masuk->get_result();
if ($result_laporan_masuk && $result_laporan_masuk->num_rows > 0) {
    $row_laporan_masuk = $result_laporan_masuk->fetch_assoc();
    $total_laporan_masuk = $row_laporan_masuk['total_laporan'];
}
$stmt_laporan_masuk->close();

// Laporan Belum Dinilai
$stmt_belum_dinilai = $conn->prepare("SELECT COUNT(id) AS belum_dinilai FROM laporan WHERE nilai IS NULL"); //
$stmt_belum_dinilai->execute();
$result_belum_dinilai = $stmt_belum_dinilai->get_result();
if ($result_belum_dinilai && $result_belum_dinilai->num_rows > 0) {
    $row_belum_dinilai = $result_belum_dinilai->fetch_assoc();
    $laporan_belum_dinilai = $row_belum_dinilai['belum_dinilai'];
}
$stmt_belum_dinilai->close();

// Aktivitas Laporan Terbaru
$recent_activities = [];
$sql_activities = "SELECT l.tanggal_kumpul, u.nama AS nama_mahasiswa, m.nama_modul, mp.nama_praktikum
                   FROM laporan l
                   JOIN users u ON l.mahasiswa_id = u.id
                   JOIN modul m ON l.modul_id = m.id
                   JOIN mata_praktikum mp ON m.praktikum_id = mp.id
                   ORDER BY l.tanggal_kumpul DESC LIMIT 5"; //
$result_activities = $conn->query($sql_activities);

if ($result_activities && $result_activities->num_rows > 0) {
    while ($activity = $result_activities->fetch_assoc()) {
        $recent_activities[] = $activity;
    }
}

?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-pastelblue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-pastelblue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-subtletext-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-darkblue-800"><?php echo $total_modul_diajarkan; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-pastelblue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-pastelblue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-subtletext-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-darkblue-800"><?php echo $total_laporan_masuk; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-pastelpink-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-pastelpink-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-subtletext-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-pastelpink-600"><?php echo $laporan_belum_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-md mt-8">
    <h3 class="text-xl font-bold text-darkblue-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-pastelblue-200 flex items-center justify-center mr-4">
                        <span class="font-bold text-pastelblue-700 text-sm"><?php echo strtoupper(substr($activity['nama_mahasiswa'], 0, 2)); ?></span>
                    </div>
                    <div>
                        <p class="text-darkblue-800"><strong><?php echo htmlspecialchars($activity['nama_mahasiswa']); ?></strong> mengumpulkan laporan untuk <strong><?php echo htmlspecialchars($activity['nama_modul']); ?></strong> pada praktikum <strong><?php echo htmlspecialchars($activity['nama_praktikum']); ?></strong></p>
                        <p class="text-sm text-subtletext-500"><?php echo date('d M Y, H:i', strtotime($activity['tanggal_kumpul'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-subtletext-500 py-4">Tidak ada aktivitas laporan terbaru.</p>
        <?php endif; ?>
    </div>
</div>


<?php
$conn->close();
require_once 'templates/footer.php';
?>