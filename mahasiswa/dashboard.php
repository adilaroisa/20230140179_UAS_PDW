<?php
require_once '../includes/config.php'; 

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php'; // This now handles session_start and basic checks

$display_name = isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Pengguna';

// --- Data Fetching for Dashboard ---
$total_praktikum_diikuti = 0;
$stmt_courses = $conn->prepare("SELECT COUNT(DISTINCT praktikum_id) AS total_courses FROM pendaftaran_praktikum WHERE mahasiswa_id = ?"); //
$stmt_courses->bind_param("i", $_SESSION['user_id']);
$stmt_courses->execute();
$result_courses = $stmt_courses->get_result();
if ($result_courses->num_rows > 0) {
    $row_courses = $result_courses->fetch_assoc();
    $total_praktikum_diikuti = $row_courses['total_courses'];
}
$stmt_courses->close();

$total_tugas_selesai = 0;
$stmt_completed_tasks = $conn->prepare("SELECT COUNT(*) AS total_completed FROM laporan WHERE mahasiswa_id = ? AND status = 'Dinilai'"); //
$stmt_completed_tasks->bind_param("i", $_SESSION['user_id']);
$stmt_completed_tasks->execute();
$result_completed_tasks = $stmt_completed_tasks->get_result();
if ($result_completed_tasks->num_rows > 0) {
    $row_completed_tasks = $result_completed_tasks->fetch_assoc();
    $total_tugas_selesai = $row_completed_tasks['total_completed'];
}
$stmt_completed_tasks->close();

$total_tugas_menunggu = 0;
$stmt_pending_tasks = $conn->prepare("SELECT COUNT(*) AS total_pending FROM laporan WHERE mahasiswa_id = ? AND status = 'Terkumpul'"); //
$stmt_pending_tasks->bind_param("i", $_SESSION['user_id']);
$stmt_pending_tasks->execute();
$result_pending_tasks = $stmt_pending_tasks->get_result();
if ($result_pending_tasks->num_rows > 0) {
    $row_pending_tasks = $result_pending_tasks->fetch_assoc();
    $total_tugas_menunggu = $row_pending_tasks['total_pending'];
}
$stmt_pending_tasks->close();
// --- End Data Fetching ---

// --- Logic untuk Notifikasi Aktual (Tanpa Tabel Notifikasi Khusus) ---
$recent_notifications = [];

// Notifikasi 1: Laporan yang baru dinilai
$stmt_graded_laporan = $conn->prepare("
    SELECT l.id, m.nama_modul, mp.nama_praktikum, l.nilai, l.feedback, l.tanggal_kumpul
    FROM laporan l
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.praktikum_id = mp.id
    WHERE l.mahasiswa_id = ? AND l.status = 'Dinilai' AND l.nilai IS NOT NULL
    ORDER BY l.tanggal_kumpul DESC LIMIT 2
"); //
$stmt_graded_laporan->bind_param("i", $_SESSION['user_id']);
$stmt_graded_laporan->execute();
$result_graded_laporan = $stmt_graded_laporan->get_result();
if ($result_graded_laporan) {
    while ($notif = $result_graded_laporan->fetch_assoc()) {
        $message = "Nilai Anda untuk <a href=\"courses_detail.php?id={$notif['id']}\">Modul " . htmlspecialchars($notif['nama_modul']) . "</a> pada " . htmlspecialchars($notif['nama_praktikum']) . " telah diberikan: <strong>{$notif['nilai']}</strong>.";
        $link = "courses_detail.php?id={$notif['id']}"; // Link ke detail praktikum
        $recent_notifications[] = ['message' => $message, 'link' => $link, 'type' => 'grade'];
    }
}
$stmt_graded_laporan->close();


// Notifikasi 2: Pengumpulan laporan terbaru
$stmt_recent_upload = $conn->prepare("
    SELECT l.id, m.nama_modul, mp.nama_praktikum, l.tanggal_kumpul
    FROM laporan l
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.praktikum_id = mp.id
    WHERE l.mahasiswa_id = ? AND l.tanggal_kumpul >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ORDER BY l.tanggal_kumpul DESC LIMIT 1
"); //
$stmt_recent_upload->bind_param("i", $_SESSION['user_id']);
$stmt_recent_upload->execute();
$result_recent_upload = $stmt_recent_upload->get_result();
if ($result_recent_upload && $result_recent_upload->num_rows > 0) {
    $notif = $result_recent_upload->fetch_assoc();
    $message = "Anda baru saja mengumpulkan laporan untuk <a href=\"courses_detail.php?id={$notif['id']}\">Modul " . htmlspecialchars($notif['nama_modul']) . "</a> pada " . htmlspecialchars($notif['nama_praktikum']) . ".";
    $link = "courses_detail.php?id={$notif['id']}";
    $recent_notifications[] = ['message' => $message, 'link' => $link, 'type' => 'upload'];
}
$stmt_recent_upload->close();


// Notifikasi 3: Pendaftaran praktikum terbaru
$stmt_recent_registration = $conn->prepare("
    SELECT pp.id, mp.nama_praktikum, pp.tanggal_daftar
    FROM pendaftaran_praktikum pp
    JOIN mata_praktikum mp ON pp.praktikum_id = mp.id
    WHERE pp.mahasiswa_id = ? AND pp.tanggal_daftar >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ORDER BY pp.tanggal_daftar DESC LIMIT 1
"); //
$stmt_recent_registration->bind_param("i", $_SESSION['user_id']);
$stmt_recent_registration->execute();
$result_recent_registration = $stmt_recent_registration->get_result();
if ($result_recent_registration && $result_recent_registration->num_rows > 0) {
    $notif = $result_recent_registration->fetch_assoc();
    $message = "Anda baru saja mendaftar pada mata praktikum <a href=\"courses_detail.php?id={$notif['id']}\">" . htmlspecialchars($notif['nama_praktikum']) . "</a>.";
    $link = "courses_detail.php?id={$notif['id']}";
    $recent_notifications[] = ['message' => $message, 'link' => $link, 'type' => 'registration'];
}
$stmt_recent_registration->close();


// Tambahkan notifikasi default jika tidak ada notifikasi yang ditemukan
if (empty($recent_notifications)) {
    $recent_notifications[] = ['message' => 'Selamat datang di dashboard SIMPRAK! Tidak ada notifikasi terbaru saat ini.', 'link' => '#', 'type' => 'info'];
}
// --- Akhir Logic Notifikasi Aktual ---
?>

<div class="bg-white text-darkblue-800 p-8 rounded-xl shadow-lg mb-8 border-t-4 border-pastelblue-600"> <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?php echo $display_name; ?>!</h1>
    <p class="mt-2 text-subtletext-600 opacity-90">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-pastelblue-600"><?php echo $total_praktikum_diikuti; ?></div> <div class="mt-2 text-darkblue-700">Praktikum Diikuti</div> </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-green-500"><?php echo $total_tugas_selesai; ?></div>
        <div class="mt-2 text-darkblue-700">Tugas Selesai</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-orange-500"><?php echo $total_tugas_menunggu; ?></div>
        <div class="mt-2 text-darkblue-700">Tugas Menunggu</div>
    </div>
    
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-darkblue-800 mb-4">Notifikasi Terbaru</h3>
    <ul class="space-y-4">
        <?php if (!empty($recent_notifications)): ?>
            <?php foreach ($recent_notifications as $notif): 
                $icon_html = '';
                // Pilih ikon berdasarkan 'type' dari notifikasi
                switch ($notif['type']) {
                    case 'grade': $icon_html = '<span class="text-xl mr-4 text-pastelblue-500">🔔</span>'; break; // Warna disesuaikan
                    case 'deadline': $icon_html = '<span class="text-xl mr-4 text-orange-500">⏳</span>'; break; 
                    case 'success': $icon_html = '<span class="text-xl mr-4 text-green-500">✅</span>'; break;
                    case 'registration': $icon_html = '<span class="text-xl mr-4 text-pastelblue-500">✅</span>'; break; // Warna disesuaikan
                    case 'upload': $icon_html = '<span class="text-xl mr-4 text-pastelblue-500">⬆️</span>'; break; // Warna disesuaikan
                    case 'info': default: $icon_html = '<span class="text-xl mr-4 text-subtletext-500">ℹ️</span>'; break;
                }
            ?>
                <li class="flex items-start p-3 border-b border-pastelblue-100 last:border-b-0"> <?php echo $icon_html; ?>
                    <div>
                        <?php echo $notif['message']; ?> 
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="text-subtletext-500 italic text-center py-4">Tidak ada notifikasi terbaru.</li>
        <?php endif; ?>
    </ul>
</div>

<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>