<?php
// Aktifkan pelaporan error secara eksplisit untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Kelola Mata Praktikum';
$activePage = 'praktikum';
require_once 'templates/header.php'; // Memanggil header asisten
require_once '../includes/config.php'; // Memanggil config.php

// Simpan / Update Praktikum
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_praktikum'])) {
    $nama = $_POST['nama_praktikum'];
    $deskripsi = $_POST['deskripsi'];
    $id = $_POST['id_praktikum'];

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO mata_praktikum (nama_praktikum, deskripsi) VALUES (?, ?)"); //
        $stmt->bind_param("ss", $nama, $deskripsi);
    } else {
        $stmt = $conn->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?"); //
        $stmt->bind_param("ssi", $nama, $deskripsi, $id);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: management_praktikum.php");
    exit();
}

// Hapus Praktikum
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ?"); //
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: management_praktikum.php");
    exit();
}

// Ambil Data Praktikum untuk Edit
$praktikum_edit = ['id' => '', 'nama_praktikum' => '', 'deskripsi' => ''];
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ?"); //
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $praktikum_edit = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="bg-white p-8 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6 text-pastelblue-700"><?php echo empty($praktikum_edit['id']) ? 'Tambah' : 'Edit'; ?> Praktikum</h2>
        <form action="management_praktikum.php" method="POST" class="space-y-4">
            <input type="hidden" name="id_praktikum" value="<?php echo htmlspecialchars($praktikum_edit['id']); ?>">
            <div>
                <label for="nama_praktikum" class="block font-semibold text-subtletext-700 mb-1">Nama Praktikum</label>
                <input type="text" name="nama_praktikum" id="nama_praktikum" value="<?php echo htmlspecialchars($praktikum_edit['nama_praktikum']); ?>" class="w-full px-4 py-3 border border-subtletext-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pastelblue-500 text-darkblue-800" required>
            </div>
            <div>
                <label for="deskripsi" class="block font-semibold text-subtletext-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="4" class="w-full px-4 py-3 border border-subtletext-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pastelblue-500 text-darkblue-800" required><?php echo htmlspecialchars($praktikum_edit['deskripsi']); ?></textarea>
            </div>
            <button type="submit" name="submit_praktikum" class="w-full bg-pastelblue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-pastelblue-700 transition">
                Simpan Praktikum
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white p-8 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6 text-pastelblue-700">Daftar Mata Praktikum</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b-2 border-pastelblue-200 bg-pastelblue-100">
                    <tr>
                        <th class="py-3 px-4 text-left uppercase font-semibold text-sm text-pastelblue-700">Nama Praktikum</th>
                        <th class="py-3 px-4 text-left uppercase font-semibold text-sm text-pastelblue-700">Deskripsi</th>
                        <th class="py-3 px-4 text-left uppercase font-semibold text-sm text-pastelblue-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-darkblue-800">
                    <?php
                    // PERBAIKAN: Hapus ORDER BY created_at DESC karena kolom itu tidak ada di tabel mata_praktikum
                    $result = $conn->query("SELECT * FROM mata_praktikum ORDER BY nama_praktikum ASC"); // Diurutkan berdasarkan nama_praktikum
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b border-subtletext-100 hover:bg-pastelblue-50 transition">
                                <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                                <td class="py-4 px-4 text-subtletext-600"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                <td class="py-4 px-4 flex items-center space-x-2">
                                    <a href="management_praktikum.php?edit=<?php echo $row['id']; ?>" class="bg-pastelpink-400 text-white py-1 px-3 rounded-lg text-xs hover:bg-pastelpink-500">Edit</a>
                                    <a href="management_praktikum.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')" class="bg-red-500 text-white py-1 px-3 rounded-lg text-xs hover:bg-red-600">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr><td colspan="3" class="text-center py-6 text-subtletext-500">Belum ada data praktikum.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>