<?php
// Aktifkan pelaporan error secara eksplisit untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/config.php';

// Hapus akun
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id != $_SESSION['user_id']) { // Tidak bisa menghapus akun sendiri
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?"); //
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: management_account.php");
    exit();
}

// Ambil data akun yang akan diedit
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt_edit = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?"); //
    $stmt_edit->bind_param("i", $edit_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    $edit_user = $result_edit->fetch_assoc();
    $stmt_edit->close();
}

// Proses update akun
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_akun'])) {
    $id = intval($_POST['id']);
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt_update = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?"); //
    $stmt_update->bind_param("sssi", $nama, $email, $role, $id);
    $stmt_update->execute();
    $stmt_update->close();

    header("Location: management_account.php");
    exit();
}

// Ambil semua akun
$result = $conn->query("SELECT id, nama, email, role, created_at FROM users ORDER BY role, created_at DESC"); //

$pageTitle = 'Kelola Akun Pengguna';
$activePage = 'akun';
require_once 'templates/header.php';
?>

<div class="bg-white p-8 rounded-2xl shadow-xl">
    <?php if (isset($edit_user)): ?>
        <div class="mb-8 p-6 bg-pastelblue-50 border border-pastelblue-200 rounded-lg shadow-sm">
            <h3 class="text-lg font-bold text-pastelblue-800 mb-4">Edit Akun: <?php echo htmlspecialchars($edit_user['nama']); ?></h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                <div>
                    <label class="block text-sm text-subtletext-700 mb-1">Nama</label>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($edit_user['nama']); ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pastelblue-400 border-subtletext-300 text-darkblue-800">
                </div>
                <div>
                    <label class="block text-sm text-subtletext-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pastelblue-400 border-subtletext-300 text-darkblue-800">
                </div>
                <div>
                    <label class="block text-sm text-subtletext-700 mb-1">Role</label>
                    <select name="role" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pastelblue-400 border-subtletext-300 text-darkblue-800">
                        <option value="asisten" <?php if ($edit_user['role'] == 'asisten') echo 'selected'; ?>>Asisten</option>
                        <option value="mahasiswa" <?php if ($edit_user['role'] == 'mahasiswa') echo 'selected'; ?>>Mahasiswa</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <a href="management_account.php" class="bg-subtletext-300 px-4 py-2 rounded-lg hover:bg-subtletext-400 text-sm font-medium text-darkblue-800">Batal</a>
                    <button type="submit" name="update_akun" class="bg-pastelblue-600 text-white px-4 py-2 rounded-lg hover:bg-pastelblue-700 text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <h2 class="text-3xl font-bold text-pastelblue-700">Kelola Akun Pengguna</h2>
        <a href="../register.php" target="_blank" class="flex items-center gap-2 bg-pastelblue-600 text-white font-semibold py-2 px-5 rounded-lg hover:bg-pastelblue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
            Tambah Akun Baru
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-pastelblue-200 rounded-lg overflow-hidden">
            <thead class="bg-pastelblue-100 text-pastelblue-700">
                <tr>
                    <th class="py-3 px-5 text-left font-medium uppercase text-sm">Nama</th>
                    <th class="py-3 px-5 text-left font-medium uppercase text-sm">Email</th>
                    <th class="py-3 px-5 text-left font-medium uppercase text-sm">Peran</th>
                    <th class="py-3 px-5 text-left font-medium uppercase text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-pastelblue-100">
                <?php if ($result->num_rows > 0):
                    while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-pastelblue-50 transition">
                        <td class="py-4 px-5 font-semibold text-darkblue-800"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="py-4 px-5 text-subtletext-600"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="py-4 px-5">
                            <?php if($row['role'] == 'asisten'): ?>
                                <span class="inline-block bg-pastelblue-100 text-pastelblue-800 text-xs px-3 py-1 rounded-full font-medium">Asisten</span>
                            <?php else: ?>
                                <span class="inline-block bg-pastelpink-100 text-pastelpink-800 text-xs px-3 py-1 rounded-full font-medium">Mahasiswa</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-5 flex gap-2">
                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                <a href="management_account.php?edit=<?php echo $row['id']; ?>" class="bg-pastelpink-400 text-white px-3 py-1 rounded-lg text-xs hover:bg-pastelpink-500">Edit</a>
                                <a href="management_account.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus akun ini?')" class="bg-red-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-red-600">Hapus</a>
                            <?php else: ?>
                                <span class="text-subtletext-400 italic text-sm">Akun Anda</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-6 text-subtletext-500">Belum ada akun terdaftar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>