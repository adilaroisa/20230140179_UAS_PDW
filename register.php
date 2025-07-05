<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth_functions.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SIMPRAK</title>
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
                            400: '#38bdf8', /* Warna ikon yang Anda inginkan */
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
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-auth font-sans">
    <div class="container mx-auto px-4 py-16 flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="text-center mb-10">
                <div class="mx-auto w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center mb-4">
                    <i class="fas fa-user-plus text-3xl text-secondary-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-primary-800">Buat Akun Baru</h1>
                <p class="text-primary-600 mt-1">Mulai menggunakan SIMPRAK</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-8 py-6">
                    <?php if (!empty($message)): ?>
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                            <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="post" class="space-y-5">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-primary-700 mb-1">Nama Lengkap</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-primary-400"></i> </div>
                                <input type="text" id="nama" name="nama" required 
                                        class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 pr-3 py-3 border border-primary-200 rounded-lg placeholder-primary-400"
                                        placeholder="Nama lengkap">
                            </div>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-primary-700 mb-1">Email</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-primary-400"></i> </div>
                                <input type="email" id="email" name="email" required 
                                        class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 pr-3 py-3 border border-primary-200 rounded-lg placeholder-primary-400"
                                        placeholder="email@example.com">
                            </div>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-primary-700 mb-1">Password</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-primary-400"></i> </div>
                                <input type="password" id="password" name="password" required 
                                        class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 pr-3 py-3 border border-primary-200 rounded-lg placeholder-primary-400"
                                        placeholder="••••••••">
                            </div>
                        </div>
                        
                        <div>
                            <label for="role" class="block text-sm font-medium text-primary-700 mb-1">Daftar Sebagai</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-primary-400"></i> </div>
                                <select id="role" name="role" required 
                                        class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 pr-3 py-3 border border-primary-200 rounded-lg appearance-none bg-white text-primary-700">
                                    <option value="mahasiswa">Mahasiswa</option>
                                    <option value="asisten">Asisten Praktikum</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-primary-400"></i> </div>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="terms" name="terms" type="checkbox" required
                                        class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-primary-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="terms" class="font-medium text-primary-700">
                                    Saya menyetujui <a href="#" class="text-secondary-600 hover:text-secondary-700">Syarat dan Ketentuan</a>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" 
                                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white btn-primary-gradient hover:btn-primary-gradient focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2563eb] transition duration-150 ease-in-out">
                                <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="px-8 py-4 bg-primary-50 border-t border-primary-100">
                    <p class="text-xs text-center text-primary-700">
                        Sudah punya akun? 
                        <a href="login.php" class="font-medium text-secondary-600 hover:text-secondary-700">
                            Masuk disini
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