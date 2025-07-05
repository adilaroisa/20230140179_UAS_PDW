<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Jika sudah login, redirect ke halaman yang sesuai
// Perbaiki: Tambahkan pengecekan isset($_SESSION['role'])
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) { // Perbaikan di sini
    if ($_SESSION['role'] == 'asisten') {
        header("Location: asisten/dashboard.php");
        exit(); // Penting: selalu panggil exit() setelah header redirect
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
        exit(); // Penting: selalu panggil exit() setelah header redirect
    }
    // Jika user_id ada tapi role tidak valid, biarkan halaman dimuat normal atau arahkan ke default
}


$message = ''; // Inisialisasi pesan untuk ditampilkan di halaman
$status_type = ''; // Untuk menentukan apakah pesan itu success atau error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi!";
        $status_type = 'error';
    } else {
        $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Password benar, simpan semua data penting ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                // Logika untuk mengarahkan pengguna berdasarkan peran (role)
                if ($user['role'] == 'asisten') {
                    header("Location: asisten/dashboard.php");
                    exit();
                } elseif ($user['role'] == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                    exit();
                } else {
                    // Fallback jika peran tidak dikenali
                    $message = "Peran pengguna tidak valid.";
                    $status_type = 'error';
                }
            } else {
                $message = "Password yang Anda masukkan salah.";
                $status_type = 'error';
            }
        } else {
            $message = "Akun dengan email tersebut tidak ditemukan.";
            $status_type = 'error';
        }
        $stmt->close();
    }
}

// Cek jika ada status registrasi dari register.php
if (isset($_GET['status']) && $_GET['status'] == 'registered') {
    $message = "Registrasi berhasil! Silakan login.";
    $status_type = 'success';
}

$conn->close(); // Tutup koneksi database setelah semua operasi selesai
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-auth font-sans">
    <div class="container mx-auto px-4 py-16 flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="text-center mb-10">
                <div class="mx-auto w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mb-4">
                    <i class="fas fa-sign-in-alt text-3xl text-secondary-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-primary-800">Masuk ke Akun Anda</h1>
                <p class="text-primary-600 mt-1">Selamat datang kembali di SIMPRAK</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-8 py-6">
                    <?php if (!empty($message)): ?>
                        <div class="mb-6 p-4 
                            <?php echo ($status_type == 'success' ? 'bg-green-50 border-l-4 border-green-500 text-green-700' : 'bg-red-50 border-l-4 border-red-500 text-red-700'); ?> 
                            rounded">
                            <i class="fas <?php echo ($status_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'); ?> mr-2"></i> 
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="post" class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-primary-700 mb-1">Email</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-primary-400"></i>
                                </div>
                                <input type="email" id="email" name="email" required 
                                        class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 pr-3 py-3 border border-primary-200 rounded-lg placeholder-primary-400"
                                        placeholder="email@example.com">
                            </div>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-primary-700 mb-1">Password</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-primary-400"></i>
                                </div>
                                <input type="password" id="password" name="password" required 
                                        class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 pr-3 py-3 border border-primary-200 rounded-lg placeholder-primary-400"
                                        placeholder="••••••••">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" 
                                    style="background: linear-gradient(to right, #2563eb, #1d4ed8);"
                                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-gradient-to-r hover:from-[#1d4ed8] hover:to-[#1e40af] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563eb] transition duration-150 ease-in-out">
                                <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="px-8 py-4 bg-primary-50 border-t border-primary-100">
                    <p class="text-xs text-center text-primary-700">
                        Belum punya akun? 
                        <a href="register.php" class="font-medium text-secondary-600 hover:text-secondary-700">
                            Daftar di sini
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="mt-8 text-center text-xs text-primary-500">
                © 2023 SIMPRAK. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>