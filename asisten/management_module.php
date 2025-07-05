<?php
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_modul'])) {
    // Your existing form handling code here
}

// Handle delete
if (isset($_GET['hapus'])) {
    // Your existing delete handling code here
}

// Get all modules
$query = "SELECT m.id, m.nama_modul, m.file_materi, p.nama_praktikum 
          FROM modul m 
          JOIN mata_praktikum p ON m.praktikum_id = p.id 
          ORDER BY m.nama_modul ASC";
$result = $conn->query($query);
$modules = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manajemen Modul';
$activePage = 'modul';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asisten - <?php echo $pageTitle; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pastelblue: {
                            50: '#E0F7FA',
                            100: '#B2EBF2',
                            200: '#80DEEA',
                            300: '#4DD0E1',
                            400: '#26C6DA',
                            500: '#00BCD4',
                            600: '#00ACC1',
                            700: '#0097A7',
                            800: '#00838F',
                            900: '#006064',
                        },
                        pastelpink: {
                            50: '#FCE4EC',
                            100: '#F8BBD0',
                            200: '#F48FB1',
                            300: '#F06292',
                            400: '#EC407A',
                            500: '#D81B60',
                            600: '#C2185B',
                            700: '#AD1457',
                            800: '#880E4F',
                            900: '#640D3D',
                        },
                        darkblue: {
                            DEFAULT: '#1a202c',
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        },
                        subtletext: {
                            DEFAULT: '#6b7280',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-pastelblue-50 to-pastelpink-50 font-sans">

<div class="flex h-screen">
    <aside class="w-64 bg-[#1a4b7a] text-white flex flex-col shadow-lg">
        <div class="p-6 text-center border-b border-[#0d3a66] bg-[#0d3a66]">
            <h3 class="text-xl font-bold">Asisten</h3>
            <p class="text-sm text-blue-100 mt-1"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
        </div>
        <nav class="flex-grow bg-[#1a4b7a]">
            <ul class="space-y-2 p-4">
                <li>
                    <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-md transition-colors <?php echo ($activePage == 'dashboard') ? 'bg-pastelblue-700 text-white' : 'text-gray-300 hover:bg-pastelblue-800 hover:text-white'; ?>">
                        <i class="fas fa-th-large mr-3 w-5 h-5"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="management_module.php" class="flex items-center px-4 py-3 rounded-md transition-colors <?php echo ($activePage == 'modul') ? 'bg-pastelblue-700 text-white' : 'text-gray-300 hover:bg-pastelblue-800 hover:text-white'; ?>">
                        <i class="fas fa-book-open mr-3 w-5 h-5"></i>
                        <span>Manajemen Modul</span>
                    </a>
                </li>
                <li>
                    <a href="laporan.php" class="flex items-center px-4 py-3 rounded-md transition-colors <?php echo ($activePage == 'laporan') ? 'bg-pastelblue-700 text-white' : 'text-gray-300 hover:bg-pastelblue-800 hover:text-white'; ?>">
                        <i class="fas fa-file-alt mr-3 w-5 h-5"></i>
                        <span>Laporan Masuk</span>
                    </a>
                </li>
                <li>
                    <a href="management_praktikum.php" class="flex items-center px-4 py-3 rounded-md transition-colors <?php echo ($activePage == 'praktikum') ? 'bg-pastelblue-700 text-white' : 'text-gray-300 hover:bg-pastelblue-800 hover:text-white'; ?>">
                        <i class="fas fa-flask mr-3 w-5 h-5"></i>
                        <span>Kelola Praktikum</span>
                    </a>
                </li>
                <li>
                    <a href="management_account.php" class="flex items-center px-4 py-3 rounded-md transition-colors <?php echo ($activePage == 'akun') ? 'bg-pastelblue-700 text-white' : 'text-gray-300 hover:bg-pastelblue-800 hover:text-white'; ?>">
                        <i class="fas fa-users-cog mr-3 w-5 h-5"></i>
                        <span>Kelola Akun</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="flex-1 p-6 lg:p-10 overflow-auto">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-darkblue-800"><?php echo $pageTitle; ?></h1>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                Logout
            </a>
        </header>

        <!-- Module Management Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <!-- Full-width header -->
            <div class="bg-pastelblue-600 px-8 py-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-white">Daftar Modul Praktikum</h2>
                    <a href="management_module.php?tambah=1" class="bg-white text-pastelblue-600 hover:bg-pastelblue-50 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Tambah Modul
                    </a>
                </div>
            </div>
            
            <!-- Table Section -->
            <div class="p-8">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-pastelblue-50">
                            <tr>
                                <th class="py-3 px-4 text-left font-semibold text-pastelblue-800 border-b border-pastelblue-200">NAMA MODUL</th>
                                <th class="py-3 px-4 text-left font-semibold text-pastelblue-800 border-b border-pastelblue-200">PRAKTIKUM</th>
                                <th class="py-3 px-4 text-left font-semibold text-pastelblue-800 border-b border-pastelblue-200">FILE</th>
                                <th class="py-3 px-4 text-left font-semibold text-pastelblue-800 border-b border-pastelblue-200">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-pastelblue-100">
                            <?php foreach($modules as $modul): ?>
                            <tr class="hover:bg-pastelblue-50">
                                <td class="py-4 px-4 text-darkblue-800 font-medium"><?php echo htmlspecialchars($modul['nama_modul']); ?></td>
                                <td class="py-4 px-4 text-subtletext-600"><?php echo htmlspecialchars($modul['nama_praktikum']); ?></td>
                                <td class="py-4 px-4">
                                    <?php if(!empty($modul['file_materi'])): ?>
                                        <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" class="text-pastelblue-600 hover:underline" target="_blank">Lihat File</a>
                                    <?php else: ?>
                                        <span class="text-subtletext-400 italic">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex space-x-2">
                                        <a href="management_module.php?edit=<?php echo $modul['id']; ?>" class="bg-pastelblue-100 text-pastelblue-700 hover:bg-pastelblue-200 px-3 py-1 rounded-md text-sm">
                                            Edit
                                        </a>
                                        <a href="management_module.php?hapus=<?php echo $modul['id']; ?>" onclick="return confirm('Yakin ingin menghapus modul ini?')" class="bg-pastelpink-100 text-pastelpink-700 hover:bg-pastelpink-200 px-3 py-1 rounded-md text-sm">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add/Edit Form (shown when ?tambah or ?edit parameter exists) -->
<?php if(isset($_GET['tambah']) || isset($_GET['edit'])): ?>
<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-pastelblue-800"><?php echo isset($_GET['edit']) ? 'Edit Modul' : 'Tambah Modul Baru'; ?></h2>
    
    <form action="management_module.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id_modul" value="<?php echo isset($edit_modul) ? $edit_modul['id'] : ''; ?>">
        
        <div>
            <label class="block font-semibold text-subtletext-700 mb-1">Mata Praktikum</label>
            <select name="praktikum_id" required class="w-full px-4 py-2 border border-subtletext-300 rounded-lg focus:ring-pastelblue-500 focus:outline-none text-darkblue-800">
                <option value="">-- Pilih Praktikum --</option>
                <?php
                $praktikum_list = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum");
                while ($p = $praktikum_list->fetch_assoc()):
                ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo (isset($edit_modul) && $edit_modul['praktikum_id'] == $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
                
                <div>
                    <label class="block font-semibold text-subtletext-700 mb-1">Nama Modul</label>
                    <input type="text" name="nama_modul" required value="<?php echo isset($edit_modul) ? htmlspecialchars($edit_modul['nama_modul']) : ''; ?>" class="w-full px-4 py-2 border border-subtletext-300 rounded-lg focus:ring-pastelblue-500 focus:outline-none text-darkblue-800">
                </div>
                
                <div>
                    <label class="block font-semibold text-subtletext-700 mb-1">File Materi</label>
                    <input type="file" name="file_materi" class="w-full px-4 py-2 border border-subtletext-300 rounded-lg focus:ring-pastelblue-500 focus:outline-none text-darkblue-800">
                </div>
                
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="management_module.php" class="bg-subtletext-300 text-darkblue-800 font-semibold py-2 px-6 rounded-lg hover:bg-subtletext-400 transition">
                        Batal
                    </a>
                    <button type="submit" name="submit_modul" class="bg-pastelblue-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-pastelblue-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>