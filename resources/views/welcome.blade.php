<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SIPRES - Sistem Informasi Presensi SMP Negeri 2 Mijen</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-blue-600 selection:text-white overflow-x-hidden min-h-screen flex flex-col justify-between">

    <!-- Preline Navbar -->
    <header class="sticky top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <!-- Brand -->
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-lg shadow-sm shadow-blue-500/20">
                    S2M
                </div>
                <div>
                    <span class="block text-base font-extrabold text-slate-900 tracking-tight leading-none">SIPRES</span>
                    <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mt-0.5">SMP Negeri 2 Mijen</span>
                </div>
            </a>

            <!-- Right Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    T.P 2025/2026 Aktif
                </span>

                @auth
                    <a href="{{ url('/dashboard') }}" class="py-2 px-4 inline-flex items-center gap-x-2 text-xs sm:text-sm font-semibold rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition focus:outline-hidden shadow-xs cursor-pointer">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Buka Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="py-2 px-4 inline-flex items-center gap-x-2 text-xs sm:text-sm font-semibold rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition focus:outline-hidden shadow-xs cursor-pointer">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk ke Sistem</span>
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="grow">
        <!-- Hero Section -->
        <section class="relative overflow-hidden py-10 sm:py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-x-2 py-1.5 px-3.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-6">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span>Sistem Informasi Presensi Berbasis QR Code</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        Presensi Siswa Cepat, Akurat & <span class="text-blue-600">Terintegrasi</span>
                    </h1>

                    <!-- Description -->
                    <p class="mt-4 sm:mt-6 text-sm sm:text-base lg:text-lg text-slate-600 leading-relaxed">
                        Solusi digitalisasi presensi kelas <strong>SMP NEGERI 2 MIJEN</strong> dengan pemindaian QR instan, rekonsiliasi data otomatis, format impor/ekspor Excel resmi, dan pemantauan realtime.
                    </p>

                    <!-- Buttons -->
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto py-3 px-6 inline-flex justify-center items-center gap-x-2 text-sm font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-md shadow-blue-500/20 transition cursor-pointer">
                                <span>Menuju Dashboard SIPRES</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="w-full sm:w-auto py-3 px-6 inline-flex justify-center items-center gap-x-2 text-sm font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-md shadow-blue-500/20 transition cursor-pointer">
                                <span>Masuk ke Akun Anda</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="#fitur" class="w-full sm:w-auto py-3 px-5 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                                <i data-lucide="info" class="w-4 h-4 text-slate-500"></i>
                                <span>Pelajari Fitur</span>
                            </a>
                        @endauth
                    </div>

                    <!-- Demo Accounts Notification Box -->
                    <div class="mt-10 p-4 sm:p-5 rounded-2xl bg-white border border-slate-200 shadow-xs text-left max-w-xl mx-auto">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="key-round" class="w-4 h-4 text-blue-600"></i>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Akun Pengguna Demo</h2>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Admin</span>
                                <p class="text-xs font-mono font-semibold text-slate-800 mt-1">admin</p>
                                <p class="text-[11px] font-mono text-slate-500">password: admin123</p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Guru</span>
                                <p class="text-xs font-mono font-semibold text-slate-800 mt-1">198501152010011001</p>
                                <p class="text-[11px] font-mono text-slate-500">password: guru123</p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">Siswa</span>
                                <p class="text-xs font-mono font-semibold text-slate-800 mt-1">8101</p>
                                <p class="text-[11px] font-mono text-slate-500">password: siswa123</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="fitur" class="py-12 sm:py-16 bg-white border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Fitur Unggulan Sistem Presensi
                    </h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Dirancang khusus untuk kebutuhan sekolah menengah dengan standar performa dan keandalan tinggi.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    <!-- Feature 1 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-blue-400 hover:shadow-xs transition">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Pemindaian QR Instan</h3>
                        <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Kamera langsung mengenali QR kartu siswa dalam hitungan milidetik dengan audio chime feedback dan proteksi duplikasi.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-emerald-400 hover:shadow-xs transition">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                            <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Integrasi Excel (.xlsx)</h3>
                        <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Dukungan impor & ekspor data Siswa, Guru, Mata Pelajaran, Jadwal, dan Rekap Presensi secara rapi via format spreadsheet.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-amber-400 hover:shadow-xs transition">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Mobile Native 360px</h3>
                        <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Dioptimalkan penuh untuk layar smartphone Android compact tanpa horizontal scrolling dengan navigasi bawah yang responsif.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-purple-400 hover:shadow-xs transition">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-4">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Multi-Role Terproteksi</h3>
                        <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Pemisahan wewenang yang tegas antara Administrator Sekolah, Guru Pengajar, dan Siswa dengan log aktivitas terverifikasi.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-800">SIPRES</span>
                <span>•</span>
                <span>SMP NEGERI 2 MIJEN</span>
            </div>
            <div>
                © {{ date('Y') }} Hak Cipta Dilindungi Undang-Undang. Presensi Siswa Berbasis QR Code.
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
