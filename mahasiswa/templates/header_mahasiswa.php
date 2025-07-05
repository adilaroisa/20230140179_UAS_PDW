<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Hanya mahasiswa yang bisa mengakses
if ($_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../asisten/dashboard.php"); // Sesuaikan path ini jika dashboard asisten berbeda
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Biru Pastel (Dominan)
                        pastelblue: {
                            50: '#E0F7FA',   // Sangat terang, untuk latar belakang/border sangat ringan
                            100: '#B2EBF2',
                            200: '#80DEEA',  // Untuk border ringan, highlight
                            300: '#4DD0E1',
                            400: '#26C6DA',  // Biru pastel utama, untuk ikon/detail
                            500: '#00BCD4',  // Sedikit lebih gelap
                            600: '#00ACC1',  // Untuk tombol utama, hover
                            700: '#0097A7',  // Untuk teks heading, aktif
                            800: '#00838F',  // Untuk teks sangat gelap
                            900: '#006064',  // Untuk teks paling gelap
                        },
                        // Pink Pastel (Aksen)
                        pastelpink: {
                            50: '#FCE4EC',   // Sangat terang
                            100: '#F8BBD0',
                            200: '#F48FB1',  // Untuk aksen ringan
                            300: '#F06292',  // Pink pastel utama, untuk ikon/aksen
                            400: '#EC407A',
                            500: '#D81B60',
                            600: '#C2185B',  // Untuk tombol aksen, hover
                            700: '#AD1457',
                            800: '#880E4F',
                            900: '#640D3D',
                        },
                        // Warna Lain (Untuk Kontras dan Teks)
                        darkblue: { // Tetap gunakan untuk teks heading utama yang harus terbaca jelas
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
                        subtletext: { // Untuk teks sekunder/placeholder yang lembut
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
                        // Warna hijau dan oranye dari Tailwind default (misal: green-500, orange-500)
                        // akan tetap berfungsi jika tidak didefinisikan ulang di sini.
                    }
                }
            }
        }
    </script>
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>
<body class="bg-gradient-to-br from-pastelblue-50 to-pastelpink-50 font-sans">

    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <div class="flex-shrink-0">
                    <a href="dashboard.php" class="text-darkblue-900 text-2xl font-bold flex items-center">
                        <i class="fas fa-cube text-pastelblue-600 mr-2"></i> <span class="hidden sm:block">SIMPRAK</span>
                    </a>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <?php 
                            // Biru pastel untuk navigasi aktif dan hover
                            $activeClass = 'bg-pastelblue-600 text-white px-3 py-2 rounded-md text-sm font-medium';
                            $inactiveClass = 'text-subtletext-700 hover:bg-pastelblue-100 hover:text-pastelblue-700 px-3 py-2 rounded-md text-sm font-medium';
                        ?>
                        <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?>">Dashboard</a>
                        <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? $activeClass : $inactiveClass; ?>">Praktikum Saya</a>
                        <a href="courses.php" class="<?php echo ($activePage == 'courses') ? $activeClass : $inactiveClass; ?>">Cari Praktikum</a>
                    </div>
                </div>

                <div class="hidden md:block">
                    <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition-colors duration-300">
                        Logout
                    </a>
                </div>

                <div class="-mr-2 flex md:hidden">
                    <button type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-subtletext-500 hover:text-subtletext-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pastelblue-500" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'bg-pastelblue-600 text-white' : 'text-subtletext-700 hover:bg-pastelblue-100 hover:text-pastelblue-700'; ?> block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? 'bg-pastelblue-600 text-white' : 'text-subtletext-700 hover:bg-pastelblue-100 hover:text-pastelblue-700'; ?> block px-3 py-2 rounded-md text-base font-medium">Praktikum Saya</a>
                <a href="courses.php" class="<?php echo ($activePage == 'courses') ? 'bg-pastelblue-600 text-white' : 'text-subtletext-700 hover:bg-pastelblue-100 hover:text-pastelblue-700'; ?> block px-3 py-2 rounded-md text-base font-medium">Cari Praktikum</a>
                <a href="../logout.php" class="block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md text-base text-center transition-colors duration-300 mt-2 mx-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 lg:p-8">