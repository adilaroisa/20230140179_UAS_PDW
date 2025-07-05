<?php
require_once '../includes/config.php';

$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';

$id_mahasiswa = $_SESSION['user_id'];

// Ambil data praktikum yang diikuti mahasiswa
$query = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi 
          FROM mata_praktikum mp
          JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
          WHERE pp.mahasiswa_id = ?"; //
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();

$myCourses = []; 
if ($result) { 
    while ($row = $result->fetch_assoc()) {
        $myCourses[] = $row;
    }
}

// Notifikasi Pendaftaran (jika ada redirect dari pendaftaran.php)
$registration_status_message = '';
if (isset($_GET['status']) && $_GET['status'] == 'sukses') {
    $registration_status_message = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert"><p><strong>Berhasil!</strong> Anda telah mendaftar ke praktikum baru.</p></div>';
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold text-darkblue-800 mb-6">Praktikum yang Saya Ikuti</h2>
        
        <?php echo $registration_status_message; ?>

        <?php if (empty($myCourses)): ?>
            <div class="bg-pastelblue-100 border-l-4 border-pastelblue-500 text-subtletext-700 p-4 mb-6 rounded-lg"> <p>Anda belum terdaftar di praktikum manapun. 
                   <a href="courses.php" class="text-pastelblue-600 hover:underline font-semibold">Daftar sekarang!</a> </p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($myCourses as $course): ?>
                    <div class="border border-pastelblue-200 rounded-2xl p-6 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow"> <div>
                            <h3 class="text-lg font-bold text-darkblue-700">
                                <a href="courses_detail.php?id=<?= htmlspecialchars($course['id']) ?>" class="text-darkblue-800 hover:text-pastelblue-600"> <?= htmlspecialchars($course['nama_praktikum'] ?? '') ?> 
                                </a>
                            </h3>
                            <p class="text-subtletext-600 mt-2 text-sm"><?= htmlspecialchars($course['deskripsi'] ?? '') ?></p>
                        </div>
                        
                        <div class="mt-4">
                            <a href="courses_detail.php?id=<?= htmlspecialchars($course['id']) ?>" class="btn-primary-gradient text-white font-bold py-2 px-4 rounded-lg text-center transition block w-full">
                                Lihat Detail & Tugas
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php'; 
?>