<?php
require_once '../includes/config.php';

$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
require_once 'templates/header_mahasiswa.php';

$id_mahasiswa = $_SESSION['user_id'];

// Mengambil semua mata praktikum dan mengecek apakah mahasiswa sudah terdaftar
$sql = "SELECT mp.*, pp.id AS id_pendaftaran 
        FROM mata_praktikum mp
        LEFT JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id AND pp.mahasiswa_id = ?
        ORDER BY mp.nama_praktikum ASC"; //
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();

$availableCourses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $availableCourses[] = $row;
    }
}
?>

    <h1 class="text-4xl font-bold text-darkblue-800 mb-6">Temukan Praktikum</h1>
    <p class="text-subtletext-600 mb-8">Pilihlah Sesuai dengan Minat Anda.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (!empty($availableCourses)): // Memeriksa jika ada praktikum yang tersedia
            foreach($availableCourses as $course): ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-darkblue-800 mb-2"><?php echo htmlspecialchars($course['nama_praktikum'] ?? ''); ?></h3>
                    <p class="text-subtletext-600 text-sm h-16"><?php echo htmlspecialchars($course['deskripsi'] ?? ''); ?></p>
                </div>
                <div class="px-6 pb-6">
                    <?php if (is_null($course['id_pendaftaran'])): ?>
                        <a href="pendaftaran.php?id=<?php echo htmlspecialchars($course['id']); ?>" class="block w-full text-center btn-primary-gradient text-white font-semibold py-2 rounded-lg transition-colors">
                            Daftar Praktikum
                        </a>
                    <?php else: ?>
                        <span class="block w-full text-center bg-lightblue-100 text-brandblue-700 font-semibold py-2 rounded-lg cursor-not-allowed flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Sudah Terdaftar
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach;
        else: ?>
            <p class="col-span-3 text-subtletext-500 bg-white p-8 rounded-2xl shadow-md text-center">
                Saat ini belum ada mata praktikum yang tersedia.
            </p>
        <?php endif; ?>
    </div>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>