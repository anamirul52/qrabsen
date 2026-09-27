<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - SIPRES SMP NEGERI 2 MIJEN</title>
    
    <!-- Preline & Inter Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Mobile 360px Native Safeguards */
        @media (max-width: 380px) {
            html, body {
                font-size: 13.5px;
            }
        }
        /* Hide scrollbars on mobile tab navigation while keeping scroll capability */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-800 bg-gray-50 overflow-x-hidden selection:bg-blue-600 selection:text-white" x-data="{ mobileMenuOpen: false }">
    <div class="min-h-full flex flex-col md:flex-row w-full max-w-full overflow-x-hidden">

        <!-- Mobile Drawer Backdrop (Preline Style) -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false" 
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-50 md:hidden" 
             style="display: none;"></div>

        <!-- Sidebar Navigation (Preline Application Sidebar) -->
        <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
               class="fixed inset-y-0 start-0 z-50 w-72 sm:w-64 bg-white border-e border-gray-200 flex flex-col transition-transform duration-200 ease-in-out md:static md:w-64 md:shrink-0 shadow-lg md:shadow-none">
            
            <!-- School Brand Header -->
            <div class="h-16 flex items-center justify-between px-5 border-b border-gray-100">
                <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="flex items-center gap-x-3 focus:outline-hidden">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-sm shadow-blue-500/20 shrink-0">
                        <i data-lucide="qr-code" class="w-5 h-5"></i>
                    </div>
                    <div class="truncate">
                        <div class="text-base font-bold text-gray-900 tracking-tight leading-none flex items-center gap-1.5">
                            SIPRES
                            <span class="inline-flex items-center py-0.5 px-1.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800">PRO</span>
                        </div>
                        <div class="text-[10px] font-semibold text-blue-600 uppercase tracking-wider mt-1">SMPN 2 MIJEN</div>
                    </div>
                </a>
                <button type="button" @click="mobileMenuOpen = false" class="md:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition" aria-label="Tutup Menu">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Role & Academic Year Badge -->
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-x-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ auth()->user()->role === 'admin' ? 'bg-blue-400' : (auth()->user()->role === 'teacher' ? 'bg-emerald-400' : 'bg-amber-400') }}"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 {{ auth()->user()->role === 'admin' ? 'bg-blue-600' : (auth()->user()->role === 'teacher' ? 'bg-emerald-600' : 'bg-amber-600') }}"></span>
                    </span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-700">
                        {{ auth()->user()->role === 'admin' ? 'Admin / TU' : (auth()->user()->role === 'teacher' ? 'Guru Mapel' : 'Siswa') }}
                    </span>
                </div>
                <span class="inline-flex items-center py-0.5 px-2 rounded-full text-[10px] font-medium bg-white text-gray-600 border border-gray-200 shadow-2xs">
                    2025/2026 Ganjil
                </span>
            </div>

            <!-- Preline Nav Links -->
            <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1 text-sm no-scrollbar">
                @if (auth()->user()->isAdmin())
                    <!-- Admin Navigation -->
                    <div class="px-3 pb-1.5 pt-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">Utama</div>
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Dashboard</span>
                    </a>

                    <div class="pt-5 px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Master Data</div>
                    <a href="{{ route('admin.students.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.students.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="users" class="w-4 h-4 {{ request()->routeIs('admin.students.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Data Siswa</span>
                    </a>
                    <a href="{{ route('admin.teachers.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.teachers.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="graduation-cap" class="w-4 h-4 {{ request()->routeIs('admin.teachers.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Data Guru</span>
                    </a>
                    <a href="{{ route('admin.classes.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.classes.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="school" class="w-4 h-4 {{ request()->routeIs('admin.classes.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Data Kelas</span>
                    </a>
                    <a href="{{ route('admin.subjects.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.subjects.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="book-open" class="w-4 h-4 {{ request()->routeIs('admin.subjects.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Mata Pelajaran</span>
                    </a>
                    <a href="{{ route('admin.schedules.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.schedules.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="calendar-days" class="w-4 h-4 {{ request()->routeIs('admin.schedules.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Jadwal Pelajaran</span>
                    </a>

                    <div class="pt-5 px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Presensi & QR</div>
                    <a href="{{ route('admin.attendance.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.attendance.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="clipboard-check" class="w-4 h-4 {{ request()->routeIs('admin.attendance.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Rekap Presensi</span>
                    </a>
                    <a href="{{ route('admin.qr-cards.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.qr-cards.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="printer" class="w-4 h-4 {{ request()->routeIs('admin.qr-cards.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Cetak Kartu QR</span>
                    </a>

                    <div class="pt-5 px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">Sistem</div>
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.audit-logs.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="history" class="w-4 h-4 {{ request()->routeIs('admin.audit-logs.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Audit Log</span>
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="settings" class="w-4 h-4 {{ request()->routeIs('admin.settings.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Pengaturan</span>
                    </a>
                @elseif (auth()->user()->isTeacher())
                    <!-- Teacher Navigation -->
                    <div class="px-3 pb-1.5 pt-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">Menu Guru</div>
                    <a href="{{ route('teacher.dashboard') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('teacher.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('teacher.dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('teacher.sessions.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('teacher.sessions.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="camera" class="w-4 h-4 {{ request()->routeIs('teacher.sessions.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Sesi & Scanner</span>
                    </a>
                    <a href="{{ route('teacher.attendance.index') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('teacher.attendance.*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="file-check-2" class="w-4 h-4 {{ request()->routeIs('teacher.attendance.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Riwayat Presensi</span>
                    </a>
                @else
                    <!-- Student Navigation -->
                    <div class="px-3 pb-1.5 pt-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">Menu Siswa</div>
                    <a href="{{ route('student.dashboard') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('student.dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('student.qr') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.qr') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="qr-code" class="w-4 h-4 {{ request()->routeIs('student.qr') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Kartu QR Saya</span>
                    </a>
                    <a href="{{ route('student.attendance') }}"
                       class="flex items-center gap-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.attendance') ? 'bg-blue-50 text-blue-700 font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i data-lucide="history" class="w-4 h-4 {{ request()->routeIs('student.attendance') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span>Riwayat Kehadiran</span>
                    </a>
                @endif
            </nav>

            <!-- User Profile & Logout (Preline User Block) -->
            <div class="p-3 border-t border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-x-3 p-2 rounded-lg bg-white border border-gray-200/80 shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="truncate flex-1 min-w-0">
                        <div class="text-xs font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-gray-500 truncate">{{ auth()->user()->email ?? auth()->user()->username }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit"
                        class="w-full py-2 px-3 inline-flex justify-center items-center gap-x-2 text-xs font-medium rounded-lg border border-gray-200 bg-white text-rose-600 shadow-2xs hover:bg-rose-50 hover:border-rose-200 transition cursor-pointer">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 w-full overflow-x-hidden">
            
            <!-- Topbar Header (Preline Application Navbar) -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-3 sm:px-6 z-10 shrink-0 sticky top-0">
                <div class="flex items-center gap-x-2 sm:gap-x-3 min-w-0">
                    <button type="button" @click="mobileMenuOpen = true"
                        class="p-2 -ms-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg md:hidden focus:outline-hidden transition"
                        aria-label="Buka Menu">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div class="truncate">
                        <h1 class="text-sm sm:text-base font-bold text-gray-900 leading-tight truncate">
                            {{ $header ?? 'Dashboard' }}
                        </h1>
                        <p class="text-[11px] text-gray-500 hidden sm:block">
                            {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-x-2 sm:gap-x-3">
                    @if (auth()->user()->isTeacher())
                        <a href="{{ route('teacher.sessions.index') }}"
                           class="py-1.5 px-2.5 sm:px-3 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-2xs transition">
                            <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Scanner QR</span>
                        </a>
                    @elseif (auth()->user()->isStudent())
                        <a href="{{ route('student.qr') }}"
                           class="py-1.5 px-2.5 sm:px-3 inline-flex items-center gap-x-1.5 text-xs font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 shadow-2xs transition">
                            <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                            <span>Kartu QR</span>
                        </a>
                    @endif

                    <div class="flex items-center gap-x-2 ps-2 border-s border-gray-200">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs border border-gray-200/60 shadow-2xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden lg:block text-start leading-tight">
                            <div class="text-xs font-semibold text-gray-800">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-gray-500 capitalize">{{ auth()->user()->role }}</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Preline Flash Alerts -->
            <div class="px-3 sm:px-6 pt-3 sm:pt-4">
                @if (session('success'))
                    <div class="mb-3 rounded-xl bg-teal-50 border border-teal-200 p-3.5 sm:p-4 text-sm text-teal-800 flex items-center gap-x-3 shadow-2xs">
                        <div class="w-7 h-7 rounded-lg bg-teal-100 flex items-center justify-center text-teal-700 shrink-0">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <span class="font-medium text-xs sm:text-sm flex-1">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-3 rounded-xl bg-red-50 border border-red-200 p-3.5 sm:p-4 text-sm text-red-800 flex items-center gap-x-3 shadow-2xs">
                        <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center text-red-700 shrink-0">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        </div>
                        <span class="font-medium text-xs sm:text-sm flex-1">{{ session('error') }}</span>
                    </div>
                @endif
            </div>

            <!-- Page Content Slot (Responsive 360px Native) -->
            <main class="flex-1 p-3 sm:p-6 pb-24 md:pb-8 w-full max-w-full overflow-x-hidden">
                {{ $slot }}
            </main>

            <!-- Bottom Navigation Bar for Mobile (Optimized for 360px Viewports) -->
            <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-gray-200 z-40 px-1 py-1 flex items-center justify-around shadow-lg">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Beranda</span>
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('admin.students.*') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Siswa</span>
                    </a>
                    <a href="{{ route('admin.attendance.index') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('admin.attendance.*') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Presensi</span>
                    </a>
                    <button type="button" @click="mobileMenuOpen = true" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] text-gray-500 hover:text-gray-800">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Menu</span>
                    </button>
                @elseif (auth()->user()->isTeacher())
                    <a href="{{ route('teacher.dashboard') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('teacher.dashboard') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Beranda</span>
                    </a>
                    <a href="{{ route('teacher.sessions.index') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('teacher.sessions.*') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="camera" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Scanner</span>
                    </a>
                    <a href="{{ route('teacher.attendance.index') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('teacher.attendance.*') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Riwayat</span>
                    </a>
                    <button type="button" @click="mobileMenuOpen = true" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] text-gray-500 hover:text-gray-800">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Menu</span>
                    </button>
                @else
                    <a href="{{ route('student.dashboard') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('student.dashboard') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Beranda</span>
                    </a>
                    <a href="{{ route('student.qr') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('student.qr') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="qr-code" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Kartu QR</span>
                    </a>
                    <a href="{{ route('student.attendance') }}" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] {{ request()->routeIs('student.attendance') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}">
                        <i data-lucide="history" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Riwayat</span>
                    </a>
                    <button type="button" @click="mobileMenuOpen = true" class="flex-1 flex flex-col items-center justify-center py-1.5 px-1 min-h-[48px] text-gray-500 hover:text-gray-800">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight mt-0.5">Profil</span>
                    </button>
                @endif
            </nav>
        </div>
    </div>

    @livewireScripts
</body>
</html>
