<?php
// Aktifkan pelaporan error secara eksplisit untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php'; // Memanggil header asisten
require_once '../includes/config.php'; // Memanggil config.php

$whereClauses = [];
$filter_modul = $_GET['filter_modul'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$queryParams = [];
$paramTypes = '';

if (!empty($filter_modul)) {
    $whereClauses[] = "m.id = ?";
    $queryParams[] = intval($filter_modul);
    $paramTypes .= 'i';
}
if ($filter_status === 'dinilai') {
    $whereClauses[] = "l.nilai IS NOT NULL";
} elseif ($filter_status === 'belum_dinilai') {
    $whereClauses[] = "l.nilai IS NULL";
}

// Mengubah nama kolom sesuai pengumpulantugas.sql
$sql = "SELECT l.id, l.tanggal_kumpul, l.nilai, u.nama as nama_mahasiswa, m.nama_modul, mp.nama_praktikum, l.file_laporan, l.feedback
        FROM laporan l
        JOIN users u ON l.mahasiswa_id = u.id
        JOIN modul m ON l.modul_id = m.id
        JOIN mata_praktikum mp ON m.praktikum_id = mp.id";
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}
$sql .= " ORDER BY l.tanggal_kumpul DESC";

$stmt = $conn->prepare($sql);
if (!empty($queryParams)) {
    $stmt->bind_param($paramTypes, ...$queryParams);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
        <div class="bg-pastelblue-100 border-l-4 border-pastelblue-500 text-pastelblue-700 p-4 mb-6 rounded-lg" role="alert">
            Nilai berhasil disimpan.
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'gagal'): ?>
        <div class="bg-pastelpink-100 border-l-4 border-pastelpink-500 text-pastelpink-700 p-4 mb-6 rounded-lg" role="alert">
            Gagal menyimpan nilai.
        </div>
    <?php endif; ?>

    <div class="flex flex-wrap gap-4 justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-darkblue-800">Filter Laporan</h2>
        <form action="laporan.php" method="GET" class="flex flex-wrap gap-4">
            <div>
                <select name="filter_modul" class="px-4 py-2 border border-subtletext-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-pastelblue-500">
                    <option value="">Semua Modul</option>
                    <?php
                    // Mengubah nama kolom/tabel sesuai pengumpulantugas.sql
                    $modul_list = $conn->query("SELECT id, nama_modul FROM modul ORDER BY nama_modul");
                    while ($modul = $modul_list->fetch_assoc()) {
                        $selected = ($filter_modul == $modul['id']) ? 'selected' : '';
                        echo "<option value='{$modul['id']}' {$selected}>".htmlspecialchars($modul['nama_modul'])."</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <select name="filter_status" class="px-4 py-2 border border-subtletext-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-pastelblue-500">
                    <option value="">Semua Status</option>
                    <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
                    <option value="belum_dinilai" <?php echo ($filter_status == 'belum_dinilai') ? 'selected' : ''; ?>>Belum Dinilai</option>
                </select>
            </div>
            <button type="submit" class="bg-pastelblue-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-pastelblue-700 transition">
                Filter
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="border-b-2 border-pastelblue-200">
                <tr>
                    <th class="py-3 px-4 text-left uppercase font-semibold text-sm text-subtletext-600">Mahasiswa</th>
                    <th class="py-3 px-4 text-left uppercase font-semibold text-sm text-subtletext-600">Modul</th>
                    <th class="py-3 px-4 text-left uppercase font-semibold text-sm text-subtletext-600">Tanggal Kumpul</th>
                    <th class="py-3 px-4 text-left uppercase font-semibold text-sm text-subtletext-600">Status</th>
                    <th class="py-3 px-4 text-left uppercase font-semibold text-sm text-subtletext-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-darkblue-700">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="border-b border-pastelblue-100 hover:bg-pastelblue-50">
                            <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                            <td class="py-4 px-4">
                                <p class="font-semibold"><?php echo htmlspecialchars($row['nama_modul']); ?></p>
                                <p class="text-xs text-subtletext-500"><?php echo htmlspecialchars($row['nama_praktikum']); ?></p>
                            </td>
                            <td class="py-4 px-4 text-sm text-subtletext-500">
                                <?php echo date('d M Y, H:i', strtotime($row['tanggal_kumpul'])); ?>
                            </td>
                            <td class="py-4 px-4">
                                <?php if (is_null($row['nilai'])): ?>
                                    <span class="bg-pastelpink-200 text-pastelpink-800 py-1 px-3 rounded-full text-xs font-semibold">Belum Dinilai</span>
                                <?php else: ?>
                                    <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs font-semibold">Dinilai (<?php echo $row['nilai']; ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4">
                                <form action="nilai.php" method="POST" class="flex items-center space-x-2">
                                    <input type="hidden" name="id_laporan" value="<?php echo $row['id']; ?>">
                                    <input type="number" name="nilai" value="<?php echo htmlspecialchars($row['nilai'] ?? ''); ?>" 
                                           min="0" max="100" required 
                                           class="w-20 px-2 py-1 border border-subtletext-300 rounded-md text-sm text-center focus:ring-2 focus:ring-pastelblue-500">
                                    <button type="submit" class="bg-pastelblue-600 text-white py-1 px-3 rounded-md text-xs font-semibold hover:bg-pastelblue-700">
                                        Simpan
                                    </button>
                                    <a href="../uploads/laporan/<?php echo $row['file_laporan']; ?>" target="_blank"
                                       class="bg-subtletext-200 text-subtletext-700 py-1 px-3 rounded-md text-xs font-semibold hover:bg-subtletext-300">
                                        Unduh
                                    </a>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-6 text-subtletext-500">Tidak ada laporan yang cocok dengan filter.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer.php';
?>