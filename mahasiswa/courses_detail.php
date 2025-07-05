<?php
// Pastikan session sudah dimulai (seharusnya sudah di handle oleh header_mahasiswa.php)
require_once '../includes/config.php';

// Cek jika pengguna belum login. Jika tidak, redirect ke login.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Detail Praktikum';
$activePage = 'my_courses'; // Set active page for header navigation

$praktikum_id_url = isset($_GET['id']) ? intval($_GET['id']) : 0; // ID Praktikum dari URL

$praktikum_data = null;
$modul_list = [];

if ($praktikum_id_url > 0) {
    // 1. Ambil data praktikum
    $stmt_praktikum = $conn->prepare("SELECT id AS praktikum_id, nama_praktikum, deskripsi
                                      FROM mata_praktikum
                                      WHERE id = ?"); //
    $stmt_praktikum->bind_param("i", $praktikum_id_url);
    $stmt_praktikum->execute();
    $result_praktikum = $stmt_praktikum->get_result();
    if ($result_praktikum->num_rows > 0) {
        $praktikum_data = $result_praktikum->fetch_assoc();
        $pageTitle = 'Praktikum: ' . htmlspecialchars($praktikum_data['nama_praktikum']);

        // 2. Ambil semua modul untuk praktikum ini
        $sql_modul = "SELECT id AS modul_id, nama_modul, file_materi FROM modul WHERE praktikum_id = ? ORDER BY modul_id ASC"; //
        $stmt_modul = $conn->prepare($sql_modul);
        $stmt_modul->bind_param("i", $praktikum_id_url);
        $stmt_modul->execute();
        $result_modul = $stmt_modul->get_result();

        if ($result_modul->num_rows > 0) {
            while ($modul = $result_modul->fetch_assoc()) {
                $modul_id = $modul['modul_id'];
                $modul['laporan_mahasiswa'] = null; // Inisialisasi

                // 3. Ambil laporan mahasiswa untuk modul ini (jika ada)
                $sql_laporan = "SELECT file_laporan, tanggal_kumpul, nilai, feedback, status
                                FROM laporan
                                WHERE modul_id = ? AND mahasiswa_id = ?"; //
                $stmt_laporan = $conn->prepare($sql_laporan);
                $stmt_laporan->bind_param("ii", $modul_id, $_SESSION['user_id']);
                $stmt_laporan->execute();
                $result_laporan = $stmt_laporan->get_result();
                if ($result_laporan->num_rows > 0) {
                    $modul['laporan_mahasiswa'] = $result_laporan->fetch_assoc();
                }
                $stmt_laporan->close();
                
                $modul_list[] = $modul;
            }
        }
        $stmt_modul->close();
    }
    $stmt_praktikum->close();
}

require_once 'templates/header_mahasiswa.php';

// Notifikasi Upload (for display after redirect from laporan.php)
$upload_status_message = '';
if (isset($_GET['upload_status'])) {
    if ($_GET['upload_status'] == 'sukses') {
        $upload_status_message = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg" role="alert"><p><strong>Berhasil!</strong> Laporan Anda berhasil diunggah.</p></div>';
    } elseif ($_GET['upload_status'] == 'gagal') {
        $msg = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Terjadi kesalahan saat mengunggah laporan. Pastikan file valid.';
        $upload_status_message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg" role="alert"><p><strong>Gagal!</strong> ' . $msg . '</p></div>';
    }
}
?>

<div class="max-w-7xl mx-auto py-8">
    <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
        <?php if ($praktikum_data): // Tampilkan konten hanya jika $praktikum_data valid ?>
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-darkblue-800"><?php echo htmlspecialchars($praktikum_data['nama_praktikum']); ?></h1>
                    <p class="text-subtletext-600 mt-1"><?php echo htmlspecialchars($praktikum_data['deskripsi']); ?></p>
                </div>
                <a href="my_courses.php" class="text-pastelblue-600 hover:text-pastelblue-800 font-semibold flex items-center"> <i class="fas fa-arrow-left mr-2"></i> Kembali ke Praktikum Saya
                </a>
            </div>

            <?php echo $upload_status_message; ?>

            <?php if (!empty($modul_list)): ?>
                <h4 class="text-xl font-semibold text-darkblue-700 mb-4 border-b pb-2 border-pastelblue-200">Daftar Modul & Tugas:</h4> <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($modul_list as $modul): ?>
                        <div class="bg-white border border-pastelblue-200 rounded-lg p-5 shadow-sm flex flex-col justify-between"> <div>
                                <h5 class="text-lg font-bold text-pastelblue-700 mb-2"><?php echo htmlspecialchars($modul['nama_modul']); ?></h5> <div class="mb-4">
                                    <h6 class="text-sm font-semibold text-darkblue-600 mb-2 flex items-center">
                                        <i class="fas fa-file-download text-pastelblue-500 mr-2"></i> Materi: </h6>
                                    <?php if ($modul['file_materi']): ?>
                                        <a href="../uploads/modul/<?php echo htmlspecialchars($modul['file_materi']); ?>" 
                                           target="_blank" class="text-pastelblue-600 hover:underline"> <i class="fas fa-file-alt mr-1"></i> Unduh File Materi
                                        </a>
                                    <?php else: ?>
                                        <p class="text-subtletext-500 text-xs italic">Materi belum tersedia.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-4">
                                    <h6 class="text-sm font-semibold text-darkblue-600 mb-2 flex items-center">
                                        <i class="fas fa-cloud-upload-alt text-pastelblue-500 mr-2"></i> Unggah Laporan: </h6>
                                    <form action="laporan.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_modul" value="<?php echo htmlspecialchars($modul['modul_id']); ?>">
                                        <input type="hidden" name="id_praktikum" value="<?php echo htmlspecialchars($praktikum_data['praktikum_id']); ?>"> 
                                        <input type="file" name="file_laporan" required
                                               class="block w-full text-xs text-subtletext-700
                                                      file:mr-4 file:py-1 file:px-2
                                                      file:rounded-full file:border-0
                                                      file:text-xs file:font-semibold
                                                      file:bg-pastelblue-50 file:text-pastelblue-700 hover:file:bg-pastelblue-100 cursor-pointer focus:outline-none focus:ring-1 focus:ring-pastelblue-500 focus:border-transparent"> <p class="mt-1 text-xs text-subtletext-500">Maks: 10MB (PDF, DOCX, ZIP, RAR)</p>
                                        <button type="submit" class="mt-2 w-full btn-primary-gradient text-white font-bold py-1.5 px-3 rounded-lg text-xs text-center transition">
                                            Upload Laporan
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div>
                                <h6 class="text-sm font-semibold text-darkblue-600 mb-2 flex items-center">
                                    <i class="fas fa-info-circle text-pastelblue-500 mr-2"></i> Status Laporan: </h6>
                                <?php if ($modul['laporan_mahasiswa']): ?>
                                    <div class="text-xs text-subtletext-700 space-y-0.5">
                                        <p>File: <a href="../uploads/laporan/<?php echo htmlspecialchars($modul['laporan_mahasiswa']['file_laporan']); ?>" target="_blank" class="text-pastelblue-600 hover:underline"><?php echo htmlspecialchars($modul['laporan_mahasiswa']['file_laporan']); ?></a></p> <p>Tanggal: <?php echo date('d M Y', strtotime($modul['laporan_mahasiswa']['tanggal_kumpul'])); ?></p>
                                        <p>
                                            Status: 
                                            <span class="<?php 
                                                if ($modul['laporan_mahasiswa']['status'] == 'Dinilai') echo 'text-green-600 font-semibold';
                                                else echo 'text-orange-500 font-semibold';
                                            ?>">
                                                <?php echo htmlspecialchars($modul['laporan_mahasiswa']['status']); ?>
                                            </span>
                                        </p>
                                        <?php if ($modul['laporan_mahasiswa']['status'] == 'Dinilai'): ?>
                                            <p>Nilai: <span class="text-pastelblue-600 font-bold"><?php echo htmlspecialchars($modul['laporan_mahasiswa']['nilai'] ?? '-'); ?></span></p> <p>Feedback: <?php echo htmlspecialchars($modul['laporan_mahasiswa']['feedback'] ?? 'Tidak ada feedback.'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-subtletext-500 text-xs italic">Anda belum mengumpulkan laporan.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-subtletext-500 italic">Belum ada modul yang tersedia untuk praktikum ini.</p>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-10">
                <h2 class="text-2xl font-bold text-darkblue-800 mb-3">Praktikum Tidak Ditemukan</h2>
                <p class="text-subtletext-600">ID praktikum yang Anda minta tidak valid atau tidak ada.</p>
                <a href="my_courses.php" class="mt-5 inline-block btn-primary-gradient text-white py-2 px-5 rounded-lg hover:opacity-90">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Praktikum Saya
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>