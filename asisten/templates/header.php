<?php
error_reporting(E_ALL);           // Aktifkan pelaporan error secara eksplisit untuk debugging
ini_set('display_errors', 1);       // Aktifkan pelaporan error secara eksplisit untuk debugging

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asisten - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    
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
                    }
                }
            }
        }
    </script>
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
</head>
<body class="bg-gradient-to-br from-pastelblue-50 to-pastelpink-50 font-sans">

<div class="flex h-screen">
    <!-- Refined blue sidebar with better color harmony -->
    <aside class="w-64 bg-[#1a4b7a] text-white flex flex-col shadow-lg">
        <div class="p-6 text-center border-b border-[#0d3a66] bg-[#0d3a66]">
            <h3 class="text-xl font-bold">Asisten</h3>
            <p class="text-sm text-blue-100 mt-1"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
        </div>
        <nav class="flex-grow bg-[#1a4b7a]">
            <ul class="space-y-2 p-4">
                <?php 
                    $activeClass = 'bg-pastelblue-700 text-white';
                    $inactiveClass = 'text-subtletext-300 hover:bg-pastelblue-800 hover:text-white';
                ?>
                <li>
                    <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="management_module.php" class="<?php echo ($activePage == 'modul') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                        <span>Manajemen Modul</span>
                    </a>
                </li>
                <li>
                    <a href="laporan.php" class="<?php echo ($activePage == 'laporan') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75c0-.231-.035-.454-.1-.664M6.75 7.5h1.5M6.75 12h1.5m6.75 0h1.5m-1.5 3h1.5m-1.5 3h1.5M4.5 6.75h1.5v1.5H4.5v-1.5zM4.5 12h1.5v1.5H4.5v-1.5zM4.5 17.25h1.5v1.5H4.5v-1.5z" /></svg>
                        <span>Laporan Masuk</span>
                    </a>
                </li>
                <li>
                    <a href="management_praktikum.php" class="<?php echo ($activePage == 'praktikum') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md transition-colors duration-200">
                        <i class="fas fa-flask mr-3 w-5 h-5"></i>
                        <span>Kelola Praktikum</span>
                    </a>
                </li>
                 <li>
                    <a href="management_account.php" class="<?php echo ($activePage == 'akun') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md transition-colors duration-200">
                        <i class="fas fa-users-cog mr-3 w-5 h-5"></i>
                        <span>Kelola Akun</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="flex-1 p-6 lg:p-10">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-darkblue-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                Logout
            </a>
        </header>