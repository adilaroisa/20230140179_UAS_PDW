<?php
// Aktifkan pelaporan error secara eksplisit untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Kelola Modul Praktikum';
$activePage = 'modul';
require_once 'templates/header.php'; // Memanggil header asisten
require_once '../includes/config.php'; // Memanggil config.php

$upload_message = '';
$upload_message_type = '';
$edit_mode = false;
$show_form = isset($_GET['tambah']) || isset($_GET['edit']); // Menentukan apakah form harus ditampilkan

// === TAMBAH / UPDATE MODUL ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modul'])) {
    $id_modul = $_POST['id_modul'];
    $id_praktikum = $_POST['id_praktikum'];
    $nama_modul = $_POST['nama_modul'];
    $deskripsi = $_POST['deskripsi'];

    $file_materi_path = '';

    $target_dir = "../uploads/materi/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0775, true);

    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
        $file_materi_path = time() . '_' . basename($_FILES["file_materi"]["name"]);
        $file_path = $target_dir . $file_materi_path;

        if (!move_uploaded_file($_FILES["file_materi"]["tmp_name"], $file_path)) {
            $upload_message = "Gagal upload file.";
            $upload_message_type = 'error';
        }
    }

    if (empty($id_modul)) {
        $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, nama_modul, file_materi) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_praktikum, $nama_modul, $file_materi_path);
    } else {
        if ($file_materi_path != '') {
            $stmt = $conn->prepare("UPDATE modul SET praktikum_id=?, nama_modul=?, file_materi=? WHERE id=?");
            $stmt->bind_param("issi", $id_praktikum, $nama_modul, $file_materi_path, $id_modul);
        } else {
            $stmt = $conn->prepare("UPDATE modul SET praktikum_id=?, nama_modul=? WHERE id=?");
            $stmt->bind_param("isi", $id_praktikum, $nama_modul, $id_modul);
        }
    }

    if ($stmt->execute()) {
        $upload_message = "Modul berhasil disimpan.";
        $upload_message_type = 'success';
        $show_form = false; // Sembunyikan form setelah berhasil submit
    } else {
        $upload_message = "Gagal menyimpan modul. Error: " . $conn->error;
        $upload_message_type = 'error';
        $show_form = true; // Tetap tampilkan form jika ada error
    }

    $stmt->close();
}

// === HAPUS MODUL ===
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: management_module.php");
    exit();
}

// === FORM EDIT ===
$modul_edit = ['id' => '', 'praktikum_id' => '', 'nama_modul' => '', 'file_materi' => '', 'deskripsi' => ''];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $show_form = true;
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $modul_edit = $result->fetch_assoc();
    $stmt->close();
}
?>

<div class="bg-white p-8 rounded-2xl shadow-lg mb-10">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-pastelblue-800">Daftar Modul Praktikum</h2>
        <?php if (!$show_form): ?>
            <a href="management_module.php?tambah=1" class="bg-pastelblue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-pastelblue-700 transition">
                + Tambahkan Modul
            </a>
        <?php endif; ?>
    </div>

    <?php if ($show_form): ?>
        <div class="mb-10">
            <h3 class="text-xl font-bold mb-4 text-pastelblue-800"><?php echo $edit_mode ? 'Edit' : 'Tambah'; ?> Modul Praktikum</h3>

            <?php if (!empty($upload_message)): ?>
                <div class="p-4 mb-6 rounded-lg <?php echo ($upload_message_type == 'success') ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 'bg-pastelpink-100 border-l-4 border-pastelpink-500 text-pastelpink-700'; ?>">
                    <?php echo $upload_message; ?>
                </div>
            <?php endif; ?>

            <form action="management_module.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="id_modul" value="<?php echo htmlspecialchars($modul_edit['id']); ?>">

                <div>
                    <label class="block font-semibold text-subtletext-700 mb-1">Mata Praktikum</label>
                    <select name="id_praktikum" required class="w-full px-4 py-3 border border-subtletext-300 rounded-lg focus:ring-pastelblue-500 focus:outline-none text-darkblue-800">
                        <option value="">-- Pilih Praktikum --</option>
                        <?php
                        $praktikum_list = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum");
                        while ($p = $praktikum_list->fetch_assoc()) {
                            $selected = ($modul_edit['praktikum_id'] == $p['id']) ? 'selected' : '';
                            echo "<option value='{$p['id']}' $selected>" . htmlspecialchars($p['nama_praktikum']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-subtletext-700 mb-1">Nama Modul</label>
                    <input type="text" name="nama_modul" required value="<?php echo htmlspecialchars($modul_edit['nama_modul']); ?>"
                           class="w-full px-4 py-3 border border-subtletext-300 rounded-lg focus:ring-pastelblue-500 focus:outline-none text-darkblue-800">
                </div>

                <div>
                    <label class="block font-semibold text-subtletext-700 mb-1">Upload File Materi</label>
                    <?php if (!empty($modul_edit['file_materi'])): ?>
                        <p class="text-sm text-subtletext-600 mb-1">File saat ini: <a class="text-pastelblue-600 underline" href="../uploads/materi/<?php echo htmlspecialchars($modul_edit['file_materi']); ?>" target="_blank"><?php echo htmlspecialchars($modul_edit['file_materi']); ?></a></p>
                    <?php endif; ?>
                    <input type="file" name="file_materi"
                           class="w-full text-sm text-subtletext-500 file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0 file:text-sm file:font-semibold
                                  file:bg-pastelblue-50 file:text-pastelblue-700 hover:file:bg-pastelblue-100">
                </div>

                <div>
                    <label class="block font-semibold text-subtletext-700 mb-1">Deskripsi Singkat</label>
                    <textarea name="deskripsi" rows="4"
                              class="w-full px-4 py-3 border border-subtletext-300 rounded-lg focus:ring-pastelblue-500 focus:outline-none text-darkblue-800"><?php echo htmlspecialchars($modul_edit['deskripsi'] ?? ''); ?></textarea>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" name="submit_modul"
                            class="bg-pastelblue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-pastelblue-700 transition">
                        <?php echo $edit_mode ? 'Simpan Perubahan' : 'Tambah Modul'; ?>
                    </button>
                    <a href="management_module.php" class="bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-400 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full text-subtletext-700 border border-pastelblue-200 rounded-lg overflow-hidden">
            <thead class="bg-pastelblue-100">
                <tr>
                    <th class="py-3 px-5 text-left text-sm font-semibold uppercase">Nama Modul</th>
                    <th class="py-3 px-5 text-left text-sm font-semibold uppercase">Praktikum</th>
                    <th class="py-3 px-5 text-left text-sm font-semibold uppercase">File</th>
                    <th class="py-3 px-5 text-left text-sm font-semibold uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT m.id, m.nama_modul, m.file_materi, p.nama_praktikum 
                                        FROM modul m 
                                        JOIN mata_praktikum p ON m.praktikum_id = p.id 
                                        ORDER BY m.created_at DESC");
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                    <tr class="border-t border-subtletext-100 hover:bg-pastelblue-50 transition">
                        <td class="py-4 px-5 font-medium text-darkblue-800"><?php echo htmlspecialchars($row['nama_modul']); ?></td>
                        <td class="py-4 px-5 text-subtletext-600"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                        <td class="py-4 px-5">
                            <?php if (!empty($row['file_materi'])): ?>
                                <a href="../uploads/materi/<?php echo htmlspecialchars($row['file_materi']); ?>" class="text-pastelblue-600 underline text-sm" target="_blank">Lihat File</a>
                            <?php else: ?>
                                <span class="text-subtletext-400 italic text-sm">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-5 space-x-2">
                            <a href="management_module.php?edit=<?php echo $row['id']; ?>" class="bg-pastelpink-400 text-white py-1 px-3 rounded-lg text-sm hover:bg-pastelpink-500">Edit</a>
                            <a href="management_module.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Hapus modul ini?')" class="bg-red-500 text-white py-1 px-3 rounded-lg text-sm hover:bg-red-600">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4" class="text-center py-6 text-subtletext-500">Belum ada modul.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>